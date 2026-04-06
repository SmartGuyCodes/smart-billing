<?php

namespace SmartGuyCodes\Billing\Contracts;

use SmartGuyCodes\Billing\Support\PaymentResult;

interface PaymentDriver
{
    /**
     * Initiate a payment request (e.g. STK Push for M-Pesa).
     */
    public function initiate(array $payload): PaymentResult;

    /**
     * Verify/query the status of a transaction.
     */
    public function verify(string $reference): PaymentResult;

    /**
     * Handle an inbound callback/webhook payload from the gateway.
     */
    public function handleCallback(array $payload): PaymentResult;

    /**
     * Refund a transaction.
     */
    public function refund(string $reference, float $amount): PaymentResult;

    /**
     * Return the driver's unique slug identifier.
     */
    public function driverName(): string;

    /**
     * Validate the driver's configuration before use.
     */
    public function validateConfig(): void;
}