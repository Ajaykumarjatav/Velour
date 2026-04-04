<?php

namespace App\Services;

use App\Mail\TenantCancellationMail;
use App\Mail\TenantNewBookingMail;
use App\Mail\TenantRescheduleMail;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\MarketingCampaign;
use App\Models\SalonNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /* ── Public API (called by controllers & BookingService) ─────────────── */

    public function appointmentConfirmation(Appointment $appointment): void
    {
        $this->notifyTenantNewBooking($appointment);
    }

    public function appointmentReminder(Appointment $appointment): void
    {
        $this->createNotification($appointment->salon_id, 'reminder', [
            'title' => 'Appointment Reminder Sent',
            'body'  => "Reminder sent to {$appointment->client->first_name} for {$appointment->starts_at->format('D j M')}",
            'data'  => ['appointment_id' => $appointment->id],
        ]);
    }

    public function appointmentCancellation(Appointment $appointment): void
    {
        $this->notifyTenantCancellation($appointment);
    }

    public function appointmentRescheduled(Appointment $appointment, ?Carbon $originalStartsAt = null): void
    {
        $this->notifyTenantReschedule($appointment, $originalStartsAt ?? $appointment->starts_at);
    }

    public function requestReview(Appointment $appointment): void
    {
        // Queue email/SMS with review link in production
    }

    public function staffAlert(int $salonId, string $message, string $type = 'info'): void
    {
        $this->createNotification($salonId, $type, ['title' => $message, 'body' => null]);
    }

    public function sendDirectMessage(Client $client, array $data): void
    {
        // Route to Twilio (SMS) or Mailgun (email) via queued job
    }

    /**
     * Marketing campaign SMS — integrate Twilio/Vonage in production.
     */
    public function sendSms(Client $client, string $body): void
    {
        Log::info('Marketing SMS (stub)', [
            'client_id' => $client->id,
            'preview'   => mb_substr($body, 0, 120),
        ]);
    }

    /**
     * Marketing campaign email — integrate Mailgun/SES in production.
     */
    public function sendEmail(Client $client, MarketingCampaign $campaign): void
    {
        Log::info('Marketing email (stub)', [
            'client_id'    => $client->id,
            'campaign_id'  => $campaign->id,
            'subject'      => $campaign->subject,
        ]);
    }

    /* ── Tenant notification methods ─────────────────────────────────────── */

    /**
     * Notify the tenant of a new booking (in-app + email + optional SMS).
     */
    public function notifyTenantNewBooking(Appointment $appointment): void
    {
        // 1. In-app notification
        $this->createNotification($appointment->salon_id, 'appointment', [
            'title' => 'New Booking',
            'body'  => "{$appointment->client->first_name} {$appointment->client->last_name} — {$appointment->starts_at->format('D j M, g:ia')}",
            'data'  => ['appointment_id' => $appointment->id],
        ]);

        // 2. Email to tenant
        $recipient = $this->resolveTenantEmail($appointment);
        if ($recipient) {
            try {
                Mail::to($recipient)->queue(new TenantNewBookingMail($appointment));
            } catch (\Throwable $e) {
                Log::error('Tenant new-booking email failed', [
                    'appointment_id' => $appointment->id,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        // 3. Optional SMS
        $this->notifyTenantSms($appointment);
    }

    /**
     * Notify the tenant of a cancellation (in-app + email).
     */
    public function notifyTenantCancellation(Appointment $appointment): void
    {
        $this->createNotification($appointment->salon_id, 'cancellation', [
            'title' => 'Booking Cancelled',
            'body'  => "{$appointment->client->first_name} {$appointment->client->last_name} cancelled their {$appointment->starts_at->format('D j M')} appointment.",
            'data'  => ['appointment_id' => $appointment->id],
        ]);

        $recipient = $this->resolveTenantEmail($appointment);
        if ($recipient) {
            try {
                Mail::to($recipient)->queue(new TenantCancellationMail($appointment));
            } catch (\Throwable $e) {
                Log::error('Tenant cancellation email failed', [
                    'appointment_id' => $appointment->id,
                    'error'          => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Notify the tenant of a reschedule (in-app + email).
     */
    public function notifyTenantReschedule(Appointment $appointment, Carbon $originalStartsAt): void
    {
        $this->createNotification($appointment->salon_id, 'reschedule', [
            'title' => 'Booking Rescheduled',
            'body'  => "{$appointment->client->first_name} {$appointment->client->last_name} rescheduled to {$appointment->starts_at->format('D j M, g:ia')}.",
            'data'  => ['appointment_id' => $appointment->id],
        ]);

        $recipient = $this->resolveTenantEmail($appointment);
        if ($recipient) {
            try {
                Mail::to($recipient)->queue(new TenantRescheduleMail($appointment, $originalStartsAt));
            } catch (\Throwable $e) {
                Log::error('Tenant reschedule email failed', [
                    'appointment_id' => $appointment->id,
                    'error'          => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Send SMS to tenant if sms_new_booking_enabled setting is on.
     */
    public function notifyTenantSms(Appointment $appointment): void
    {
        try {
            $salon = $appointment->salon ?? $appointment->load('salon')->salon;
            if (! $salon?->getSetting('sms_new_booking_enabled')) {
                return;
            }

            $phone = $salon->phone;
            if (! $phone) {
                return;
            }

            // SMS body — Twilio/Vonage integration goes here
            $serviceName = $appointment->services->first()?->service?->name ?? 'appointment';
            $message = "New booking: {$appointment->client->first_name} {$appointment->client->last_name}"
                . " — {$serviceName}"
                . " on {$appointment->starts_at->format('D j M \a\t g:ia')}";

            Log::info('Tenant SMS notification (stub)', [
                'to'             => $phone,
                'message'        => $message,
                'appointment_id' => $appointment->id,
            ]);

            // TODO: dispatch(new SendSmsJob($phone, $message));
        } catch (\Throwable $e) {
            Log::error('Tenant SMS notification failed', [
                'appointment_id' => $appointment->id,
                'error'          => $e->getMessage(),
            ]);
        }
    }

    /* ── Private helpers ─────────────────────────────────────────────────── */

    /**
     * Resolve the tenant email: salon email → owner email → null.
     */
    private function resolveTenantEmail(Appointment $appointment): ?string
    {
        $salon = $appointment->salon ?? $appointment->load('salon')->salon;

        $email = $salon?->email
            ?: optional($salon?->owner)->email;

        if (! $email) {
            Log::warning('No tenant email found for notification', [
                'salon_id'       => $appointment->salon_id,
                'appointment_id' => $appointment->id,
            ]);
            return null;
        }

        return $email;
    }

    private function createNotification(int $salonId, string $type, array $payload): void
    {
        SalonNotification::create([
            'salon_id' => $salonId,
            'type'     => $type,
            'title'    => $payload['title'],
            'body'     => $payload['body'] ?? null,
            'data'     => $payload['data'] ?? null,
        ]);
    }
}
