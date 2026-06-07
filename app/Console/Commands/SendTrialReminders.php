<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TrialEndingNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * SendTrialReminders — AUDIT FIX: SaaS Essential
 *
 * Sends proactive trial expiry emails:
 *   - 7 days before trial ends
 *   - 3 days before trial ends
 *   - 1 day before trial ends
 *   - On the day of expiry
 *
 * Scheduled: daily at 09:00 UTC (console.php)
 */
class SendTrialReminders extends Command
{
    protected $signature   = 'velour:send-trial-reminders';
    protected $description = 'Send trial ending notification emails';

    public function handle(): int
    {
        $reminders = [
            7 => 'trial_ending_7d',
            3 => 'trial_ending_3d',
            1 => 'trial_ending_1d',
            0 => 'trial_ended',
        ];

        $sent = 0;

        foreach ($reminders as $daysLeft => $type) {
            $users = User::query()
                ->whereNotNull('trial_ends_at')
                ->where('is_active', true)
                ->whereDoesntHave('subscription', fn($q) => $q->where('stripe_status', 'active'))
                ->when($daysLeft === 0,
                    fn($q) => $q->whereDate('trial_ends_at', today()),
                    fn($q) => $q->whereDate('trial_ends_at', today()->addDays($daysLeft))
                )
                ->get();

            foreach ($users as $user) {
                try {
                    $user->notify(new TrialEndingNotification($daysLeft));
                    $sent++;
                    Log::info("Trial reminder sent [{$type}]", [
                        'user_id'       => $user->id,
                        'trial_ends_at' => $user->trial_ends_at,
                    ]);
                } catch (\Throwable $e) {
                    Log::error("Failed to send trial reminder", [
                        'user_id' => $user->id,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("Sent {$sent} trial reminder(s).");
        return self::SUCCESS;
    }
}
