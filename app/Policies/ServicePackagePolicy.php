<?php

namespace App\Policies;

use App\Models\Salon;
use App\Models\ServicePackage;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;

class ServicePackagePolicy
{
    /**
     * Web routes do not run {@see \App\Http\Middleware\EnsureSalonAccess}, so
     * `salon_id` / `access_level` may be missing. Fall back to current tenant + DB.
     */
    private function salonId(): int
    {
        $fromRequest = (int) request()->attributes->get('salon_id');
        if ($fromRequest > 0) {
            return $fromRequest;
        }
        if (Tenant::checkCurrent()) {
            return (int) Tenant::current()->getKey();
        }

        return 0;
    }

    private function canManagePackages(User $user): bool
    {
        $level = request()->attributes->get('access_level');
        if (is_string($level) && $level !== '') {
            return in_array($level, ['owner', 'manager'], true);
        }

        $salonId = $this->salonId();
        if ($salonId <= 0) {
            return false;
        }

        if (Salon::query()->where('id', $salonId)->where('owner_id', $user->id)->exists()) {
            return true;
        }

        $staff = Staff::query()
            ->where('user_id', $user->id)
            ->where('salon_id', $salonId)
            ->where('is_active', true)
            ->first();

        return $staff !== null
            && in_array((string) $staff->access_level, ['owner', 'manager'], true);
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServicePackage $package): bool
    {
        return $package->salon_id === $this->salonId();
    }

    public function create(User $user): bool
    {
        return $this->canManagePackages($user);
    }

    public function update(User $user, ServicePackage $package): bool
    {
        return $package->salon_id === $this->salonId() && $this->canManagePackages($user);
    }

    public function delete(User $user, ServicePackage $package): bool
    {
        return $package->salon_id === $this->salonId() && $this->canManagePackages($user);
    }
}
