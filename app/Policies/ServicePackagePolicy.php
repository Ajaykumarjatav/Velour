<?php

namespace App\Policies;

use App\Models\Salon;
use App\Models\ServicePackage;
use App\Models\Staff;
use App\Models\User;
use App\Policies\Concerns\ResolvesActiveSalonForPolicy;

class ServicePackagePolicy
{
    use ResolvesActiveSalonForPolicy;

    /**
     * Web routes do not run {@see \App\Http\Middleware\EnsureSalonAccess}, so
     * `salon_id` / `access_level` may be missing. Use the same active location as
     * {@see \App\Http\Controllers\Web\Concerns\ResolvesActiveSalon} (session + tenant + staff).
     */
    private function salonId(): int
    {
        return $this->resolveActiveSalonIdForPolicy();
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

        $staff = Staff::withoutGlobalScopes()
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
