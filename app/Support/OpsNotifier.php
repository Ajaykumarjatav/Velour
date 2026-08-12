<?php

namespace App\Support;

use App\Mail\OpsSignupAlert;
use App\Models\Salon;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends internal ops alerts (new user / new store) to MAIL_OPS_NOTIFY.
 */
final class OpsNotifier
{
    public static function recipient(): ?string
    {
        $email = trim((string) config('mail.ops_notify', ''));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
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
            Mail::to($to)->send(new OpsSignupAlert($event, $user, $salon));
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
