<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

/**
 * HealthController — AUDIT FIX: Monitoring & Alert System
 *
 * GET /api/v1/health
 *
 * Returns a structured health JSON payload. Used by:
 *   - Docker HEALTHCHECK
 *   - Uptime Robot / Better Uptime / Pingdom
 *   - Kubernetes liveness + readiness probes
 *   - Load balancer health checks
 *
 * Response codes:
 *   200 — all healthy
 *   207 — degraded (some checks failed, service partially available)
 *   503 — critical check failed (database down)
 */
class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $checks  = [];
        $healthy = true;
        $start   = microtime(true);

        // ── Database ──────────────────────────────────────────────────────
        try {
            $dbStart = microtime(true);
            DB::select('SELECT 1');
            $checks['database'] = [
                'status'     => 'healthy',
                'latency_ms' => round((microtime(true) - $dbStart) * 1000, 2),
            ];
        } catch (\Throwable $e) {
            $checks['database'] = ['status' => 'unhealthy', 'error' => 'Connection failed'];
            $healthy = false; // Database is critical
        }

        // ── Cache/Redis ───────────────────────────────────────────────────
        try {
            $cacheStart = microtime(true);
            Cache::put('health_ping', 1, 10);
            Cache::get('health_ping');
            $checks['cache'] = [
                'status'     => 'healthy',
                'latency_ms' => round((microtime(true) - $cacheStart) * 1000, 2),
            ];
        } catch (\Throwable $e) {
            $checks['cache'] = ['status' => 'degraded', 'error' => 'Cache unavailable'];
        }

        // ── Queue ─────────────────────────────────────────────────────────
        try {
            $pending = DB::table('jobs')->count();
            $failed  = DB::table('failed_jobs')->count();
            $checks['queue'] = [
                'status'         => $failed < 50 ? 'healthy' : 'degraded',
                'pending_jobs'   => $pending,
                'failed_jobs'    => $failed,
            ];
        } catch (\Throwable $e) {
            $checks['queue'] = ['status' => 'unknown'];
        }

        // ── Scheduler heartbeat ───────────────────────────────────────────
        $lastScheduler = Cache::get('scheduler:last_run');
        $schedulerAge  = $lastScheduler ? now()->diffInSeconds($lastScheduler) : null;
        $checks['scheduler'] = [
            'status'    => (! $lastScheduler || $schedulerAge > 150) ? 'degraded' : 'healthy',
            'last_run'  => $lastScheduler?->toIso8601String(),
            'age_secs'  => $schedulerAge,
        ];

        // ── App info ──────────────────────────────────────────────────────
        $checks['app'] = [
            'status'      => 'healthy',
            'version'     => config('velour.version', '1.0.0'),
            'environment' => app()->environment(),
        ];

        $overallStatus = $healthy ? 'healthy' : 'unhealthy';
        $hasDegraded   = collect($checks)->contains(fn($c) => ($c['status'] ?? '') === 'degraded');
        if ($healthy && $hasDegraded) $overallStatus = 'degraded';

        $httpStatus = match($overallStatus) {
            'healthy'   => 200,
            'degraded'  => 207,
            default     => 503,
        };

        return response()->json([
            'status'        => $overallStatus,
            'timestamp'     => now()->toIso8601String(),
            'response_time' => round((microtime(true) - $start) * 1000, 2) . 'ms',
            'checks'        => $checks,
        ], $httpStatus)->header('Cache-Control', 'no-cache, no-store');
    }
}
