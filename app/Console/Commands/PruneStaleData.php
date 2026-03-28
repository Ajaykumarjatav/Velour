<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * PruneStaleData — AUDIT FIX: Database Optimization + Backup/Recovery
 *
 * Scheduler-driven data lifecycle management:
 *  - Hard-delete soft-deleted users after 30 days
 *  - Prune expired Sanctum tokens
 *  - Prune old sessions (> 2 × SESSION_LIFETIME)
 *  - Prune login_attempts older than 90 days
 *  - Prune expired booking holds
 *  - Compact audit_logs beyond retention window
 *  - Clear stale link_visits (> 1 year)
 *
 * Run: php artisan velour:prune-stale-data
 * Scheduled: daily at 03:00 UTC (console.php)
 */
class PruneStaleData extends Command
{
    protected $signature   = 'velour:prune-stale-data {--dry-run : Show counts without deleting}';
    protected $description = 'Prune expired/stale data according to retention policies';

    public function handle(): int
    {
        $dry = $this->option('dry-run');
        $this->info($dry ? '[DRY RUN] Stale data audit:' : 'Pruning stale data...');

        $pruned = [];

        // ── 1. Hard-delete users soft-deleted 30+ days ago ─────────────────
        $users = DB::table('users')
            ->whereNotNull('deleted_at')
            ->where('deleted_at', '<', now()->subDays(30));
        $pruned['deleted_users'] = $users->count();
        if (! $dry) $users->delete();

        // ── 2. Expired Sanctum tokens ──────────────────────────────────────
        $tokens = DB::table('personal_access_tokens')
            ->where(function ($q) {
                $q->where('expires_at', '<', now())
                  ->orWhere('last_used_at', '<', now()->subDays(90));
            });
        $pruned['expired_tokens'] = $tokens->count();
        if (! $dry) $tokens->delete();

        // ── 3. Stale sessions ──────────────────────────────────────────────
        $sessionLifetime = (int) config('session.lifetime', 120) * 2;
        $sessions = DB::table('sessions')
            ->where('last_activity', '<', now()->subMinutes($sessionLifetime)->timestamp);
        $pruned['stale_sessions'] = $sessions->count();
        if (! $dry) $sessions->delete();

        // ── 4. Login attempt logs older than 90 days ───────────────────────
        $attempts = DB::table('login_attempts')
            ->where('attempted_at', '<', now()->subDays(90));
        $pruned['old_login_attempts'] = $attempts->count();
        if (! $dry) $attempts->delete();

        // ── 5. Expired booking holds (status='hold', older than 15 min) ───
        $holds = DB::table('appointments')
            ->where('status', 'hold')
            ->where('created_at', '<', now()->subMinutes(15));
        $pruned['expired_holds'] = $holds->count();
        if (! $dry) $holds->delete();

        // ── 6. Audit logs beyond retention window ──────────────────────────
        $retentionDays = (int) config('security.audit_retention_days', 365);
        $auditLogs = DB::table('audit_logs')
            ->where('occurred_at', '<', now()->subDays($retentionDays));
        $pruned['old_audit_logs'] = $auditLogs->count();
        if (! $dry) $auditLogs->delete();

        // ── 7. Old link_visits (analytics, > 1 year) ──────────────────────
        $visits = DB::table('link_visits')
            ->where('created_at', '<', now()->subYear());
        $pruned['old_link_visits'] = $visits->count();
        if (! $dry) $visits->delete();

        // ── 8. Processed webhook_calls older than 90 days ─────────────────
        $webhooks = DB::table('webhook_calls')
            ->where('status', 'processed')
            ->where('created_at', '<', now()->subDays(90));
        $pruned['old_webhooks'] = $webhooks->count();
        if (! $dry) $webhooks->delete();

        // ── Summary ────────────────────────────────────────────────────────
        foreach ($pruned as $key => $count) {
            $this->line("  {$key}: " . ($dry ? "(would delete {$count})" : "deleted {$count}"));
        }

        $total = array_sum($pruned);
        $this->info($dry
            ? "DRY RUN complete. Would prune {$total} total records."
            : "Done. Pruned {$total} total records.");

        Log::info('PruneStaleData completed', array_merge($pruned, [
            'dry_run'    => $dry,
            'total'      => $total,
            'run_at'     => now()->toIso8601String(),
        ]));

        return self::SUCCESS;
    }
}
