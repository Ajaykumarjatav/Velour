<?php
namespace App\Jobs;

use App\Models\Appointment;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 30;
    public int $backoff = 60;

    public function __construct(public readonly Appointment $appointment) {}

    public function handle(NotificationService $notifications): void
    {
        if (! in_array($this->appointment->status, ['confirmed', 'pending'])) {
            return; // Don't remind for cancelled/completed appointments
        }

        $notifications->appointmentReminder($this->appointment);

        $this->appointment->update(['reminder_sent' => true, 'reminder_sent_at' => now()]);

        Log::info('Appointment reminder sent', ['appointment_id' => $this->appointment->id]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send appointment reminder', [
            'appointment_id' => $this->appointment->id,
            'error'          => $exception->getMessage(),
        ]);
    }
}
