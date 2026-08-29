<?php

namespace App\Notifications;

use App\Support\AppUrl;
use App\Support\PurposeMail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Spatie\Multitenancy\Jobs\NotTenantAware;

/**
 * ResetPasswordNotification
 *
 * Replaces Laravel's default password reset notification with a branded
 * EasyGrox email.  The reset link expires in 60 minutes (configurable via
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
        $url = AppUrl::absolute(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return PurposeMail::configureMailMessage(PurposeMail::AUTH, (new MailMessage())
            ->subject('Reset your EasyGrox password')
            ->view('emails.auth.reset-password', [
                'user'    => $notifiable,
                'url'     => $url,
                'expiry'  => config('auth.passwords.users.expire', 60) . ' minutes',
            ]));
    }
}
