<?php

namespace App\Notifications;

use App\Support\EmailVerificationToken;
use App\Support\PurposeMail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;

/**
 * Branded email verification using cache tokens (no signed-URL host issues).
 */
class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $minutes = (int) Config::get('auth.verification.expire', 60);
        $token = EmailVerificationToken::issue($notifiable, $minutes);
        $url = EmailVerificationToken::url($notifiable, $token);

        return PurposeMail::configureMailMessage(PurposeMail::AUTH, (new MailMessage())
            ->subject('Verify your EasyGrox email address')
            ->view('emails.auth.verify-email', [
                'user'   => $notifiable,
                'url'    => $url,
                'expiry' => $minutes.' minutes',
            ]));
    }
}
