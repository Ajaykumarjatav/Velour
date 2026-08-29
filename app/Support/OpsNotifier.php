<?php

namespace App\Support;

use App\Mail\OpsSignupAlert;
use App\Models\Salon;
use App\Models\User;
use App\Support\PurposeMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends internal ops alerts (new user / new store) to MAIL_OPS_NOTIFY
 * (optional CC via MAIL_OPS_NOTIFY_CC).
 */
final class OpsNotifier
{
    public static function recipient(): ?string
    {
        $email = trim((string) config('mail.ops_notify', ''));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * @return list<string>
     */
    public static function ccRecipients(?string $to = null): array
    {
        $raw = (string) config('mail.ops_notify_cc', '');
        $emails = preg_split('/[,;]+/', $raw) ?: [];
        $cc = [];

        foreach ($emails as $email) {
            $email = trim($email);
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            if ($to && strcasecmp($email, $to) === 0) {
                continue;
            }
            $cc[strtolower($email)] = $email;
        }

        return array_values($cc);
    }

    public static function newUser(User $user, Salon $salon): void
    {
        self::send('new_user', $user, $salon);
    }

    public static function newStore(User $owner, Salon $salon): void
    {
        self::send('new_store', $owner, $salon);
    }

    private static function send(string $event, User $user, Salon $salon): void
    {
        $to = self::recipient();
        if (! $to) {
            return;
        }

        // Avoid duplicate when the ops inbox is the same as the signing-up user.
        if (strcasecmp($to, (string) $user->email) === 0 && $event === 'new_user') {
            return;
        }

        try {
            $cc = self::ccRecipients($to);
            if ($cc !== []) {
                Mail::mailer(PurposeMail::mailerName(PurposeMail::ONBOARDING))
                    ->to($to)
                    ->cc($cc)
                    ->send(PurposeMail::applyFrom(new OpsSignupAlert($event, $user, $salon), PurposeMail::ONBOARDING));
            } else {
                PurposeMail::send(PurposeMail::ONBOARDING, $to, new OpsSignupAlert($event, $user, $salon));
            }
        } catch (\Throwable $e) {
            Log::warning('Ops signup alert failed', [
                'event' => $event,
                'user_id' => $user->id,
                'salon_id' => $salon->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
