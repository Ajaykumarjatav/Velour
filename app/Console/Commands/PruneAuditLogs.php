<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * PruneAuditLogs
 *
 * Enforces log retention policies defined in config/security.php.
 *
 * Schedules (add to routes/console.php):
 *   Schedule::command('audit:prune')->daily()->at('03:00');
 *
 * Defaults:
 *   security_audit  → 365 days (audit_logs table)
 *   activity_log    →  90 days (Spatie activity_log table)
 *
 * Critical events (severity=critical) are NEVER pruned — they are
 * retained indefinitely for compliance and incident investigation.
 *
 * Usage:
 *   php artisan audit:prune
 *   php artisan audit:prune --dry-run
 *   php artisan audit:prune --security-days=730 --activity-days=180
 */
class PruneAuditLogs extends Command
{
    protected $signature = 'audit:prune
                            {--security-days= : Override security audit retention days}
                            {--activity-days= : Override activity log retention days}
                            {--dry-run : Show counts without deleting}';

    protected $description = 'Prune audit and activity logs older than the configured retention period';

    public function handle(): int
    {
        $securityDays  = (int) ($this->option('security-days') ?: config('security.retention.security_audit',  365));
        $activityDays  = (int) ($this->option('activity-days') ?: config('security.retention.activity_log',    90));
        $dryRun        = $this->option('dry-run');

        $this->info("Retention policy:");
        $this->line("  Security audit logs → {$securityDays} days (critical events: never)");
        $this->line("  Activity logs       → {$activityDays} days");

        if ($dryRun) {
            $this->warn('DRY RUN — no records will be deleted.');
        }

        // ── Security audit_logs ───────────────────────────────────────────

        $securityCutoff = now()->subDays($securityDays);

        $securityQuery = AuditLog::where('occurred_at', '<', $securityCutoff)
            ->where('severity', '!=', 'critical'); // Never prune critical events

        $securityCount = $securityQuery->count();
        $this->line("  audit_logs to prune: {$securityCount} records before {$securityCutoff->toDateString()}");

        if (! $dryRun && $securityCount > 0) {
            // Chunk to avoid memory issues on large tables
            $deleted = 0;
            AuditLog::where('occurred_at', '<', $securityCutoff)
                ->where('severity', '!=', 'critical')
                ->chunkById(1000, function ($chunk) use (&$deleted) {
                    $ids = $chunk->pluck('id');
                    AuditLog::whereIn('id', $ids)->delete();
                    $deleted += $ids->count();
                });

            $this->info("  ✓ Deleted {$deleted} security audit records");
        }

        // ── Spatie activity_log ───────────────────────────────────────────

        $activityCutoff = now()->subDays($activityDays);

        $activityCount = Activity::where('created_at', '<', $activityCutoff)->count();
        $this->line("  activity_log to prune: {$activityCount} records before {$activityCutoff->toDateString()}");

        if (! $dryRun && $activityCount > 0) {
            $deleted = 0;
            Activity::where('created_at', '<', $activityCutoff)
                ->chunkById(1000, function ($chunk) use (&$deleted) {
                    $ids = $chunk->pluck('id');
                    Activity::whereIn('id', $ids)->delete();
                    $deleted += $ids->count();
                });

            $this->info("  ✓ Deleted {$deleted} activity log records");
        }

        $this->newLine();
        $this->info('Log pruning complete.');

        return self::SUCCESS;
    }
}
