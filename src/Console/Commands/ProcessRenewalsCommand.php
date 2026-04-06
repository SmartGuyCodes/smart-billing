<?php

namespace SmartGuyCodes\Billing\Console\Commands;

use Illuminate\Console\Command;
use SmartGuyCodes\Billing\Models\BillingSubscription;
use SmartGuyCodes\Billing\Services\PaymentService;
use SmartGuyCodes\Billing\Support\ReferenceGenerator;

class ProcessRenewalsCommand extends Command
{
    protected $signature   = 'billing:renewals {--dry-run : Preview without charging}';
    protected $description = 'Charge subscriptions that are due for renewal';

    public function handle(PaymentService $paymentService): void
    {
        $dryRun = $this->option('dry-run');

        $due = BillingSubscription::active()
            ->where('current_period_end', '<=', now())
            ->with(['plan', 'billable'])
            ->get();

        $this->info("Found {$due->count()} subscription(s) due for renewal." . ($dryRun ? ' (DRY RUN)' : ''));

        $charged = 0;
        $failed  = 0;

        foreach ($due as $subscription) {
            $plan     = $subscription->plan;
            $billable = $subscription->billable;

            $billableIdentifier = isset($billable->email) ? $billable->email : $billable->id;
            $this->line("  → #{$subscription->id} | {$billableIdentifier} | {$plan->name} | " . config('billing.currency_symbol') . number_format($plan->price, 2));

            if ($dryRun) continue;

            // Retrieve the default payment method for this subscriber
            $paymentMethod = $billable->billingPaymentMethods()->where('is_default', true)->first()
                ?? $billable->billingTransactions()->completed()->latest()->first();

            if (!$paymentMethod) {
                $this->warn("    ✗ No payment method on file for subscription #{$subscription->id}");
                $subscription->update(['status' => 'past_due']);
                $failed++;
                continue;
            }

            try {
                $result = $paymentService->initiate($billable, [
                    'amount'           => $plan->price,
                    'account_number'   => $paymentMethod->account_number,
                    'account_type'     => $paymentMethod->account_type ?? $paymentMethod->account_type ?? 'mobile',
                    'transaction_type' => 'income',
                    'description'      => "Renewal: {$plan->name} — " . now()->format('M Y'),
                    'subscription_id'  => $subscription->id,
                    'driver'           => $paymentMethod->driver ?? config('billing.default_driver'),
                ]);

                if ($result->success) {
                    $this->line("    ✓ Renewal initiated: {$result->reference}");
                    $charged++;
                } else {
                    $this->error("    ✗ Failed: {$result->message}");
                    $subscription->update(['status' => 'past_due', 'last_failed_at' => now()]);
                    $subscription->increment('retry_count');
                    $failed++;
                }
            } catch (\Throwable $e) {
                $this->error("    ✗ Exception: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Done. Charged: {$charged} | Failed: {$failed}");
    }
}