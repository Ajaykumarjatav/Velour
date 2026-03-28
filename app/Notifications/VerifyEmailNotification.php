<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Spatie\Multitenancy\Jobs\NotTenantAware;

/**
 * VerifyEmailNotification
 *
 * Replaces Laravel's default email verification notification with a branded
 * Velour version.  Generates a signed URL valid for 60 minutes.
 *
 * Implements NotTenantAware so Spatie Multitenancy does not attempt to
 * resolve a tenant for this job (email verification runs in guest context).
 */
class VerifyEmailNotification extends VerifyEmail implements ShouldQueue, NotTenantAware
{
    use Queueable;

    /**
     * Build the mail message.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage())
            ->subject('Verify your Velour email address')
            ->view('emails.auth.verify-email', [
                'user'    => $notifiable,
                'url'     => $url,
                'expiry'  => '60 minutes',
            ]);
    }

    /**
     * Generate a signed verification URL.
     */
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
