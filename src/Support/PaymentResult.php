<?php

namespace SmartGuyCodes\Billing\Support;

use function getenv;

class PaymentResult
{
    public function __construct(
        public readonly bool   $success,
        public readonly string $status,        // pending | completed | failed | cancelled
        public readonly string $reference,     // our internal TXN-XXXXXXXXX
        public readonly ?string $gatewayRef = null,  // gateway transaction ID
        public readonly ?float  $amount = null,
        public readonly ?string $currency = null,
        public readonly ?string $message = null,
        public readonly array   $raw = [],      // raw gateway response
        public readonly ?string $checkoutRequestId = null, // M-Pesa specific
    ) {}

    public static function success(array $data): self
    {
        return new self(
            success: true,
            status: $data['status'] ?? 'completed',
            reference: $data['reference'],
            gatewayRef: $data['gateway_ref'] ?? null,
            amount: $data['amount'] ?? null,
            currency: $data['currency'] ?? getenv('BILLING_CURRENCY') ?: 'KES',
            message: $data['message'] ?? 'Payment successful',
            raw: $data['raw'] ?? [],
            checkoutRequestId: $data['checkout_request_id'] ?? null,
        );
    }

    public static function pending(array $data): self
    {
        return new self(
            success: true,
            status: 'pending',
            reference: $data['reference'],
            gatewayRef: $data['gateway_ref'] ?? null,
            amount: $data['amount'] ?? null,
            currency: $data['currency'] ?? getenv('BILLING_CURRENCY') ?: 'KES',
            message: $data['message'] ?? 'Payment initiated, awaiting confirmation',
            raw: $data['raw'] ?? [],
            checkoutRequestId: $data['checkout_request_id'] ?? null,
        );
    }

    public static function failed(string $reference, string $message, array $raw = []): self
    {
        return new self(
            success: false,
            status: 'failed',
            reference: $reference,
            message: $message,
            raw: $raw,
        );
    }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isFailed(): bool    { return $this->status === 'failed'; }

    public function toArray(): array
    {
        return [
            'success'              => $this->success,
            'status'               => $this->status,
            'reference'            => $this->reference,
            'gateway_ref'          => $this->gatewayRef,
            'amount'               => $this->amount,
            'currency'             => $this->currency,
            'message'              => $this->message,
            'checkout_request_id'  => $this->checkoutRequestId,
        ];
    }
}