<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy
{
    use HandlesAuthorization;

    private function salonId(User $user): ?int
    {
        return $user->salons()->value('id') ?? $user->staffProfile?->salon_id;
    }

    private function isOwnerOrManager(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->hasRole(['tenant_admin', 'manager'])
            || $user->salons()->exists();
    }

    public function viewAny(User $user): bool
    {
        return true; // All authenticated salon users can see reviews
    }

    public function view(User $user, Review $review): bool
    {
        return $user->isSuperAdmin() || $review->salon_id === $this->salonId($user);
    }

    public function reply(User $user, Review $review): bool
    {
        return $review->salon_id === $this->salonId($user)
            && $this->isOwnerOrManager($user);
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->isSuperAdmin()
            || ($review->salon_id === $this->salonId($user) && $user->hasRole('tenant_admin'));
    }

    public function requestReview(User $user): bool
    {
        return $this->isOwnerOrManager($user);
    }
}
