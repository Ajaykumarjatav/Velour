<?php

namespace App\Jobs;

use App\Models\Salon;
use App\Models\SalonNotification;
use App\Models\SalonSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Multitenancy\Jobs\NotTenantAware;

/**
 * Fired after a new salon owner registers.
 * Provisions default settings, sends welcome email, and seeds starter notifications.
 *
 * NotTenantAware — registration runs before any tenant is resolved for the request,
 * so this job must not require tenant context in the queue payload (Spatie default).
 */
class OnboardNewTenant implements ShouldQueue, NotTenantAware
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly User  $user,
        public readonly Salon $salon,
    ) {}

    public function handle(): void
    {
        // 1. Provision default SalonSettings
        $defaults = [
            'booking_confirmation_email' => true,
            'booking_reminder_email'     => true,
            'booking_reminder_sms'       => false,
            'reminder_hours_before'      => 24,
            'cancellation_policy_hours'  => 24,
            'deposit_required'           => false,
            'deposit_percentage'         => 25,
            'cancellation_fee_enabled'   => false,
            'tax_rate'                   => 0,
            'show_prices_in_widget'      => true,
            'require_phone_for_booking'  => true,
            'new_booking_email_owner'    => true,
            'theme_color'                => '#B8943A',
            'booking_advance_days'       => 60,
            'booking_min_notice_hours'   => 2,
        ];

        foreach ($defaults as $key => $value) {
            SalonSetting::firstOrCreate(
                ['salon_id' => $this->salon->id, 'key' => $key],
                ['value' => (string) ($value === true ? '1' : ($value === false ? '0' : $value))]
            );
        }

        // 2. Create welcome notifications
        $notifications = [
            [
                'type'       => 'onboarding',
                'title'      => '👋 Welcome to Velour!',
                'body'       => 'Your salon is set up and ready. Start by adding your services and staff.',
                'action_url' => '/settings?tab=services',
                'data'       => ['action_label' => 'Add Services'],
            ],
            [
                'type'       => 'onboarding',
                'title'      => '📅 Set your opening hours',
                'body'       => 'Update your salon hours so clients know when to book.',
                'action_url' => '/settings?tab=hours',
                'data'       => ['action_label' => 'Set Hours'],
            ],
            [
                'type'       => 'onboarding',
                'title'      => '🔗 Share your booking link',
                'body'       => 'Your online booking widget is live. Share it with your clients.',
                'action_url' => '/go-live',
                'data'       => ['action_label' => 'Go Live'],
            ],
        ];

        foreach ($notifications as $n) {
            SalonNotification::create(array_merge(['salon_id' => $this->salon->id], $n));
        }

        // 3. Send welcome email
        try {
            Mail::to($this->user->email)->send(new \App\Mail\WelcomeEmail($this->user, $this->salon));
        } catch (\Throwable $e) {
            Log::warning('Welcome email failed to send', ['user_id' => $this->user->id, 'error' => $e->getMessage()]);
        }

        Log::info('Tenant onboarding complete', ['user_id' => $this->user->id, 'salon_id' => $this->salon->id]);
    }
}
