<?php

namespace App\Services;

use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\InventoryItem;
use App\Models\Voucher;
use App\Models\Client;
use App\Models\Staff;
use App\Models\Appointment;
use App\Models\SalonNotification;
use App\Models\MarketingCampaign;
use App\Models\LinkVisit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class NotificationService
{
    public function appointmentConfirmation(\App\Models\Appointment $appointment): void
    {
        $this->createNotification($appointment->salon_id, 'appointment', [
            'title' => 'Booking Confirmed',
            'body'  => "{$appointment->client->first_name} — {$appointment->starts_at->format('D j M, g:ia')}",
            'data'  => ['appointment_id' => $appointment->id],
        ]);
        // SMS / Email dispatch would go here via queued jobs
    }

    public function appointmentReminder(\App\Models\Appointment $appointment): void
    {
        $this->createNotification($appointment->salon_id, 'reminder', [
            'title' => 'Appointment Reminder Sent',
            'body'  => "Reminder sent to {$appointment->client->first_name} for {$appointment->starts_at->format('D j M')}",
            'data'  => ['appointment_id' => $appointment->id],
        ]);
    }

    public function appointmentCancellation(\App\Models\Appointment $appointment): void
    {
        $this->createNotification($appointment->salon_id, 'cancellation', [
            'title' => 'Appointment Cancelled',
            'body'  => "{$appointment->client->first_name} cancelled their {$appointment->starts_at->format('D j M')} appointment.",
            'data'  => ['appointment_id' => $appointment->id],
        ]);
    }

    public function appointmentRescheduled(\App\Models\Appointment $appointment): void
    {
        $this->createNotification($appointment->salon_id, 'reschedule', [
            'title' => 'Appointment Rescheduled',
            'body'  => "{$appointment->client->first_name} rescheduled to {$appointment->starts_at->format('D j M, g:ia')}.",
            'data'  => ['appointment_id' => $appointment->id],
        ]);
    }

    public function requestReview(\App\Models\Appointment $appointment): void
    {
        // Queue email/SMS with review link in production
    }

    public function staffAlert(int $salonId, string $message, string $type = 'info'): void
    {
        $this->createNotification($salonId, $type, ['title' => $message, 'body' => null]);
    }

    public function sendDirectMessage(\App\Models\Client $client, array $data): void
    {
        // Route to Twilio (SMS) or Mailgun (email) via queued job
    }

    private function createNotification(int $salonId, string $type, array $payload): void
    {
        \App\Models\SalonNotification::create([
            'salon_id' => $salonId,
            'type'     => $type,
            'title'    => $payload['title'],
            'body'     => $payload['body'] ?? null,
            'data'     => $payload['data'] ?? null,
        ]);
    }
}
