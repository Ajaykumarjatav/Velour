<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * SendAppointmentReminders — AUDIT FIX: Notification System
 *
 * Sends SMS/email reminders to clients before appointments.
 * Reminder windows configured in config/velour.php booking.reminder_hours_before.
 * Default: [24, 2] — reminds 24h before and 2h before.
 *
 * Scheduled: every 15 minutes (console.php)
 */
class SendAppointmentReminders extends Command
{
    protected $signature   = 'velour:send-appointment-reminders';
    protected $description = 'Send appointment reminder notifications to clients';

    public function __construct(private NotificationService $notificationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $windows = config('velour.booking.reminder_hours_before', [24, 2]);
        $sent    = 0;
        $failed  = 0;

        foreach ($windows as $hoursAhead) {
            $windowStart = now()->addHours($hoursAhead)->subMinutes(8);
            $windowEnd   = now()->addHours($hoursAhead)->addMinutes(7);

            $appointments = Appointment::with(['client', 'staff', 'salon', 'services'])
                ->where('status', 'confirmed')
                ->whereBetween('starts_at', [$windowStart, $windowEnd])
                ->where('reminder_sent', false)
                ->get();

            foreach ($appointments as $appointment) {
                try {
                    $this->notificationService->appointmentReminder($appointment);

                    $appointment->update(['reminder_sent' => true, 'reminder_sent_at' => now()]);
                    $sent++;

                    Log::info('Appointment reminder sent', [
                        'appointment_id' => $appointment->id,
                        'client_id'      => $appointment->client_id,
                        'starts_at'      => $appointment->starts_at,
                        'hours_ahead'    => $hoursAhead,
                    ]);
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('Failed to send appointment reminder', [
                        'appointment_id' => $appointment->id,
                        'error'          => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("Reminders sent: {$sent}, failed: {$failed}");
        return self::SUCCESS;
    }
}
