<?php

namespace App\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;

/**
 * Routes outbound email through purpose-specific SMTP mailers (onboarding, auth, bookings, support, billing).
 */
final class PurposeMail
{
    public const ONBOARDING = 'onboarding';

    public const AUTH = 'auth';

    public const BOOKINGS = 'bookings';

    public const SUPPORT = 'support';

    public const BILLING = 'billing';

    public static function mailerName(string $purpose): string
    {
        return (string) config("mail.purposes.{$purpose}.mailer", $purpose);
    }

    /** @return array{address: string, name?: string} */
    public static function from(string $purpose): array
    {
        $from = config("mail.purposes.{$purpose}.from");

        return is_array($from) ? $from : (array) config('mail.from');
    }

    public static function applyFrom(Mailable $mailable, string $purpose): Mailable
    {
        $from = self::from($purpose);

        return $mailable->from(
            $from['address'],
            $from['name'] ?? null,
        );
    }

    public static function send(string $purpose, mixed $to, Mailable $mailable): void
    {
        Mail::mailer(self::mailerName($purpose))
            ->to($to)
            ->send(self::applyFrom($mailable, $purpose));
    }

    public static function queue(string $purpose, mixed $to, Mailable $mailable): void
    {
        Mail::mailer(self::mailerName($purpose))
            ->to($to)
            ->queue(self::applyFrom($mailable, $purpose));
    }

    public static function configureMailMessage(string $purpose, MailMessage $message): MailMessage
    {
        $from = self::from($purpose);

        return $message
            ->mailer(self::mailerName($purpose))
            ->from($from['address'], $from['name'] ?? null);
    }
}
