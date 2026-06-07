<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

/**
 * HealthCheck — AUDIT FIX: Monitoring & Alert System
 *
 * Comprehensive health probe: database, Redis, queue, storage, Stripe.
 * Run: php artisan velour:health
 * Used by: docker HEALTHCHECK, uptime monitors, /api/v1/health endpoint
 */
class HealthCheck extends Command
{
    protected $signature   = 'velour:health {--format=text : Output format (text|json)}';
    protected $description = 'Run a comprehensive system health check';

    private array $results = [];

    public function handle(): int
    {
        $this->checkDatabase();
        $this->checkRedis();
        $this->checkQueue();
        $this->checkStorage();
        $this->checkStripe();
        $this->checkScheduler();

        $allHealthy = collect($this->results)->every(fn($r) => $r['healthy']);
        $status     = $allHealthy ? 'healthy' : 'degraded';

        if ($this->option('format') === 'json') {
            $this->line(json_encode([
                'status'    => $status,
                'timestamp' => now()->toIso8601String(),
                'checks'    => $this->results,
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info("Velour Health Check — " . now()->toIso8601String());
            $this->line(str_repeat('─', 50));
            foreach ($this->results as $name => $r) {
                $icon = $r['healthy'] ? '✅' : '❌';
                $msg  = $r['message'] ?? ($r['healthy'] ? 'OK' : 'FAILED');
                $time = isset($r['latency_ms']) ? " ({$r['latency_ms']}ms)" : '';
                $this->line("  {$icon}  {$name}: {$msg}{$time}");
            }
            $this->line(str_repeat('─', 50));
            $this->info("Overall: " . strtoupper($status));
        }

        return $allHealthy ? self::SUCCESS : self::FAILURE;
    }

    private function checkDatabase(): void
    {
        $start = microtime(true);
        try {
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 2);
            $count   = DB::table('users')->count();
            $this->results['database'] = [
                'healthy'    => true,
                'message'    => "Connected — {$count} users",
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            $this->results['database'] = ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkRedis(): void
    {
        $start = microtime(true);
        try {
            $key = 'health:ping:' . time();
            Cache::put($key, 'pong', 5);
            $val = Cache::get($key);
            Cache::forget($key);
            $latency = round((microtime(true) - $start) * 1000, 2);
            $this->results['redis'] = [
                'healthy'    => $val === 'pong',
                'message'    => $val === 'pong' ? 'Connected' : 'Read/write mismatch',
                'latency_ms' => $latency,
            ];
        } catch (\Throwable $e) {
            $this->results['redis'] = ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkQueue(): void
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed  = DB::table('failed_jobs')->count();
            $healthy = $failed < 50; // alert if > 50 failed jobs
            $this->results['queue'] = [
                'healthy' => $healthy,
                'message' => "Pending: {$pending}, Failed: {$failed}" . ($healthy ? '' : ' ⚠ high failure count'),
            ];
        } catch (\Throwable $e) {
            $this->results['queue'] = ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkStorage(): void
    {
        try {
            $testFile = 'health-check-' . time() . '.txt';
            Storage::put($testFile, 'ok');
            $content = Storage::get($testFile);
            Storage::delete($testFile);
            $this->results['storage'] = [
                'healthy' => $content === 'ok',
                'message' => $content === 'ok' ? 'Read/write OK' : 'Read/write mismatch',
            ];
        } catch (\Throwable $e) {
            $this->results['storage'] = ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkStripe(): void
    {
        $key = config('cashier.secret');
        if (! $key || str_starts_with($key, 'sk_test_dummy')) {
            $this->results['stripe'] = ['healthy' => true, 'message' => 'Skipped (test/no key)'];
            return;
        }
        try {
            $client = new \Stripe\StripeClient($key);
            $client->balance->retrieve();
            $this->results['stripe'] = ['healthy' => true, 'message' => 'Connected'];
        } catch (\Throwable $e) {
            $this->results['stripe'] = ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkScheduler(): void
    {
        // Check that scheduler last ran within last 2 minutes
        $lastRun = Cache::get('scheduler:last_run');
        if (! $lastRun) {
            $this->results['scheduler'] = ['healthy' => true, 'message' => 'No data yet (first boot?)'];
            return;
        }
        $ageSeconds = now()->diffInSeconds($lastRun);
        $healthy    = $ageSeconds < 150; // allow 2.5 min tolerance
        $this->results['scheduler'] = [
            'healthy' => $healthy,
            'message' => "Last run {$ageSeconds}s ago" . ($healthy ? '' : ' ⚠ scheduler may be down'),
        ];
    }
}
