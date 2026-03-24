<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Spatie\Multitenancy\Jobs\NotTenantAware;

/**
 * ResetPasswordNotification
 *
 * Replaces Laravel's default password reset notification with a branded
 * Velour email.  The reset link expires in 60 minutes (configurable via
 * config/auth.php → passwords.users.expire).
 *
 * Implements NotTenantAware so Spatie Multitenancy does not attempt to
 * resolve a tenant for this job (password resets run in guest context).
 */
class ResetPasswordNotification extends ResetPassword implements ShouldQueue, NotTenantAware
{
    use Queueable;

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage())
            ->subject('Reset your Velour password')
            ->view('emails.auth.reset-password', [
                'user'    => $notifiable,
                'url'     => $url,
                'expiry'  => config('auth.passwords.users.expire', 60) . ' minutes',
            ]);
    }
}
