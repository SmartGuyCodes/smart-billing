<?php
    namespace SmartGuyCodes\Billing\Services;

    use Carbon\Carbon;
    use Illuminate\Database\Eloquent\Model;
    use SmartGuyCodes\Billing\Events\SubscriptionCreated;
    use SmartGuyCodes\Billing\Events\SubscriptionRenewed;
    use SmartGuyCodes\Billing\Events\SubscriptionCancelled;
    use SmartGuyCodes\Billing\Models\BillingPlan;
    use SmartGuyCodes\Billing\Models\BillingSubscription;

    class SubscriptionService
    {
        /**
         * Subscribe a billable to a plan.
         */
        public function subscribe(Model $billable, BillingPlan|int|string $plan, array $options = []): BillingSubscription
        {
            $plan = $plan instanceof BillingPlan ? $plan : BillingPlan::findOrFail($plan);

            $trialDays = $options['trial_days'] ?? $plan->trial_days ?? 0;
            $now       = now();
            $status    = $trialDays > 0 ? 'trialing' : 'active';

            $trialEnd  = $trialDays > 0 ? $now->copy()->addDays($trialDays) : null;
            $periodStart = $trialEnd ?? $now;
            $periodEnd   = $this->calculatePeriodEnd($periodStart, $plan->interval);

            // Cancel any existing active subscription
            $this->cancelExisting($billable);

            $subscription = BillingSubscription::create([
                'billable_id'          => $billable->getKey(),
                'billable_type'        => get_class($billable),
                'plan_id'              => $plan->id,
                'status'               => $status,
                'trial_ends_at'        => $trialEnd,
                'current_period_start' => $periodStart,
                'current_period_end'   => $periodEnd,
                'retry_count'          => 0,
                'meta'                 => $options['meta'] ?? [],
            ]);

            event(new SubscriptionCreated($subscription));

            return $subscription;
        }

        /**
         * Change a subscription's plan.
         */
        public function changePlan(BillingSubscription $subscription, BillingPlan|int $newPlan): BillingSubscription
        {
            $plan = $newPlan instanceof BillingPlan ? $newPlan : BillingPlan::findOrFail($newPlan);

            $subscription->update(['plan_id' => $plan->id]);
            $subscription->refresh();

            return $subscription;
        }

        /**
         * Cancel a subscription.
         */
        public function cancel(BillingSubscription $subscription, bool $immediately = false): BillingSubscription
        {
            $subscription->cancel($immediately);
            event(new SubscriptionCancelled($subscription));
            return $subscription->fresh();
        }

        /**
         * Resume a cancelled subscription within the grace period.
         */
        public function resume(BillingSubscription $subscription): BillingSubscription
        {
            $subscription->resume();
            return $subscription->fresh();
        }

        /**
         * Get all subscriptions due for renewal within N days.
         */
        public function getDueForRenewal(int $withinDays = 0): \Illuminate\Database\Eloquent\Collection
        {
            return BillingSubscription::active()
                ->where('current_period_end', '<=', now()->addDays($withinDays))
                ->with('plan', 'billable')
                ->get();
        }

        protected function calculatePeriodEnd(Carbon $from, string $interval): Carbon
        {
            return match($interval) {
                'daily'   => $from->copy()->addDay(),
                'weekly'  => $from->copy()->addWeek(),
                'monthly' => $from->copy()->addMonth(),
                'yearly'  => $from->copy()->addYear(),
                default   => $from->copy()->addMonth(),
            };
        }

        protected function cancelExisting(Model $billable): void
        {
            BillingSubscription::where('billable_id', $billable->getKey())
                ->where('billable_type', get_class($billable))
                ->whereIn('status', ['active', 'trialing', 'past_due'])
                ->each(fn($s) => $s->cancel(immediately: true));
        }
    }