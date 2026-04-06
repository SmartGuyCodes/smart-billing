<?php

namespace SmartGuyCodes\Billing\Console\Commands;

use Illuminate\Console\Command;
use SmartGuyCodes\Billing\Models\BillingSubscription;
use SmartGuyCodes\Billing\Notifications\RenewalReminderNotification;

class SendRemindersCommand extends Command
{
    protected $signature   = 'billing:reminders';
    protected $description = 'Send renewal reminder notifications to expiring subscriptions';

    public function handle(): void
    {
        if (!config('billing.reminders.enabled', true)) {
            $this->info('Reminders are disabled in config.');
            return;
        }

        $daysBefore = config('billing.reminders.days_before', [7, 3, 1]);
        $sent = 0;

        foreach ($daysBefore as $days) {
            $subscriptions = BillingSubscription::active()
                ->whereDate('current_period_end', now()->addDays($days)->toDateString())
                ->with(['billable', 'plan'])
                ->get();

            foreach ($subscriptions as $subscription) {
                $billable = $subscription->billable;

                if (!method_exists($billable, 'notify')) continue;

                try {
                    $billable->notify(new RenewalReminderNotification($subscription, $days));
                    $this->line("  ✓ Reminder ({$days}d) → {$billable->email}");
                    $sent++;
                } catch (\Throwable $e) {
                    $this->error("  ✗ Failed to notify {$billable->email}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Sent {$sent} renewal reminder(s).");
    }
}