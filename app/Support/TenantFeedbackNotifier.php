<?php

declare(strict_types=1);

namespace App\Support;

use App\Mail\TenantProjectFeedbackMail;
use App\Models\TenantProjectFeedback;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Emails tenant project feedback to Ajay (MAIL_OPS_NOTIFY) and Support (MAIL_SUPPORT_NOTIFY),
 * plus optional MAIL_OPS_NOTIFY_CC.
 */
final class TenantFeedbackNotifier
{
    /**
     * @return list<string>
     */
    public static function recipients(): array
    {
        $emails = [];

        foreach ([
            config('mail.ops_notify'),
            config('mail.support_notify'),
            config('mail.ops_notify_cc'),
        ] as $raw) {
            foreach (preg_split('/[,;]+/', (string) $raw) ?: [] as $email) {
                $email = trim($email);
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }
                $emails[strtolower($email)] = $email;
            }
        }

        return array_values($emails);
    }

    public static function notify(TenantProjectFeedback $feedback): void
    {
        $to = self::recipients();
        if ($to === []) {
            Log::warning('Tenant project feedback email skipped: no recipients configured');

            return;
        }

        $feedback->loadMissing(['user', 'salon']);

        try {
            $primary = array_shift($to);
            $mailable = PurposeMail::applyFrom(
                new TenantProjectFeedbackMail($feedback),
                PurposeMail::SUPPORT
            );

            $pending = Mail::mailer(PurposeMail::mailerName(PurposeMail::SUPPORT))->to($primary);
            if ($to !== []) {
                $pending->cc($to);
            }
            $pending->send($mailable);
        } catch (\Throwable $e) {
            Log::warning('Tenant project feedback email failed', [
                'feedback_id' => $feedback->id,
                'salon_id' => $feedback->salon_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
