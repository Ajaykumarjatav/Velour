<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\NotificationConfigService;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Sends client email/SMS reminders using per-salon notification rules and offsets.
 */
class SendAppointmentReminders extends Command
{
    protected $signature = 'velour:send-appointment-reminders';

    protected $description = 'Send appointment reminder notifications to clients';

    private const SCHEDULED_RULES = [
        'client_appointment_reminder_email',
        'client_appointment_reminder_sms',
    ];

    public function __construct(
        private NotificationService $notificationService,
        private NotificationConfigService $notificationConfig,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $sent = 0;
        $failed = 0;

        /** @var array<int, array<string, mixed>> $pluckCache */
        $pluckCache = [];

        Appointment::query()
            ->with(['client', 'staff', 'salon', 'services.service'])
            ->where('status', 'confirmed')
            ->where('starts_at', '>', now())
            ->where('starts_at', '<', now()->addDays(14))
            ->orderBy('starts_at')
            ->chunkById(100, function ($chunk) use (&$pluckCache, &$sent, &$failed) {
                foreach ($chunk as $appointment) {
                    $salonId = $appointment->salon_id;
                    if (! isset($pluckCache[$salonId])) {
                        $pluckCache[$salonId] = $appointment->salon
                            ? $appointment->salon->settings()->pluck('value', 'key')->all()
                            : [];
                    }
                    $pluck = $pluckCache[$salonId];

                    foreach (self::SCHEDULED_RULES as $ruleId) {
                        $hoursAhead = $this->notificationConfig->offsetHours($appointment->salon, $ruleId, $pluck);
                        $windowStart = now()->addHours($hoursAhead)->subMinutes(8);
                        $windowEnd = now()->addHours($hoursAhead)->addMinutes(7);

                        if ($appointment->starts_at->lt($windowStart) || $appointment->starts_at->gt($windowEnd)) {
                            continue;
                        }

                        try {
                            if ($this->notificationService->sendClientScheduledReminder($appointment, $ruleId, $pluck)) {
                                $sent++;
                                Log::info('Appointment reminder dispatched', [
                                    'appointment_id' => $appointment->id,
                                    'rule'           => $ruleId,
                                    'hours_ahead'    => $hoursAhead,
                                ]);
                            }
                        } catch (\Throwable $e) {
                            $failed++;
                            Log::error('Failed to send appointment reminder', [
                                'appointment_id' => $appointment->id,
                                'rule'           => $ruleId,
                                'error'          => $e->getMessage(),
                            ]);
                        }
                    }
                }
            });

        $this->info("Reminders sent: {$sent}, failed: {$failed}");

        return self::SUCCESS;
    }
}
