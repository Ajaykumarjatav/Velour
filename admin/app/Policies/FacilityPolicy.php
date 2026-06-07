<?php

namespace App\Policies;

use App\Models\Facility;
use App\Models\Tenant;
use App\Models\User;

class FacilityPolicy
{
    private function sameTenantSalon(Facility $facility): bool
    {
        if (! Tenant::checkCurrent()) {
            return false;
        }

        return (int) $facility->salon_id === (int) Tenant::current()->getKey();
    }

    private function mayViewFacilities(User $user): bool
    {
        return $user->can('facilities.view')
            || $user->can('facilities.manage')
            || $user->salons()->exists();
    }

    private function mayManageFacilities(User $user): bool
    {
        return $user->can('facilities.manage')
            || $user->salons()->exists()
            || $user->hasAnyRole(['tenant_admin', 'manager']);
    }

    public function viewAny(User $user): bool
    {
        return $this->mayViewFacilities($user);
    }

    public function view(User $user, Facility $facility): bool
    {
        return $this->mayViewFacilities($user) && $this->sameTenantSalon($facility);
    }

    public function create(User $user): bool
    {
        return $this->mayManageFacilities($user);
    }

    public function update(User $user, Facility $facility): bool
    {
        return $this->mayManageFacilities($user) && $this->sameTenantSalon($facility);
    }

    public function delete(User $user, Facility $facility): bool
    {
        return $this->mayManageFacilities($user) && $this->sameTenantSalon($facility);
    }
}
