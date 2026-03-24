<?php
namespace App\Http\Requests\Staff;
use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Staff::class);
    }
    public function rules(): array
    {
        return [
            'first_name'      => ['required','string','max:100'],
            'last_name'       => ['required','string','max:100'],
            'email'           => ['nullable','email','max:255','unique:staff,email'],
            'phone'           => ['nullable','string','max:30'],
            'role'            => ['required','string','max:100'],
            'bio'             => ['nullable','string','max:1000'],
            'specialisms'     => ['nullable','array'],
            'specialisms.*'   => ['string','max:100'],
            'commission_rate' => ['nullable','numeric','min:0','max:100'],
            'access_level'    => ['nullable','in:staff,senior,manager,owner'],
            'color'           => ['nullable','string','regex:/^#[0-9A-Fa-f]{6}$/'],
            'working_days'    => ['nullable','array'],
            'working_days.*'  => ['in:Mon,Tue,Wed,Thu,Fri,Sat,Sun'],
            'start_time'      => ['nullable','date_format:H:i'],
            'end_time'        => ['nullable','date_format:H:i','after:start_time'],
            'hired_at'        => ['nullable','date'],
            'bookable_online' => ['nullable','boolean'],
        ];
    }
}
