<?php

namespace App\Http\Requests\Team;

use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates team invitations: only staff profiles that already exist without a linked login.
 */
class InviteTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(['tenant_admin', 'manager']) || $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        $salonId = (int) Tenant::current()->getKey();

        return [
            'staff_id' => [
                'required',
                'integer',
                Rule::exists('staff', 'id')->where(
                    fn ($q) => $q->where('salon_id', $salonId)->whereNull('user_id')->whereNull('deleted_at')
                ),
            ],
            'role' => ['required', Rule::in(['tenant_admin', 'manager', 'stylist', 'receptionist'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $salonId = (int) Tenant::current()->getKey();
            $staff    = Staff::withoutGlobalScopes()
                ->where('salon_id', $salonId)
                ->whereKey((int) $this->input('staff_id'))
                ->first();

            if ($staff === null) {
                return;
            }

            $email = trim((string) $staff->email);
            if ($email === '') {
                $validator->errors()->add(
                    'staff_id',
                    'Add an email address on the staff profile (Staff & HR) before sending an invitation.'
                );

                return;
            }

            $normalized = mb_strtolower($email);
            $user       = User::query()->whereRaw('LOWER(email) = ?', [$normalized])->first();

            if ($user === null) {
                return;
            }

            if ($user->trashed()) {
                $validator->errors()->add(
                    'staff_id',
                    'This email is tied to a deleted account. Contact support to restore or use a different email.'
                );

                return;
            }

            if ($user->isSuperAdmin()) {
                $validator->errors()->add('staff_id', 'This email cannot be invited as team staff.');

                return;
            }

            $tenant = Tenant::current();
            if ($tenant !== null && (int) $user->id === (int) $tenant->owner_id) {
                $validator->errors()->add(
                    'staff_id',
                    'This email is the salon owner account. Use Forgot password on the login page if they need access.'
                );

                return;
            }

            $blockingSameSalon = Staff::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->where('id', '!=', $staff->id)
                ->where('salon_id', $salonId)
                ->whereNull('deleted_at')
                ->exists();

            if ($blockingSameSalon) {
                $validator->errors()->add(
                    'staff_id',
                    'This login is already linked to another active profile in your team. Remove or edit that profile first.'
                );

                return;
            }

            $blockingOtherSalon = Staff::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->where('salon_id', '!=', $salonId)
                ->whereNull('deleted_at')
                ->exists();

            if ($blockingOtherSalon) {
                $validator->errors()->add(
                    'staff_id',
                    'This account is already linked to staff at another salon. Use a different email or remove the other link first.'
                );

                return;
            }
        });
    }

    public function messages(): array
    {
        return [
            'staff_id.exists' => 'Choose a staff member who already has a profile here and is not yet linked to a login.',
        ];
    }
}
