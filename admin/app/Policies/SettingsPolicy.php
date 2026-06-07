<?php

namespace App\Policies;

use App\Models\Salon;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * SettingsPolicy
 *
 * Controls access to salon configuration.
 * Most settings are owner-only; some operational settings allow managers.
 */
class SettingsPolicy
{
    use HandlesAuthorization;

    private function currentSalonId(User $user): ?int
    {
        return $user->salons()->value('id') ?? $user->staffProfile?->salon_id;
    }

    private function isOwner(User $user, Salon $salon): bool
    {
        return $user->isSuperAdmin() || $salon->owner_id === $user->id;
    }

    private function isOwnerOrManager(User $user, Salon $salon): bool
    {
        return $this->isOwner($user, $salon)
            || ($salon->id === $this->currentSalonId($user) && $user->hasRole('manager'));
    }

    /** View settings page. */
    public function view(User $user, Salon $salon): bool
    {
        return $user->isSuperAdmin() || $salon->id === $this->currentSalonId($user);
    }

    /** Manage any settings (owner + manager). */
    public function update(User $user, Salon $salon): bool
    {
        return $this->isOwnerOrManager($user, $salon);
    }

    /** Manage all settings — owner only (billing, integrations, danger zone). */
    public function manageSettings(User $user, Salon $salon): bool
    {
        return $this->isOwner($user, $salon);
    }

    /** Manage payment / billing integration settings. */
    public function manageBilling(User $user, Salon $salon): bool
    {
        return $this->isOwner($user, $salon);
    }

    /** Manage custom domain. */
    public function manageDomain(User $user, Salon $salon): bool
    {
        return $this->isOwner($user, $salon) && $user->planAllows('custom_domain');
    }

    /** Delete the salon entirely. */
    public function delete(User $user, Salon $salon): bool
    {
        return $user->isSuperAdmin() || $salon->owner_id === $user->id;
    }
}
