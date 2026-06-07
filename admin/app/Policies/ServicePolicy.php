<?php

namespace App\Policies;

use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use App\Policies\Concerns\ResolvesActiveSalonForPolicy;

class ServicePolicy
{
    use ResolvesActiveSalonForPolicy;

    private function salonId(): int
    {
        return $this->resolveActiveSalonIdForPolicy();
    }

    private function canManageServices(User $user): bool
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

    public function view(User $user, Service $service): bool
    {
        return $service->salon_id === $this->salonId();
    }

    public function create(User $user): bool
    {
        return $this->canManageServices($user);
    }

    public function update(User $user, Service $service): bool
    {
        return $service->salon_id === $this->salonId() && $this->canManageServices($user);
    }

    public function delete(User $user, Service $service): bool
    {
        return $service->salon_id === $this->salonId() && $this->canManageServices($user);
    }
}
