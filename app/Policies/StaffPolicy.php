<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StaffPolicy
{
    use HandlesAuthorization;

    private function salonId(User $user): ?int
    {
        return $user->salons()->value('id') ?? $user->staffProfile?->salon_id;
    }

    private function isManagerOrAbove(User $user): bool
    {
        return $user->hasAnyRole(['tenant_admin', 'manager']) || $user->salons()->exists();
    }

    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Staff $staff): bool
    {
        return $staff->salon_id === $this->salonId($user);
    }

    public function create(User $user): bool
    {
        return $this->isManagerOrAbove($user);
    }

    public function update(User $user, Staff $staff): bool
    {
        return $staff->salon_id === $this->salonId($user) && $this->isManagerOrAbove($user);
    }

    public function delete(User $user, Staff $staff): bool
    {
        return $staff->salon_id === $this->salonId($user)
            && ($user->hasRole('tenant_admin') || $user->salons()->exists());
    }

    /** Commission data: manager-level and above only. */
    public function viewCommission(User $user, Staff $staff): bool
    {
        return $staff->salon_id === $this->salonId($user) && $this->isManagerOrAbove($user);
    }

    public function manageRoles(User $user, Staff $staff): bool
    {
        return $staff->salon_id === $this->salonId($user)
            && ($user->hasRole('tenant_admin') || $user->salons()->exists());
    }
}
