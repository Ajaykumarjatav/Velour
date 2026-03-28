<?php
namespace App\Policies;
use App\Models\Salon;
use App\Models\User;

class SalonPolicy
{
    public function view(User $user, Salon $salon): bool {
        return request()->attributes->get('salon_id') === $salon->id;
    }
    public function update(User $user, Salon $salon): bool {
        return request()->attributes->get('salon_id') === $salon->id
            && in_array(request()->attributes->get('access_level'), ['owner','manager']);
    }
    public function manageSettings(User $user, Salon $salon): bool {
        return request()->attributes->get('salon_id') === $salon->id
            && request()->attributes->get('access_level') === 'owner';
    }
}
