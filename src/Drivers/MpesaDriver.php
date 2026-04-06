<?php

namespace SmartGuyCodes\Billing\Drivers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use SmartGuyCodes\Billing\Contracts\PaymentDriver;
use SmartGuyCodes\Billing\Support\PaymentResult;
use SmartGuyCodes\Billing\Support\ReferenceGenerator;

class MpesaDriver implements PaymentDriver
{
    protected Client $http;
    protected string $baseUrl;

    public function __construct(protected array $config)
    {
        $this->validateConfig();

        $this->baseUrl = $config['environment'] === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';

        $this->http = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => $config['timeout_seconds'] ?? 60,
            'headers'  => ['Accept' => 'application/json'],
        ]);
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------
    public function getAccessToken(): string
    {
        return Cache::remember('billing.mpesa.token', 3500, function () {
            $credentials = base64_encode("{$this->config['consumer_key']}:{$this->config['consumer_secret']}");

            $response = $this->http->get('/oauth/v1/generate?grant_type=client_credentials', [
                'headers' => ['Authorization' => "Basic {$credentials}"],
            ]);

            $data = json_decode($response->getBody(), true);

            if (empty($data['access_token'])) {
                throw new RuntimeException('M-Pesa: Failed to obtain access token.');
            }

            return $data['access_token'];
        });
    }

    // -------------------------------------------------------------------------
    // STK Push (Lipa Na M-Pesa Online)
    // -------------------------------------------------------------------------
    public function initiate(array $payload): PaymentResult
    {
        $reference = ReferenceGenerator::generate('MPE');

        $phone     = $this->formatPhone($payload['phone']);
        $amount    = (int) ceil($payload['amount']); // M-Pesa requires whole numbers
        $accountRef= $payload['account_number'] ?? $reference;
        $desc      = $payload['description'] ?? 'Payment';

        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($this->config['shortcode'] . $this->config['passkey'] . $timestamp);

        $body = [
            'BusinessShortCode' => $this->config['shortcode'],
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => $this->config['type'] === 'till' ? 'CustomerBuyGoodsOnline' : 'CustomerPayBillOnline',
            'Amount'            => $amount,
            'PartyA'            => $phone,
            'PartyB'            => $this->config['shortcode'],
            'PhoneNumber'       => $phone,
            'CallBackURL'       => $this->config['callback_url'],
            'AccountReference'  => substr($accountRef, 0, 12),
            'TransactionDesc'   => substr($desc, 0, 13),
        ];

        try {
            $response = $this->http->post('/mpesa/stkpush/v1/processrequest', [
                'headers' => $this->authHeaders(),
                'json'    => $body,
            ]);

            $data = json_decode($response->getBody(), true);

            if (($data['ResponseCode'] ?? '') === '0') {
                return PaymentResult::pending([
                    'reference'           => $reference,
                    'gateway_ref'         => $data['MerchantRequestID'],
                    'checkout_request_id' => $data['CheckoutRequestID'],
                    'amount'              => $amount,
                    'message'             => $data['CustomerMessage'] ?? 'STK Push sent.',
                    'raw'                 => $data,
                ]);
            }

            return PaymentResult::failed($reference, $data['errorMessage'] ?? 'STK Push failed.', $data);

        } catch (RequestException $e) {
            Log::error('M-Pesa STK Push error', ['error' => $e->getMessage(), 'payload' => $body]);
            return PaymentResult::failed($reference, 'Gateway connection error: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // STK Push Query / Verify
    // -------------------------------------------------------------------------
    public function verify(string $checkoutRequestId): PaymentResult
    {
        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($this->config['shortcode'] . $this->config['passkey'] . $timestamp);

        $body = [
            'BusinessShortCode' => $this->config['shortcode'],
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ];

        try {
            $response = $this->http->post('/mpesa/stkpushquery/v1/query', [
                'headers' => $this->authHeaders(),
                'json'    => $body,
            ]);

            $data = json_decode($response->getBody(), true);

            $resultCode = $data['ResultCode'] ?? null;

            if ($resultCode === '0' || $resultCode === 0) {
                return PaymentResult::success([
                    'reference'  => $checkoutRequestId,
                    'gateway_ref'=> $data['MerchantRequestID'] ?? null,
                    'message'    => $data['ResultDesc'] ?? 'Payment confirmed.',
                    'raw'        => $data,
                ]);
            }

            // Result code 1032 = cancelled by user
            if ($resultCode === '1032') {
                return PaymentResult::failed($checkoutRequestId, 'Payment cancelled by user.', $data);
            }

            return PaymentResult::pending([
                'reference' => $checkoutRequestId,
                'message'   => $data['ResultDesc'] ?? 'Awaiting payment.',
                'raw'       => $data,
            ]);

        } catch (RequestException $e) {
            Log::error('M-Pesa Query error', ['error' => $e->getMessage()]);
            return PaymentResult::failed($checkoutRequestId, 'Query failed: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Callback Handler
    // -------------------------------------------------------------------------
    public function handleCallback(array $payload): PaymentResult
    {
        $stkCallback = $payload['Body']['stkCallback'] ?? [];
        $resultCode  = $stkCallback['ResultCode'] ?? -1;
        $merchantReq = $stkCallback['MerchantRequestID'] ?? '';
        $checkoutReq = $stkCallback['CheckoutRequestID'] ?? '';

        if ((int) $resultCode !== 0) {
            return PaymentResult::failed(
                $checkoutReq,
                $stkCallback['ResultDesc'] ?? 'Payment failed.',
                $payload
            );
        }

        // Extract metadata items
        $items  = collect($stkCallback['CallbackMetadata']['Item'] ?? [])
            ->keyBy('Name');

        $amount      = $items->get('Amount')['Value'] ?? 0;
        $mpesaCode   = $items->get('MpesaReceiptNumber')['Value'] ?? null;
        $phone       = $items->get('PhoneNumber')['Value'] ?? null;
        $transDate   = $items->get('TransactionDate')['Value'] ?? null;

        return PaymentResult::success([
            'reference'           => $checkoutReq,
            'gateway_ref'         => $mpesaCode,
            'checkout_request_id' => $checkoutReq,
            'amount'              => (float) $amount,
            'currency'            => 'KES',
            'message'             => 'Payment received via M-Pesa.',
            'raw'                 => array_merge($payload, [
                'phone'          => $phone,
                'transaction_date' => $transDate,
            ]),
        ]);
    }

    // -------------------------------------------------------------------------
    // B2C Refund (Reversal)
    // -------------------------------------------------------------------------
    public function refund(string $reference, float $amount): PaymentResult
    {
        $body = [
            'InitiatorName'      => $this->config['initiator_name'],
            'SecurityCredential' => $this->generateSecurityCredential(),
            'CommandID'          => 'TransactionReversal',
            'TransactionID'      => $reference,
            'Amount'             => (int) $amount,
            'ReceiverParty'      => $this->config['shortcode'],
            'RecieverIdentifierType' => '11',
            'ResultURL'          => $this->config['callback_url'],
            'QueueTimeOutURL'    => $this->config['timeout_url'],
            'Remarks'            => 'Refund',
            'Occasion'           => 'Refund',
        ];

        try {
            $response = $this->http->post('/mpesa/reversal/v1/request', [
                'headers' => $this->authHeaders(),
                'json'    => $body,
            ]);
            $data = json_decode($response->getBody(), true);

            return ($data['ResponseCode'] ?? '') === '0'
                ? PaymentResult::pending(['reference' => $reference, 'raw' => $data, 'message' => 'Refund initiated.'])
                : PaymentResult::failed($reference, $data['errorMessage'] ?? 'Refund failed.', $data);

        } catch (RequestException $e) {
            return PaymentResult::failed($reference, 'Refund error: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // C2B Registration (for paybill / till confirmations)
    // -------------------------------------------------------------------------
    public function registerC2BUrls(): array
    {
        $body = [
            'ShortCode'       => $this->config['shortcode'],
            'ResponseType'    => 'Completed',
            'ConfirmationURL' => $this->config['c2b_confirmation_url'],
            'ValidationURL'   => $this->config['c2b_validation_url'],
        ];

        $response = $this->http->post('/mpesa/c2b/v1/registerurl', [
            'headers' => $this->authHeaders(),
            'json'    => $body,
        ]);

        return json_decode($response->getBody(), true);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    protected function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Content-Type'  => 'application/json',
        ];
    }

    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '+')) {
            $phone = ltrim($phone, '+');
        } elseif (!str_starts_with($phone, '254')) {
            $phone = '254' . $phone;
        }

        return $phone;
    }

    protected function generateSecurityCredential(): string
    {
        $publicKey = file_get_contents(
            $this->config['environment'] === 'production'
                ? __DIR__ . '/../../certs/production.cer'
                : __DIR__ . '/../../certs/sandbox.cer'
        );

        openssl_public_encrypt(
            $this->config['initiator_password'],
            $encrypted,
            $publicKey,
            OPENSSL_PKCS1_PADDING
        );

        return base64_encode($encrypted);
    }

    public function driverName(): string { return 'mpesa'; }

    public function validateConfig(): void
    {
        $required = ['consumer_key', 'consumer_secret', 'shortcode', 'passkey', 'callback_url'];
        foreach ($required as $key) {
            if (empty($this->config[$key])) {
                throw new InvalidArgumentException("M-Pesa config missing: [{$key}]");
            }
        }
    }
}