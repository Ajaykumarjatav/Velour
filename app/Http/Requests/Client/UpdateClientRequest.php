<?php
namespace App\Http\Requests\Client;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        $client = $this->route('client');
        return $client ? $this->user()->can('update', $client) : false;
    }
    public function rules(): array
    {
        $salonId  = $this->attributes->get('salon_id');
        $clientId = $this->route('client');
        return [
            'first_name'         => ['sometimes','string','max:100'],
            'last_name'          => ['sometimes','string','max:100'],
            'email'              => ['nullable','email','max:255', "unique:clients,email,{$clientId},id,salon_id,{$salonId}"],
            'phone'              => ['nullable','string','max:30','regex:/^[\+\d\s\(\)\-]+$/'],
            'date_of_birth'      => ['nullable','date','before:today','after:1900-01-01'],
            'preferred_staff_id' => ['nullable','integer','exists:staff,id'],
            'tags'               => ['nullable','array'],
            'tags.*'             => ['string','max:50'],
            'allergies'          => ['nullable','string','max:2000'],
            'medical_notes'      => ['nullable','string','max:2000'],
            'marketing_consent'  => ['nullable','boolean'],
            'sms_consent'        => ['nullable','boolean'],
            'is_vip'             => ['nullable','boolean'],
            'status'             => ['nullable','in:active,inactive,blocked'],
        ];
    }
}
