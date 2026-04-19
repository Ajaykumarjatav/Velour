<?php

namespace App\Services;

use App\Mail\ClientBookingConfirmationMail;
use App\Mail\TenantCancellationMail;
use App\Mail\TenantNewBookingMail;
use App\Mail\TenantRescheduleMail;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\MarketingCampaign;
use App\Models\Salon;
use App\Models\SalonNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    private function notificationConfig(): NotificationConfigService
    {
        return app(NotificationConfigService::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function salonSettingsPluck(Salon $salon): array
    {
        return $salon->settings()->pluck('value', 'key')->all();
    }

    /* ── Public API (called by controllers & BookingService) ─────────────── */

    public function appointmentConfirmation(Appointment $appointment): void
    {
        $this->notifyTenantNewBooking($appointment);
        $this->sendClientBookingConfirmationIfEnabled($appointment);
    }

    public function appointmentReminder(Appointment $appointment): void
    {
        $this->createNotification($appointment->salon_id, 'reminder', [
            'title' => 'Appointment Reminder Sent',
            'body'  => "Reminder sent to {$appointment->client->first_name} for {$appointment->starts_at->format('D j M')}",
            'data'  => ['appointment_id' => $appointment->id],
        ]);
    }

    /**
     * Instant client email after online booking (queued; requires queue worker unless QUEUE_CONNECTION=sync).
     */
    public function sendClientBookingConfirmationIfEnabled(Appointment $appointment): void
    {
        $salon = $appointment->salon ?? $appointment->load('salon')->salon;
        if (! $salon) {
            return;
        }

        $cfg = $this->notificationConfig();
        $pluck = $this->salonSettingsPluck($salon);
        if (! $cfg->isRuleEnabled($salon, 'client_booking_confirmation_email', $pluck)) {
            return;
        }

        $appointment->loadMissing(['client', 'staff', 'services.service']);
        $client = $appointment->client;
        if (! $client?->email) {
            return;
        }

        $tpl = $cfg->templatesForRule($salon, 'client_booking_confirmation_email', $pluck);
        $ctx = $cfg->buildAppointmentContext($appointment);
        $subject = $cfg->render($tpl['email_subject'] ?? 'Booking confirmed', $ctx);
        $body = $cfg->render($tpl['email_body'] ?? '', $ctx);

        $bodyHtml = $this->clientConfirmationBodyAsHtml($body);

        try {
            Mail::to($client->email)->queue(new ClientBookingConfirmationMail($subject, $bodyHtml));
        } catch (\Throwable $e) {
            Log::error('Client booking confirmation email failed', [
                'appointment_id' => $appointment->id,
                'to'             => $client->email,
                'error'          => $e->getMessage(),
            ]);
        }
    }

    private function clientConfirmationBodyAsHtml(string $body): string
    {
        $body = trim($body);
        if ($body === '') {
            return '<p>Your booking is confirmed.</p>';
        }

        return '<p style="margin:0;font-family:system-ui,sans-serif;font-size:15px;line-height:1.6;color:#111827;">'
            . nl2br(htmlspecialchars($body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'))
            . '</p>';
    }

    /**
     * Scheduled reminder for one rule id (email or SMS). Returns whether a send was recorded.
     */
    public function sendClientScheduledReminder(Appointment $appointment, string $ruleId, array $settingsPluck): bool
    {
        $salon = $appointment->salon ?? $appointment->load('salon')->salon;
        if (! $salon) {
            return false;
        }

        $cfg = $this->notificationConfig();
        if (! $cfg->isRuleEnabled($salon, $ruleId, $settingsPluck)) {
            return false;
        }
        if ($cfg->shouldSkipForQuietHours($salon, $settingsPluck)) {
            return false;
        }

        $offset = $cfg->offsetHours($salon, $ruleId, $settingsPluck);
        $key = NotificationConfigService::dispatchKey($ruleId, $offset);
        $appointment->refresh();

        if ($cfg->hasDispatchKey($appointment, $key)) {
            return false;
        }

        $appointment->loadMissing(['client', 'staff', 'services.service']);
        $tpl = $cfg->templatesForRule($salon, $ruleId, $settingsPluck);
        $ctx = $cfg->buildAppointmentContext($appointment);

        if ($ruleId === 'client_appointment_reminder_email') {
            $client = $appointment->client;
            if (! $client?->email) {
                return false;
            }
            $subject = $cfg->render($tpl['email_subject'] ?? 'Reminder', $ctx);
            $body = $cfg->render($tpl['email_body'] ?? '', $ctx);
            Log::info('Client appointment reminder email (stub)', [
                'appointment_id' => $appointment->id,
                'to'             => $client->email,
                'subject'        => $subject,
                'preview'        => mb_substr($body, 0, 200),
            ]);
            $cfg->markDispatchKey($appointment->fresh(), $key);
            $this->appointmentReminder($appointment->fresh());

            return true;
        }

        if ($ruleId === 'client_appointment_reminder_sms') {
            $client = $appointment->client;
            if (! $client?->phone) {
                return false;
            }
            $sms = $cfg->render($tpl['sms_body'] ?? '', $ctx);
            Log::info('Client appointment reminder SMS (stub)', [
                'appointment_id' => $appointment->id,
                'to'             => $client->phone,
                'preview'        => mb_substr($sms, 0, 160),
            ]);
            $cfg->markDispatchKey($appointment->fresh(), $key);
            $this->appointmentReminder($appointment->fresh());

            return true;
        }

        return false;
    }

    public function notifyTenantNewClientRegistered(Salon $salon, Client $client): void
    {
        $pluck = $this->salonSettingsPluck($salon);
        $cfg = $this->notificationConfig();
        if (! $cfg->isRuleEnabled($salon, 'tenant_new_client_email', $pluck)) {
            return;
        }

        $tpl = $cfg->templatesForRule($salon, 'tenant_new_client_email', $pluck);
        $ctx = $cfg->buildClientContext($client, $salon);
        $subject = $cfg->render($tpl['email_subject'] ?? 'New client', $ctx);
        $body = $cfg->render($tpl['email_body'] ?? '', $ctx);

        $this->createNotification($salon->id, 'client', [
            'title' => $subject,
            'body'  => trim($client->first_name.' '.$client->last_name).' — '.($client->email ?: $client->phone ?: ''),
            'data'  => ['client_id' => $client->id],
        ]);

        $recipient = $salon->email ?: optional($salon->owner)->email;
        if ($recipient) {
            try {
                Log::info('Tenant new-client email (stub)', [
                    'salon_id' => $salon->id,
                    'to'       => $recipient,
                    'subject'  => $subject,
                    'preview'  => mb_substr($body, 0, 200),
                ]);
            } catch (\Throwable $e) {
                Log::error('Tenant new-client email failed', ['error' => $e->getMessage()]);
            }
        }
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
