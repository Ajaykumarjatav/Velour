<?php

namespace App\Http\Requests\Staff;

use App\Models\Staff;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        $staff = $this->route('staff');
        return $staff instanceof Staff
            ? $this->user()->can('update', $staff)
            : $this->user()->can('create', Staff::class);
    }

    public function rules(): array
    {
        $staffId = $this->route('staff')?->id;

        return [
            'first_name'      => ['sometimes', 'string', 'max:100'],
            'last_name'       => ['sometimes', 'string', 'max:100'],
            'email'           => ['nullable', 'email', 'max:255', "unique:staff,email,{$staffId}"],
            'phone'           => ['nullable', 'string', 'max:30', 'regex:/^[\+\d\s\(\)\-]+$/'],
            'role'            => ['sometimes', 'string', 'max:100'],
            'bio'             => ['nullable', 'string', 'max:1000'],
            'specialisms'     => ['nullable', 'array'],
            'specialisms.*'   => ['string', 'max:100'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'access_level'    => ['nullable', 'in:staff,senior,manager,owner'],
            'color'           => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'working_days'    => ['nullable', 'array'],
            'working_days.*'  => ['in:Mon,Tue,Wed,Thu,Fri,Sat,Sun'],
            'start_time'      => ['nullable', 'date_format:H:i'],
            'end_time'        => ['nullable', 'date_format:H:i', 'after:start_time'],
            'bookable_online' => ['nullable', 'boolean'],
            'is_active'       => ['nullable', 'boolean'],
        ];
    }
}
