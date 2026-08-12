<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use App\Support\SignedUrl;

/**
 * Branded email verification. Sent synchronously so registration can catch
 * mail/config failures without a queued job masking MissingAppKey / SMTP errors.
 */
class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage())
            ->subject('Verify your EasyGrox email address')
            ->view('emails.auth.verify-email', [
                'user'   => $notifiable,
                'url'    => $url,
                'expiry' => '60 minutes',
            ]);
    }

    protected function verificationUrl($notifiable): string
    {
        return SignedUrl::temporaryRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
