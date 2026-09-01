<?php

namespace App\Console\Commands;

use App\Services\Billing\SubscriptionBillingService;
use Illuminate\Console\Command;

class ActivateScheduledPlans extends Command
{
    protected $signature = 'velour:activate-scheduled-plans';

    protected $description = 'Activate paid plans scheduled after trial or billing period end';

    public function handle(SubscriptionBillingService $billing): int
    {
        $count = $billing->activateDueScheduledPlans();

        $this->info("Activated {$count} scheduled plan(s).");

        return self::SUCCESS;
    }
}
