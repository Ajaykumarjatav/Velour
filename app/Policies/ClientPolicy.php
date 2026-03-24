<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    private function salonId(User $user): ?int
    {
        return $user->salons()->value('id') ?? $user->staffProfile?->salon_id;
    }

    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Client $client): bool
    {
        return $client->salon_id === $this->salonId($user);
    }

    public function create(User $user): bool { return true; }

    public function update(User $user, Client $client): bool
    {
        return $client->salon_id === $this->salonId($user);
    }

    public function delete(User $user, Client $client): bool
    {
        return $client->salon_id === $this->salonId($user)
            && $user->hasAnyRole(['tenant_admin', 'manager'])
                || $user->salons()->exists();
    }

    /** Sensitive: medical notes, formulas, allergies. */
    public function viewMedical(User $user, Client $client): bool
    {
        return $client->salon_id === $this->salonId($user)
            && $user->hasAnyPermissionTo(['view clients', 'manage clients']);
    }

    public function export(User $user): bool
    {
        return $user->hasAnyRole(['tenant_admin', 'manager'])
            || $user->salons()->exists();
    }

    public function gdpr(User $user, Client $client): bool
    {
        return $client->salon_id === $this->salonId($user)
            && ($user->hasRole('tenant_admin') || $user->salons()->exists());
    }
}
