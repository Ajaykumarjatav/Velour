<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

/*
|──────────────────────────────────────────────────────────────────────────────
| VELOUR — Scheduled Tasks
|──────────────────────────────────────────────────────────────────────────────
|
| All Laravel scheduled commands.
| The scheduler itself is invoked by supervisord every minute via:
|   php artisan schedule:run
|
*/

// ── Heartbeat — record scheduler liveness for health check ────────────────
Schedule::call(function () {
    Cache::put('scheduler:last_run', now(), 300);
})->everyMinute()->name('scheduler-heartbeat')->withoutOverlapping();

// ── Appointment reminders (24h + 2h before) ───────────────────────────────
Schedule::command('velour:send-appointment-reminders')
    ->everyFifteenMinutes()
    ->name('appointment-reminders')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(fn() => \Illuminate\Support\Facades\Log::critical('appointment-reminders scheduler failed'));

// ── Trial ending notifications ─────────────────────────────────────────────
Schedule::command('velour:send-trial-reminders')
    ->dailyAt('09:00')
    ->name('trial-reminders')
    ->withoutOverlapping();

// ── Queue failed jobs monitor ──────────────────────────────────────────────
Schedule::command('queue:prune-failed --hours=168')  // keep 7 days
    ->daily()
    ->name('prune-failed-jobs');

// ── Database cleanup ───────────────────────────────────────────────────────
Schedule::command('velour:prune-stale-data')
    ->dailyAt('03:00')
    ->name('prune-stale-data')
    ->withoutOverlapping();

// ── Billing reconciliation (1st of each month) ────────────────────────────
Schedule::command('velour:reconcile-billing')
    ->monthlyOn(1, '06:00')
    ->name('billing-reconciliation')
    ->withoutOverlapping();

// ── SSL certificate check (alert 30 days before expiry) ───────────────────
Schedule::command('velour:check-ssl')
    ->weekly()
    ->name('ssl-check');

// ── Telescope pruning (dev only) ──────────────────────────────────────────
if (app()->environment('local')) {
    Schedule::command('telescope:prune --hours=48')->daily();
}
