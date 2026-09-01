<?php
namespace App\Http\Requests\Appointment;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Appointment::class);
    }
    public function rules(): array
    {
        return [
            'client_id'      => ['required','integer','exists:clients,id'],
            'staff_id'       => ['required','integer','exists:staff,id'],
            'service_ids'    => ['required','array','min:1'],
            'service_ids.*'  => ['integer','exists:services,id'],
            'starts_at'      => ['required','date','after:now'],
            'source'         => ['nullable','in:online,phone,walk_in,google,instagram,facebook,whatsapp,manual'],
            'client_notes'   => ['nullable','string','max:1000'],
            'internal_notes' => ['nullable','string','max:1000'],
        ];
    }
}
