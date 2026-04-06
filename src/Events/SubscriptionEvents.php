<?php

namespace SmartGuyCodes\Billing\Events;

use SmartGuyCodes\Billing\Models\BillingSubscription;
use SmartGuyCodes\Billing\Support\PaymentResult;

class SubscriptionCreated
{
    public function __construct(public readonly BillingSubscription $subscription) {}
}

class SubscriptionRenewed
{
    public function __construct(public readonly BillingSubscription $subscription) {}
}

class SubscriptionCancelled
{
    public function __construct(public readonly BillingSubscription $subscription) {}
}

class SubscriptionSuspended
{
    public function __construct(public readonly BillingSubscription $subscription) {}
}

class DunningAttempted
{
    public function __construct(
        public readonly BillingSubscription $subscription,
        public readonly PaymentResult $result,
        public readonly int $attemptNumber,
    ) {}
}