<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StaffPolicy
{
    use HandlesAuthorization;

    /** Owner may access any owned location; staff users may access their work salon. */
    private function userMayAccessStaffSalon(User $user, int $staffSalonId): bool
    {
        if ($user->salons()->whereKey($staffSalonId)->exists()) {
            return true;
        }

        $sid = Staff::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->value('salon_id');

        return $sid !== null && (int) $sid === $staffSalonId;
    }

    private function isManagerOrAbove(User $user): bool
    {
        return $user->hasAnyRole(['tenant_admin', 'manager']) || $user->salons()->exists();
    }

    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Staff $staff): bool
    {
        return $this->userMayAccessStaffSalon($user, (int) $staff->salon_id);
    }

    public function create(User $user): bool
    {
        return $this->isManagerOrAbove($user);
    }

    public function update(User $user, Staff $staff): bool
    {
        return $this->userMayAccessStaffSalon($user, (int) $staff->salon_id) && $this->isManagerOrAbove($user);
    }

    public function delete(User $user, Staff $staff): bool
    {
        return $this->userMayAccessStaffSalon($user, (int) $staff->salon_id)
            && ($user->hasRole('tenant_admin') || $user->salons()->exists());
    }

    /** Commission data: manager-level and above only. */
    public function viewCommission(User $user, Staff $staff): bool
    {
        return $this->userMayAccessStaffSalon($user, (int) $staff->salon_id) && $this->isManagerOrAbove($user);
    }

    public function manageRoles(User $user, Staff $staff): bool
    {
        return $this->userMayAccessStaffSalon($user, (int) $staff->salon_id)
            && ($user->hasRole('tenant_admin') || $user->salons()->exists());
    }
}
