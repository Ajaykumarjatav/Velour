<?php
namespace App\Http\Requests\Booking;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return \App\Support\PhoneCountry::merge([
            'hold_token'               => ['required','string','uuid'],
            'first_name'               => ['required','string','max:100'],
            'last_name'                => ['required','string','max:100'],
            'email'                    => ['nullable','email','max:255'],
            'notes'                    => ['nullable','string','max:500'],
            'marketing_consent'        => ['nullable','boolean'],
            'stripe_payment_intent_id' => ['nullable','string','max:255'],
        ], 'phone', true);
    }
}
