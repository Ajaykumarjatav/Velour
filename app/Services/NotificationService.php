<?php

namespace App\Services;

use App\Jobs\SendWhatsAppNotification;
use App\Mail\ClientBookingConfirmationMail;
use App\Mail\StaffAlertMail;
use App\Mail\TenantCancellationMail;
use App\Mail\TenantNewBookingMail;
use App\Mail\TenantRescheduleMail;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Expense;
use App\Models\MarketingCampaign;
use App\Models\PosTransaction;
use App\Models\Salon;
use App\Models\SalonActionItem;
use App\Models\SalonNotification;
use App\Models\Staff;
use App\Models\StaffLeaveRequest;
use App\Support\SalonUrl;
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
        if ($appointment->status === 'pending') {
            $this->notifyClientBookingReceived($appointment);
        } else {
            $this->notifyClientBookingConfirmed($appointment);
        }
    }

    /**
     * Email the client when a booking request is submitted (pending approval).
     */
    public function notifyClientBookingReceived(Appointment $appointment): void
    {
        $appointment->loadMissing(['client', 'staff', 'services.service', 'salon']);
        $client = $appointment->client;
        $salon = $appointment->salon;
        if (! $client?->email || ! $salon) {
            return;
        }

        $cfg = $this->notificationConfig();
        $ctx = $cfg->buildAppointmentContext($appointment);
        $subject = $cfg->render('Booking request received — {{reference}}', $ctx);
        $body = $cfg->render(
            "Hi {{client_first_name}},\n\nWe received your booking request at {{salon_name}} for {{appointment_date}} at {{appointment_time}} with {{staff_name}}.\nServices: {{service_names}}\nReference: {{reference}}\n\nThe salon will confirm shortly.",
            $ctx
        );

        $this->queueClientHtmlMail($client->email, $subject, $body, $appointment->id, 'Client booking-received email failed');
    }

    /**
     * Email / WhatsApp to the client after their booking is confirmed (admin or instant online).
     */
    public function notifyClientBookingConfirmed(Appointment $appointment, bool $forceEmail = false): void
    {
        $this->sendClientBookingConfirmationIfEnabled($appointment, $forceEmail);
        $this->sendClientBookingConfirmationWhatsAppIfEnabled($appointment);
    }

    public function appointmentReminder(Appointment $appointment): void
    {
        $this->createNotification($appointment->salon_id, 'reminder', [
            'title' => 'Appointment Reminder Sent',
            'body'  => "Reminder sent to {$appointment->client->first_name} for {$appointment->starts_at->format('D j M')}",
            'staff_id' => $appointment->staff_id,
            'data'  => ['appointment_id' => $appointment->id],
        ]);
    }

    /**
     * Instant client email after online booking (queued; requires queue worker unless QUEUE_CONNECTION=sync).
     * Also sends when only the WhatsApp confirmation rule is on, so every confirmation channel emails the client.
     */
    public function sendClientBookingConfirmationIfEnabled(Appointment $appointment, bool $forceEmail = false): void
    {
        $salon = $appointment->salon ?? $appointment->load('salon')->salon;
        if (! $salon) {
            return;
        }

        $cfg = $this->notificationConfig();
        $pluck = $this->salonSettingsPluck($salon);
        $emailOn = $cfg->isRuleEnabled($salon, 'client_booking_confirmation_email', $pluck);
        $whatsAppOn = $cfg->isRuleEnabled($salon, 'client_booking_confirmation_whatsapp', $pluck);
        if (! $forceEmail && ! $emailOn && ! $whatsAppOn) {
            return;
        }

        $appointment->loadMissing(['client', 'staff', 'services.service']);
        $client = $appointment->client;
        if (! $client?->email) {
            return;
        }

        $ctx = $cfg->buildAppointmentContext($appointment);
        if ($emailOn) {
            $tpl = $cfg->templatesForRule($salon, 'client_booking_confirmation_email', $pluck);
            $subject = $cfg->render($tpl['email_subject'] ?? 'Booking confirmed', $ctx);
            $body = $cfg->render($tpl['email_body'] ?? '', $ctx);
        } else {
            $tpl = $cfg->templatesForRule($salon, 'client_booking_confirmation_whatsapp', $pluck);
            $subject = $cfg->render('Your appointment at {{salon_name}}', $ctx);
            $body = $cfg->render($tpl['whatsapp_body'] ?? '', $ctx);
        }

        $this->queueClientHtmlMail(
            $client->email,
            $subject,
            $body,
            $appointment->id,
            'Client booking confirmation email failed'
        );
    }

    private function queueClientHtmlMail(string $to, string $subject, string $body, int $appointmentId, string $logLabel): void
    {
        try {
            Mail::to($to)->queue(new ClientBookingConfirmationMail($subject, $this->clientConfirmationBodyAsHtml($body)));
        } catch (\Throwable $e) {
            Log::error($logLabel, [
                'appointment_id' => $appointmentId,
                'to'             => $to,
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
     * Instant client WhatsApp after booking (queued; requires Twilio WhatsApp sender).
     */
    public function sendClientBookingConfirmationWhatsAppIfEnabled(Appointment $appointment): void
    {
        $salon = $appointment->salon ?? $appointment->load('salon')->salon;
        if (! $salon) {
            return;
        }

        $cfg = $this->notificationConfig();
        $pluck = $this->salonSettingsPluck($salon);
        if (! $cfg->isRuleEnabled($salon, 'client_booking_confirmation_whatsapp', $pluck)) {
            return;
        }

        $appointment->loadMissing(['client', 'staff', 'services.service']);
        $client = $appointment->client;
        if (! $client?->phone) {
            return;
        }

        $tpl = $cfg->templatesForRule($salon, 'client_booking_confirmation_whatsapp', $pluck);
        $ctx = $cfg->buildAppointmentContext($appointment);
        $body = $cfg->render($tpl['whatsapp_body'] ?? '', $ctx);
        if (trim($body) === '') {
            return;
        }

        try {
            SendWhatsAppNotification::dispatch($client->phone, $body, $client->id);
        } catch (\Throwable $e) {
            Log::error('Client booking confirmation WhatsApp failed', [
                'appointment_id' => $appointment->id,
                'to'             => $client->phone,
                'error'          => $e->getMessage(),
            ]);
        }
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
            try {
                Mail::to($client->email)->queue(new ClientBookingConfirmationMail($subject, $this->clientConfirmationBodyAsHtml($body)));
            } catch (\Throwable $e) {
                Log::error('Client appointment reminder email failed', [
                    'appointment_id' => $appointment->id,
                    'error'          => $e->getMessage(),
                ]);
            }
            $cfg->markDispatchKey($appointment->fresh(), $key);
            $this->appointmentReminder($appointment->fresh());

            return true;
        }

        if ($ruleId === 'client_appointment_reminder_sms') {
            $client = $appointment->client;
            if (! $client?->phone && ! $client?->email) {
                return false;
            }

            $sms = $cfg->render($tpl['sms_body'] ?? '', $ctx);
            if ($client?->phone) {
                Log::info('Client appointment reminder SMS (stub)', [
                    'appointment_id' => $appointment->id,
                    'to'             => $client->phone,
                    'preview'        => mb_substr($sms, 0, 160),
                ]);
            }

            // Always email as well (SMS gateway may be stub). Skip if email reminder rule already covers it.
            $emailRuleOn = $cfg->isRuleEnabled($salon, 'client_appointment_reminder_email', $settingsPluck);
            if ($client?->email && ! $emailRuleOn) {
                $emailTpl = $cfg->templatesForRule($salon, 'client_appointment_reminder_email', $settingsPluck);
                $subject = $cfg->render($emailTpl['email_subject'] ?? 'Reminder', $ctx);
                $body = trim((string) ($emailTpl['email_body'] ?? '')) !== ''
                    ? $cfg->render($emailTpl['email_body'], $ctx)
                    : $sms;
                try {
                    Mail::to($client->email)->queue(new ClientBookingConfirmationMail(
                        $subject,
                        $this->clientConfirmationBodyAsHtml($body)
                    ));
                } catch (\Throwable $e) {
                    Log::error('Client appointment reminder SMS→email failed', [
                        'appointment_id' => $appointment->id,
                        'error'          => $e->getMessage(),
                    ]);
                }
            }

            if (! $client?->phone && ! ($client?->email && ! $emailRuleOn)) {
                return false;
            }

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
                Mail::to($recipient)->queue(new ClientBookingConfirmationMail(
                    $subject,
                    $this->clientConfirmationBodyAsHtml($body)
                ));
            } catch (\Throwable $e) {
                Log::error('Tenant new-client email failed', [
                    'salon_id' => $salon->id,
                    'to'       => $recipient,
                    'error'    => $e->getMessage(),
                ]);
            }
        }
    }

    public function appointmentCancellation(Appointment $appointment, bool $declinedRequest = false): void
    {
        $this->notifyTenantCancellation($appointment);
        $this->sendClientCancellationConfirmation($appointment, $declinedRequest);
    }

    public function sendClientCancellationConfirmation(Appointment $appointment, bool $declinedRequest = false): void
    {
        $appointment->loadMissing(['client', 'salon', 'staff', 'services.service']);
        $client = $appointment->client;
        if (! $client?->email) {
            return;
        }

        $salon = $appointment->salon;
        if (! $salon) {
            return;
        }

        $cfg = $this->notificationConfig();
        $ctx = $cfg->buildAppointmentContext($appointment);
        $subject = $cfg->render(
            $declinedRequest ? 'Booking request declined — {{reference}}' : 'Appointment cancelled — {{reference}}',
            $ctx
        );
        $body = $cfg->render(
            $declinedRequest
                ? "Hi {{client_first_name}},\n\nYour booking request on {{appointment_date}} at {{appointment_time}} was not approved.\n\nRef: {{reference}}\n\n{{salon_name}}"
                : "Hi {{client_first_name}},\n\nYour appointment on {{appointment_date}} at {{appointment_time}} has been cancelled.\n\nRef: {{reference}}",
            $ctx
        );

        $this->queueClientHtmlMail($client->email, $subject, $body, $appointment->id, 'Client cancellation email failed');
    }

    public function sendClientRescheduleConfirmation(Appointment $appointment, Carbon $originalStartsAt): void
    {
        $appointment->loadMissing(['client', 'salon', 'staff', 'services.service']);
        $client = $appointment->client;
        if (! $client?->email) {
            return;
        }

        $salon = $appointment->salon;
        if (! $salon) {
            return;
        }

        $cfg = $this->notificationConfig();
        $ctx = $cfg->buildAppointmentContext($appointment);
        $subject = $cfg->render('Appointment rescheduled — {{reference}}', $ctx);
        $body = $cfg->render(
            "Hi {{client_first_name}},\n\nYour appointment has been rescheduled to {{appointment_date}} at {{appointment_time}}.\n\nRef: {{reference}}",
            $ctx
        );

        $this->queueClientHtmlMail($client->email, $subject, $body, $appointment->id, 'Client reschedule email failed');
    }

    public function appointmentRescheduled(Appointment $appointment, ?Carbon $originalStartsAt = null): void
    {
        $original = $originalStartsAt ?? $appointment->starts_at;
        $this->notifyTenantReschedule($appointment, $original);
        $this->sendClientRescheduleConfirmation($appointment, $original);
    }

    public function requestReview(Appointment $appointment): void
    {
        // Queue email/SMS with review link in production
    }

    public function staffAlert(int $salonId, string $message, string $type = 'info'): void
    {
        $this->createNotification($salonId, $type, ['title' => $message, 'body' => null]);
    }

    /**
     * In-app alert when a POS / checkout sale is completed.
     */
    public function notifyPosSaleCompleted(PosTransaction $transaction): void
    {
        $transaction->loadMissing(['client', 'staff', 'items', 'salon']);
        if ($transaction->status !== 'completed') {
            return;
        }

        $salon = $transaction->salon;
        if (! $salon) {
            return;
        }

        $client = trim(($transaction->client?->first_name ?? '').' '.($transaction->client?->last_name ?? ''));
        if ($client === '') {
            $client = 'Walk-in';
        }

        $amount = \App\Helpers\CurrencyHelper::format((float) $transaction->total, $salon->currency ?? 'INR');
        $pay = ucfirst(str_replace('_', ' ', (string) ($transaction->payment_method ?: 'cash')));
        $itemNames = $transaction->items->pluck('name')->filter()->unique()->take(3)->implode(', ');
        $ref = $transaction->reference ?: ('#'.$transaction->id);
        $url = route('pos.show', ['po' => $transaction->id, 'store' => SalonUrl::key($salon)]);

        $this->createNotification((int) $transaction->salon_id, 'sale', [
            'title'      => 'Sale completed',
            'body'       => "{$client} — {$amount} · {$pay}".($itemNames !== '' ? " · {$itemNames}" : '')." · {$ref}",
            'action_url' => $url,
            'data'       => [
                'pos_transaction_id' => $transaction->id,
                'action_label'       => 'Invoice',
            ],
        ]);
    }

    /**
     * Email + in-app when a desk task is assigned to a staff member.
     */
    public function notifyStaffTaskAssigned(SalonActionItem $item, ?int $previousAssigneeId = null): void
    {
        $item->loadMissing(['assignedStaff.user', 'salon']);
        $staff = $item->assignedStaff;
        if (! $staff || ! $item->assigned_staff_id) {
            return;
        }

        if ($previousAssigneeId !== null && (int) $previousAssigneeId === (int) $staff->id) {
            return;
        }

        $salon = $item->salon;
        $kind = SalonActionItem::kindLabels()[$item->kind] ?? $item->kind;
        $due = $item->due_at ? $item->due_at->timezone(\App\Support\SalonTime::timezone($salon))->format('D j M Y') : 'No due date';
        $tasksUrl = route('tasks.index', ['store' => SalonUrl::key($salon)]);

        $this->createNotification((int) $item->salon_id, 'task', [
            'title' => 'New task assigned to you',
            'body'  => $item->title,
            'staff_id' => $staff->id,
            'action_url' => $tasksUrl,
            'data'  => ['task_id' => $item->id, 'action_label' => 'tasks'],
        ]);

        $this->mailStaff($staff, $salon, new StaffAlertMail(
            staffName: $staff->name,
            salonName: $salon->name,
            subjectLine: "Task assigned: {$item->title}",
            headline: '✅ New task assigned',
            lines: array_values(array_filter([
                "Type: {$kind}",
                "Priority: ".ucfirst((string) $item->priority),
                "Due: {$due}",
                $item->body ? 'Details: '.$item->body : null,
            ])),
            actionUrl: $tasksUrl,
            actionLabel: 'View tasks',
        ));
    }

    /**
     * Email assigned staff about a new booking on their calendar.
     * (In-app row is already created by notifyTenantNewBooking with staff_id.)
     */
    public function notifyStaffBookingAssigned(Appointment $appointment): void
    {
        $appointment->loadMissing(['client', 'staff.user', 'services', 'salon']);
        $staff = $appointment->staff;
        if (! $staff) {
            return;
        }

        $salon = $appointment->salon;
        $client = trim(($appointment->client?->first_name ?? '').' '.($appointment->client?->last_name ?? '')) ?: 'Client';
        $when = $appointment->starts_at->timezone(\App\Support\SalonTime::timezone($salon))->format('D j M Y, g:ia');
        $services = $appointment->services->pluck('service_name')->filter()->implode(', ') ?: 'Appointment';
        $url = route('appointments.show', ['appointment' => $appointment->id, 'store' => SalonUrl::key($salon)]);

        $this->mailStaff($staff, $salon, new StaffAlertMail(
            staffName: $staff->name,
            salonName: $salon->name,
            subjectLine: "New booking: {$client} — {$when}",
            headline: '📅 Booking assigned to you',
            lines: [
                "Client: {$client}",
                "When: {$when}",
                "Service: {$services}",
            ],
            actionUrl: $url,
            actionLabel: 'Open appointment',
        ));
    }

    public function notifyStaffLeaveApproved(StaffLeaveRequest $leave): void
    {
        $leave->loadMissing(['staff.user', 'salon']);
        $staff = $leave->staff;
        if (! $staff) {
            return;
        }

        $salon = $leave->salon;
        $from = $leave->start_date->format('j M Y');
        $to = $leave->end_date->format('j M Y');
        $url = route('availability.index', ['store' => SalonUrl::key($salon), 'tab' => 'leave']);

        $this->createNotification((int) $leave->salon_id, 'leave', [
            'title' => 'Leave approved',
            'body'  => "{$leave->leave_type}: {$from} → {$to}",
            'staff_id' => $staff->id,
            'action_url' => $url,
            'data'  => ['leave_id' => $leave->id, 'action_label' => 'leave'],
        ]);

        $this->mailStaff($staff, $salon, new StaffAlertMail(
            staffName: $staff->name,
            salonName: $salon->name,
            subjectLine: "Leave approved: {$leave->leave_type}",
            headline: '🏖 Leave approved',
            lines: [
                "Type: {$leave->leave_type}",
                "From: {$from}",
                "To: {$to}",
                $leave->blocks_slots ? 'Your appointment slots are blocked for these dates.' : 'Slots were not blocked for this leave.',
            ],
            actionUrl: $url,
            actionLabel: 'View leave',
        ));
    }

    public function notifyStaffSalaryRecorded(Expense $expense): void
    {
        $expense->loadMissing(['staff.user', 'salon', 'category']);
        if (! $expense->staff_id || $expense->category?->slug !== 'salary') {
            return;
        }

        $staff = $expense->staff;
        $salon = $expense->salon;
        if (! $staff || ! $salon) {
            return;
        }

        $amount = \App\Helpers\CurrencyHelper::format((float) $expense->amount, $salon->currency ?? 'INR');
        $url = route('expenses.index', ['store' => SalonUrl::key($salon)]);

        $this->createNotification((int) $expense->salon_id, 'salary', [
            'title' => 'Salary recorded',
            'body'  => "{$expense->title} — {$amount}",
            'staff_id' => $staff->id,
            'action_url' => $url,
            'data'  => ['expense_id' => $expense->id, 'action_label' => 'salary'],
        ]);

        $this->mailStaff($staff, $salon, new StaffAlertMail(
            staffName: $staff->name,
            salonName: $salon->name,
            subjectLine: "Salary recorded — {$amount}",
            headline: '💰 Salary payment recorded',
            lines: [
                "Title: {$expense->title}",
                "Amount: {$amount}",
                'Date: '.$expense->expense_date->format('j M Y'),
            ],
            actionUrl: $url,
            actionLabel: 'Open expenses',
        ));
    }

    public function notifyStaffAttendanceMarked(Staff $staff, Salon $salon, string $dateYmd, string $status): void
    {
        $label = match ($status) {
            'absent' => 'Absent',
            'on_leave' => 'On leave',
            'half_day' => 'Half day',
            default => null,
        };
        if ($label === null) {
            return;
        }

        $staff->loadMissing('user');
        $url = route('availability.index', ['store' => SalonUrl::key($salon), 'tab' => 'attendance']);
        $this->createNotification((int) $salon->id, 'attendance', [
            'title' => "Attendance: {$label}",
            'body'  => "Marked {$label} for {$dateYmd}",
            'staff_id' => $staff->id,
            'action_url' => $url,
            'data'  => ['action_label' => 'attendance'],
        ]);

        $this->mailStaff($staff, $salon, new StaffAlertMail(
            staffName: $staff->name,
            salonName: $salon->name,
            subjectLine: "Attendance marked: {$label} ({$dateYmd})",
            headline: '📋 Attendance update',
            lines: [
                "Date: {$dateYmd}",
                "Status: {$label}",
            ],
            actionUrl: $url,
            actionLabel: 'View attendance',
        ));
    }

    private function mailStaff(Staff $staff, Salon $salon, StaffAlertMail $mail): void
    {
        $email = $this->resolveStaffEmail($staff);
        if (! $email) {
            Log::info('Staff alert email skipped — no email on staff/user', [
                'staff_id' => $staff->id,
                'salon_id' => $salon->id,
            ]);

            return;
        }

        try {
            Mail::to($email)->queue($mail);
        } catch (\Throwable $e) {
            Log::error('Staff alert email failed', [
                'staff_id' => $staff->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    private function resolveStaffEmail(Staff $staff): ?string
    {
        $staff->loadMissing('user');
        $email = trim((string) ($staff->email ?: $staff->user?->email ?: ''));

        return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
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
        $appointment->loadMissing(['client', 'staff', 'services.service', 'salon']);

        // 1. In-app notification
        $this->createNotification($appointment->salon_id, 'appointment', [
            'title' => $appointment->status === 'pending' ? 'New Booking Request' : 'New Booking',
            'body'  => "{$appointment->client->first_name} {$appointment->client->last_name} — {$appointment->starts_at->format('D j M, g:ia')}",
            'staff_id' => $appointment->staff_id,
            'data'  => ['appointment_id' => $appointment->id],
        ]);

        // 1b. Email assigned staff (if different channel / they have an email)
        $this->notifyStaffBookingAssigned($appointment);

        // 2. Email to salon inbox and tenant owner
        foreach ($this->resolveTenantEmails($appointment) as $recipient) {
            try {
                Mail::to($recipient)->queue(new TenantNewBookingMail($appointment));
            } catch (\Throwable $e) {
                Log::error('Tenant new-booking email failed', [
                    'appointment_id' => $appointment->id,
                    'to'             => $recipient,
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
            'staff_id' => $appointment->staff_id,
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
            'staff_id' => $appointment->staff_id,
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
            $serviceName = $appointment->services->first()?->service_name ?? 'appointment';
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
     * Resolve tenant inboxes: salon email and owner email (unique).
     *
     * @return list<string>
     */
    private function resolveTenantEmails(Appointment $appointment): array
    {
        $salon = $appointment->salon ?? $appointment->load('salon')->salon;
        $salon?->loadMissing('owner');

        $emails = [];
        foreach ([$salon?->email, $salon?->owner?->email] as $email) {
            $email = strtolower(trim((string) $email));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[$email] = $email;
            }
        }

        if ($emails === []) {
            Log::warning('No tenant email found for notification', [
                'salon_id'       => $appointment->salon_id,
                'appointment_id' => $appointment->id,
            ]);
        }

        return array_values($emails);
    }

    private function resolveTenantEmail(Appointment $appointment): ?string
    {
        return $this->resolveTenantEmails($appointment)[0] ?? null;
    }

    private function createNotification(int $salonId, string $type, array $payload): void
    {
        SalonNotification::create([
            'salon_id'   => $salonId,
            'staff_id'   => ! empty($payload['staff_id']) ? (int) $payload['staff_id'] : null,
            'type'       => $type,
            'title'      => $payload['title'],
            'body'       => $payload['body'] ?? null,
            'data'       => $payload['data'] ?? null,
            'action_url' => $payload['action_url'] ?? null,
        ]);
    }
}
