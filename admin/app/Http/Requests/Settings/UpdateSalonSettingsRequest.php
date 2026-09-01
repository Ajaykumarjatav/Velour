<?php

namespace App\Http\Requests\Settings;

use App\Models\Salon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates updates to salon-level settings.
 * Owner-only: verified by the SettingsPolicy via authorize().
 */
class UpdateSalonSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $salon = Salon::find($this->attributes->get('salon_id'));
        return $salon ? $this->user()->can('manageSettings', $salon) : false;
    }

    public function rules(): array
    {
        return [
            // Opening hours
            'opening_hours'                     => ['nullable', 'array'],
            'opening_hours.*.open'              => ['nullable', 'date_format:H:i'],
            'opening_hours.*.close'             => ['nullable', 'date_format:H:i', 'after:opening_hours.*.open'],
            'opening_hours.*.closed'            => ['nullable', 'boolean'],

            // Booking settings
            'booking_advance_days'              => ['nullable', 'integer', 'min:1', 'max:365'],
            'booking_slot_interval'             => ['nullable', 'integer', 'in:10,15,20,30,45,60'],
            'booking_lead_time_hours'           => ['nullable', 'integer', 'min:0', 'max:72'],
            'booking_cancellation_hours'        => ['nullable', 'integer', 'min:0', 'max:168'],
            'online_booking_enabled'            => ['nullable', 'boolean'],
            'new_client_booking_enabled'        => ['nullable', 'boolean'],
            'require_deposit'                   => ['nullable', 'boolean'],
            'deposit_percentage'                => ['nullable', 'integer', 'min:10', 'max:100'],
            'booking_confirmation_message'      => ['nullable', 'string', 'max:500'],
            'booking_cancellation_message'      => ['nullable', 'string', 'max:500'],

            // Notifications
            'notify_new_booking'                => ['nullable', 'boolean'],
            'notify_cancellation'               => ['nullable', 'boolean'],
            'notify_review'                     => ['nullable', 'boolean'],
            'reminder_hours_before'             => ['nullable', 'integer', 'min:1', 'max:72'],
            'send_sms_reminders'                => ['nullable', 'boolean'],
            'send_email_reminders'              => ['nullable', 'boolean'],

            // POS / payments
            'currency'                          => ['nullable', 'string', 'size:3'],
            'tax_rate'                          => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_name'                          => ['nullable', 'string', 'max:20'],
            'receipt_footer'                    => ['nullable', 'string', 'max:300'],

            // Branding
            'primary_color'                     => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color'                   => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'opening_hours.*.close.after' => 'Closing time must be after opening time.',
            'currency.size'               => 'Currency must be a 3-letter ISO code (e.g. GBP).',
        ];
    }
}
