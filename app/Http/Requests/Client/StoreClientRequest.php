<?php
namespace App\Http\Requests\Client;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Client::class);
    }
    public function rules(): array
    {
        $salonId = $this->attributes->get('salon_id');
        return [
            'first_name'         => ['required','string','max:100'],
            'last_name'          => ['required','string','max:100'],
            'email'              => ['nullable','email','max:255', "unique:clients,email,NULL,id,salon_id,{$salonId}"],
            'phone'              => ['nullable','string','max:30','regex:/^[\+\d\s\(\)\-]+$/'],
            'date_of_birth'      => ['nullable','date','before:today','after:1900-01-01'],
            'preferred_staff_id' => ['nullable','integer','exists:staff,id'],
            'tags'               => ['nullable','array'],
            'tags.*'             => ['string','max:50'],
            'allergies'          => ['nullable','string','max:2000'],
            'medical_notes'      => ['nullable','string','max:2000'],
            'marketing_consent'  => ['nullable','boolean'],
            'sms_consent'        => ['nullable','boolean'],
            'note'               => ['nullable','string','max:2000'],
            'source'             => ['nullable','in:online_booking,walk_in,referral,instagram,google,facebook,phone,website'],
        ];
    }
}
