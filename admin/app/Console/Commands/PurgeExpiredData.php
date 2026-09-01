<?php

namespace App\Console\Commands;

use App\Models\SalonNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * GDPR-compliant data retention enforcement.
 *
 * Default retention periods (configurable via .env):
 *   - Notifications:         90 days
 *   - Link visit analytics:  12 months
 *   - Activity log entries:  24 months
 *   - Soft-deleted records:  30 days (permanent purge)
 *
 * Schedule: daily at 02:00
 */
class PurgeExpiredData extends Command
{
    protected $signature = 'velour:purge-expired-data
                            {--dry-run : Show what would be deleted without deleting}
                            {--notifications=90 : Days to retain notifications}
                            {--analytics=365 : Days to retain link visit data}
                            {--activity=730 : Days to retain activity log entries}
                            {--soft-deleted=30 : Days before permanently purging soft-deleted records}';

    protected $description = 'Purge expired data per GDPR retention policy';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $totals = [];

        $this->info($dryRun ? '🔍 DRY RUN — no data will be deleted.' : '🗑  Running data retention purge...');
        $this->newLine();

        // 1. Old notifications
        $cutoff = now()->subDays((int) $this->option('notifications'));
        $count  = SalonNotification::where('created_at', '<', $cutoff)->count();
        $totals['Notifications'] = $count;
        if (! $dryRun && $count) SalonNotification::where('created_at', '<', $cutoff)->delete();

        // 2. Link visit analytics
        $cutoff = now()->subDays((int) $this->option('analytics'));
        $count  = DB::table('link_visits')->where('created_at', '<', $cutoff)->count();
        $totals['Link visits'] = $count;
        if (! $dryRun && $count) DB::table('link_visits')->where('created_at', '<', $cutoff)->delete();

        // 3. Spatie activity logs
        $cutoff = now()->subDays((int) $this->option('activity'));
        try {
            $count = \Spatie\Activitylog\Models\Activity::where('created_at', '<', $cutoff)->count();
            $totals['Activity logs'] = $count;
            if (! $dryRun && $count) \Spatie\Activitylog\Models\Activity::where('created_at', '<', $cutoff)->delete();
        } catch (\Throwable) {
            $totals['Activity logs'] = 'skipped (table unavailable)';
        }

        // 4. Hard-delete soft-deleted records past grace period
        $cutoff  = now()->subDays((int) $this->option('soft-deleted'));
        $purged  = 0;

        $softDeletedModels = [
            \App\Models\Client::class,
            \App\Models\Appointment::class,
            \App\Models\Staff::class,
            \App\Models\Service::class,
            \App\Models\InventoryItem::class,
        ];

        foreach ($softDeletedModels as $model) {
            $count = $model::onlyTrashed()->where('deleted_at', '<', $cutoff)->count();
            $purged += $count;
            if (! $dryRun && $count) $model::onlyTrashed()->where('deleted_at', '<', $cutoff)->forceDelete();
        }
        $totals['Soft-deleted records (force purge)'] = $purged;

        // Report
        $this->table(['Resource', 'Records ' . ($dryRun ? '(would be deleted)' : 'deleted')],
            collect($totals)->map(fn($v, $k) => [$k, $v])->values()->toArray()
        );

        $total = collect($totals)->sum(fn($v) => is_int($v) ? $v : 0);
        $this->info($dryRun
            ? "Would purge {$total} records total."
            : "Purged {$total} records. Retention policy enforced."
        );

        if (! $dryRun) {
            Log::info('Data retention purge complete', ['totals' => $totals, 'timestamp' => now()->toIso8601String()]);
        }

        return Command::SUCCESS;
    }
}
