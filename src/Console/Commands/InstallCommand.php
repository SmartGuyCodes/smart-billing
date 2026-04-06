<?php

namespace SmartGuyCodes\Billing\Console\Commands;

use Illuminate\Console\Command;
use SmartGuyCodes\Billing\Models\BillingPlan;
use SmartGuyCodes\Billing\Models\BillingSubscription;

class InstallCommand extends Command
{
    protected $signature   = 'billing:install';
    protected $description = 'Publish config, run migrations, and seed sample plans';

    public function handle(): void
    {
        $this->info('Installing SmartGuyCodes Billing...');

        $this->call('vendor:publish', ['--tag' => 'billing-config', '--force' => false]);
        $this->call('vendor:publish', ['--tag' => 'billing-migrations', '--force' => false]);
        $this->call('migrate');

        if ($this->confirm('Seed sample plans (Starter / Pro / Enterprise)?', true)) {
            $this->seedPlans();
        }

        $this->newLine();
        $this->info('✓ Smart Billing installed successfully.');
        $this->line('  Next steps:');
        $this->line('  1. Set MPESA_* environment variables in .env');
        $this->line('  2. Add the Billable trait to your User model');
        $this->line('  3. Schedule billing:renewals, billing:dunning, billing:reminders');
        $this->line('  4. Visit /' . config('billing.admin.prefix', 'billing-admin'));
    }

    protected function seedPlans(): void
    {
        $plans = [
            ['name' => 'Starter',    'slug' => 'starter',    'price' => 999,   'interval' => 'monthly', 'trial_days' => 14, 'features' => ['Up to 3 users', '5GB storage', 'Email support'], 'sort_order' => 1],
            ['name' => 'Pro',        'slug' => 'pro',        'price' => 2999,  'interval' => 'monthly', 'trial_days' => 14, 'features' => ['Up to 20 users', '50GB storage', 'Priority support', 'API access'], 'is_popular' => true, 'sort_order' => 2],
            ['name' => 'Enterprise', 'slug' => 'enterprise', 'price' => 9999,  'interval' => 'monthly', 'trial_days' => 0,  'features' => ['Unlimited users', 'Unlimited storage', 'Dedicated support', 'SLA', 'Custom integrations'], 'sort_order' => 3],
        ];

        foreach ($plans as $plan) {
            BillingPlan::firstOrCreate(['slug' => $plan['slug']], array_merge($plan, [
                'currency'   => config('billing.currency', 'KES'),
                'is_active'  => true,
                'is_popular' => $plan['is_popular'] ?? false,
            ]));
        }

        $this->line('  ✓ Sample plans seeded.');
    }
}