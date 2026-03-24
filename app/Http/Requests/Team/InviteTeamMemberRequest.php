<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates team member invitations (from salon-admin team panel).
 * Tenant admin / owner only: verified by route middleware (tenant_admin).
 */
class InviteTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(['tenant_admin', 'manager']) || $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:150', 'regex:/^[\pL\s\-]+$/u'],
            'email' => [
                'required', 'email:rfc', 'max:255',
                // Can't already be a user on this salon's staff list
                Rule::unique('staff', 'email'),
            ],
            'role'  => ['required', Rule::in(['tenant_admin', 'manager', 'stylist', 'receptionist'])],
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email address is already associated with a team member.',
            'name.regex'   => 'Name may only contain letters, spaces, and hyphens.',
        ];
    }
}
