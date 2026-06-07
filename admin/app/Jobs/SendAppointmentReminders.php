<?php
namespace App\Jobs;
use App\Models\Appointment;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function handle(NotificationService $notificationService): void
    {
        $windowStart = Carbon::now()->addHours(23)->addMinutes(30);
        $windowEnd   = Carbon::now()->addHours(24)->addMinutes(30);

        Appointment::with(['client', 'staff', 'services'])
            ->whereBetween('starts_at', [$windowStart, $windowEnd])
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('reminder_sent', false)
            ->chunk(50, function ($appointments) use ($notificationService) {
                foreach ($appointments as $appointment) {
                    try {
                        $notificationService->appointmentReminder($appointment);
                        $appointment->update(['reminder_sent' => true]);
                    } catch (\Exception $e) {
                        Log::warning("Reminder failed for appointment {$appointment->id}: " . $e->getMessage());
                    }
                }
            });
    }
}
