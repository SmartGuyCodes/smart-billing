<?php

namespace SmartGuyCodes\Billing\Console\Commands;

use Illuminate\Console\Command;
use SmartGuyCodes\Billing\Services\DunningService;

class ProcessDunningCommand extends Command
{
    protected $signature   = 'billing:dunning';
    protected $description = 'Retry failed payments and suspend/cancel overdue subscriptions';

    public function handle(DunningService $dunningService): void
    {
        $this->info('Processing dunning...');

        $dunningService->processAll();
        $dunningService->cancelSuspended();

        $this->info('Dunning complete.');
    }
}