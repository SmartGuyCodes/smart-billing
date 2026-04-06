<?php

namespace SmartGuyCodes\Billing\Services;

use Illuminate\Support\Facades\Log;
use SmartGuyCodes\Billing\Events\DunningAttempted;
use SmartGuyCodes\Billing\Events\SubscriptionSuspended;
use SmartGuyCodes\Billing\Events\SubscriptionCancelled;
use SmartGuyCodes\Billing\Models\BillingSubscription;
use SmartGuyCodes\Billing\Models\BillingTransaction;

class DunningService
{
    public function __construct(protected PaymentService $paymentService) {}

    /**
     * Process all subscriptions in past_due state and attempt retry.
     */
    public function processAll(): void
    {
        if (!config('billing.dunning.enabled', true)) return;

        $intervals = config('billing.dunning.retry_intervals', [1, 3, 7]);
        $maxRetries = config('billing.dunning.max_retries', 3);

        BillingSubscription::pastDue()
            ->with(['plan', 'billable'])
            ->each(function (BillingSubscription $subscription) use ($intervals, $maxRetries) {
                $this->processSubscription($subscription, $intervals, $maxRetries);
            });
    }

    protected function processSubscription(BillingSubscription $subscription, array $intervals, int $maxRetries): void
    {
        $retryCount = $subscription->retry_count ?? 0;

        if ($retryCount >= $maxRetries) {
            $this->suspend($subscription);
            return;
        }

        // Check if it's time to retry based on last_failed_at
        $lastFailed = $subscription->last_failed_at;
        if ($lastFailed) {
            $daysSinceFail = now()->diffInDays($lastFailed);
            $requiredDays  = $intervals[$retryCount] ?? 7;

            if ($daysSinceFail < $requiredDays) {
                return; // Not yet time to retry
            }
        }

        // Find the last failed transaction to retry
        $failedTx = BillingTransaction::where('subscription_id', $subscription->id)
            ->failed()
            ->latest()
            ->first();

        if (!$failedTx) {
            Log::warning("Dunning: No failed transaction for subscription #{$subscription->id}");
            return;
        }

        Log::info("Dunning: Retrying payment for subscription #{$subscription->id}, attempt " . ($retryCount + 1));

        $result = $this->paymentService->initiate($subscription->billable, [
            'amount'           => $subscription->plan->price,
            'account_number'   => $failedTx->account_number,
            'account_type'     => $failedTx->account_type,
            'transaction_type' => 'income',
            'description'      => "Renewal retry #" . ($retryCount + 1) . " - {$subscription->plan->name}",
            'subscription_id'  => $subscription->id,
            'driver'           => $failedTx->driver,
        ]);

        $subscription->increment('retry_count');
        $subscription->update(['last_failed_at' => now()]);

        event(new DunningAttempted($subscription, $result, $retryCount + 1));
    }

    protected function suspend(BillingSubscription $subscription): void
    {
        $graceDays = config('billing.dunning.grace_period_days', 3);

        if ($subscription->current_period_end?->addDays($graceDays)->isPast()) {
            $subscription->update(['status' => 'suspended']);
            event(new SubscriptionSuspended($subscription));
        }
    }

    /**
     * Hard cancel subscriptions that have been suspended too long.
     */
    public function cancelSuspended(): void
    {
        $cancelAfter = config('billing.dunning.cancel_after_days', 30);

        BillingSubscription::where('status', 'suspended')
            ->where('updated_at', '<', now()->subDays($cancelAfter))
            ->each(function (BillingSubscription $subscription) {
                $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                event(new SubscriptionCancelled($subscription));
            });
    }
}