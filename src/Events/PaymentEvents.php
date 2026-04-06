<?php

namespace SmartGuyCodes\Billing\Events;

use SmartGuyCodes\Billing\Models\BillingTransaction;
use SmartGuyCodes\Billing\Support\PaymentResult;

class PaymentInitiated
{
    public function __construct(
        public readonly BillingTransaction $transaction,
        public readonly PaymentResult $result,
    ) {}
}

class PaymentCompleted
{
    public function __construct(
        public readonly BillingTransaction $transaction,
        public readonly PaymentResult $result,
    ) {}
}

class PaymentFailed
{
    public function __construct(
        public readonly BillingTransaction $transaction,
        public readonly PaymentResult $result,
    ) {}
}