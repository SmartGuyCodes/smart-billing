<?php

namespace SmartGuyCodes\Billing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use SmartGuyCodes\Billing\Models\BillingTransaction;
use SmartGuyCodes\Billing\Services\PaymentService;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService) {}

    /**
     * POST /api/billing/pay
     *
     * Body:
     * {
     *   "amount": 999,
     *   "account_number": "0712345678",
     *   "account_type": "mobile",
     *   "transaction_type": "income",
     *   "description": "Starter Plan - April 2025",
     *   "driver": "mpesa"            // optional
     * }
     */
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount'           => 'required|numeric|min:1',
            'account_number'   => 'required|string',
            'account_type'     => 'required|in:mobile,bank,card',
            'transaction_type' => 'required|in:income,expense',
            'description'      => 'nullable|string|max:255',
            'driver'           => 'nullable|string',
            'client_no'        => 'nullable|string',
            'subscription_id'  => 'nullable|integer',
            'invoice_id'       => 'nullable|integer',
        ]);

        // For M-Pesa, phone = account_number when account_type is mobile
        if ($validated['account_type'] === 'mobile') {
            $validated['phone'] = $validated['account_number'];
        }

        $result = $this->paymentService->initiate($request->user(), $validated);

        return response()->json($result->toArray(), $result->success ? 200 : 422);
    }

    /**
     * GET /api/billing/transactions/{ref}
     */
    public function status(string $ref): JsonResponse
    {
        $transaction = BillingTransaction::where('reference_no', $ref)
            ->where('billable_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'reference_no'      => $transaction->reference_no,
            'invoice_number'    => $transaction->invoice_number,
            'client_no'         => $transaction->client_no,
            'account_number'    => $transaction->account_number,
            'account_type'      => $transaction->account_type,
            'transaction_type'  => $transaction->transaction_type,
            'amount'            => $transaction->amount,
            'currency'          => $transaction->currency,
            'status'            => $transaction->status,
            'driver'            => $transaction->driver,
            'gateway_ref'       => $transaction->gateway_ref,
            'description'       => $transaction->description,
            'paid_at'           => $transaction->paid_at,
            'created_at'        => $transaction->created_at,
        ]);
    }

    /**
     * POST /api/billing/transactions/{ref}/verify
     * Manually poll the gateway for the transaction status.
     */
    public function verify(string $ref): JsonResponse
    {
        $transaction = BillingTransaction::where('reference_no', $ref)
            ->where('billable_id', auth()->id())
            ->firstOrFail();

        $result = $this->paymentService->verify($transaction);

        return response()->json($result->toArray());
    }
}