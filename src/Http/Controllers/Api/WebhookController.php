<?php

namespace SmartGuyCodes\Billing\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use SmartGuyCodes\Billing\Services\PaymentService;

class WebhookController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    /**
     * Handle M-Pesa STK Push callback.
     * Safaricom POSTs to this URL after the user pays or rejects.
     */
    public function mpesaCallback(Request $request)
    {
        Log::channel('billing')->info('M-Pesa STK callback received', $request->all());

        try {
            $result = $this->paymentService->handleCallback($request->all(), 'mpesa');

            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Accepted',
            ]);

        } catch (\Throwable $e) {
            Log::channel('billing')->error('M-Pesa callback error', ['error' => $e->getMessage()]);

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Failed',
            ], 500);
        }
    }

    /**
     * Handle M-Pesa STK Push timeout.
     */
    public function mpesaTimeout(Request $request)
    {
        Log::channel('billing')->warning('M-Pesa STK timeout', $request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Acknowledged']);
    }

    /**
     * C2B Validation URL — called before payment is accepted.
     * Return 0 to accept, non-zero to reject.
     */
    public function mpesaValidation(Request $request)
    {
        Log::channel('billing')->info('M-Pesa C2B validation', $request->all());

        // You can add custom validation logic here, e.g. checking account numbers
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }

    /**
     * C2B Confirmation URL — payment confirmed by Safaricom.
     */
    public function mpesaConfirmation(Request $request)
    {
        Log::channel('billing')->info('M-Pesa C2B confirmation', $request->all());

        try {
            $payload = $request->all();

            // Map C2B payload to our STK callback format
            $normalised = [
                'Body' => [
                    'stkCallback' => [
                        'ResultCode'      => 0,
                        'CheckoutRequestID' => $payload['BillRefNumber'] ?? '',
                        'CallbackMetadata' => [
                            'Item' => [
                                ['Name' => 'Amount',              'Value' => $payload['TransAmount']],
                                ['Name' => 'MpesaReceiptNumber',  'Value' => $payload['TransID']],
                                ['Name' => 'PhoneNumber',         'Value' => $payload['MSISDN']],
                                ['Name' => 'TransactionDate',     'Value' => $payload['TransTime']],
                            ],
                        ],
                    ],
                ],
            ];

            $this->paymentService->handleCallback($normalised, 'mpesa');

        } catch (\Throwable $e) {
            Log::channel('billing')->error('C2B confirmation error', ['error' => $e->getMessage()]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }
}