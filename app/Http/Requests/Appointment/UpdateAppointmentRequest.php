<?php
namespace App\Http\Requests\Appointment;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'client_id'      => ['sometimes','integer','exists:clients,id'],
            'staff_id'       => ['sometimes','integer','exists:staff,id'],
            'service_ids'    => ['sometimes','array','min:1'],
            'service_ids.*'  => ['integer','exists:services,id'],
            'starts_at'      => ['sometimes','date'],
            'client_notes'   => ['nullable','string','max:1000'],
            'internal_notes' => ['nullable','string','max:1000'],
            'status'         => ['sometimes','in:pending,confirmed,checked_in,in_progress,completed,cancelled,no_show'],
        ];
    }
}
