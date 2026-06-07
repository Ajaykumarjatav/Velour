<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\Multitenancy\Jobs\NotTenantAware;

/**
 * TwoFactorCodeNotification
 *
 * Sends a 6-digit OTP to the user's email address as a 2FA challenge.
 *
 * Implements NotTenantAware — 2FA challenges happen at login time before
 * a tenant context is established, so Spatie must not try to resolve one.
 */
class TwoFactorCodeNotification extends Notification implements ShouldQueue, NotTenantAware
{
    use Queueable;

    public function __construct(public readonly string $code) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Your Velour login code: ' . $this->code)
            ->view('emails.auth.two-factor-code', [
                'user' => $notifiable,
                'code' => $this->code,
            ]);
    }
}
