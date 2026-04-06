<?php

namespace SmartGuyCodes\Billing\Concerns;

use SmartGuyCodes\Billing\Models\BillingInvoice;
use SmartGuyCodes\Billing\Models\BillingPlan;
use SmartGuyCodes\Billing\Models\BillingSubscription;
use SmartGuyCodes\Billing\Models\BillingTransaction;
use SmartGuyCodes\Billing\Services\PaymentService;
use SmartGuyCodes\Billing\Services\SubscriptionService;
use SmartGuyCodes\Billing\Support\PaymentResult;

/**
 * Add this trait to your User (or any billable) model:
 *
 *   use SmartGuyCodes\Billing\Concerns\Billable;
 */
trait Billable
{
    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function billingTransactions()
    {
        return $this->morphMany(BillingTransaction::class, 'billable');
    }

    public function billingSubscriptions()
    {
        return $this->morphMany(BillingSubscription::class, 'billable');
    }

    public function billingInvoices()
    {
        return $this->morphMany(BillingInvoice::class, 'billable');
    }

    // -------------------------------------------------------------------------
    // Subscription helpers
    // -------------------------------------------------------------------------

    public function activeSubscription(): ?BillingSubscription
    {
        return $this->billingSubscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->latest()
            ->first();
    }

    public function subscribedTo(BillingPlan|int $plan): bool
    {
        $planId = $plan instanceof BillingPlan ? $plan->id : $plan;
        return $this->billingSubscriptions()
            ->where('plan_id', $planId)
            ->whereIn('status', ['active', 'trialing'])
            ->exists();
    }

    public function isSubscribed(): bool
    {
        return $this->billingSubscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->exists();
    }

    public function onTrial(): bool
    {
        return $this->billingSubscriptions()
            ->where('status', 'trialing')
            ->exists();
    }

    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    public function subscribeTo(BillingPlan|int $plan, array $options = []): BillingSubscription
    {
        return app(SubscriptionService::class)->subscribe($this, $plan, $options);
    }

    public function charge(array $payload): PaymentResult
    {
        return app(PaymentService::class)->initiate($this, $payload);
    }

    public function cancelSubscription(bool $immediately = false): BillingSubscription
    {
        $subscription = $this->activeSubscription();

        if (!$subscription) {
            throw new \RuntimeException("No active subscription found.");
        }

        return app(SubscriptionService::class)->cancel($subscription, $immediately);
    }
}