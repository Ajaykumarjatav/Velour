<?php
namespace App\Policies;
use App\Models\PosTransaction;
use App\Models\User;

class PosTransactionPolicy
{
    private function salonId($user): ?int
    {
        return $user->salons()->value('id') ?? $user->staffProfile?->salon_id;
    }

    public function view(User $user, PosTransaction $transaction): bool
    {
        return $transaction->salon_id === $this->salonId($user);
    }

    public function create(User $user): bool { return true; }

    public function refund(User $user, PosTransaction $transaction): bool
    {
        return $transaction->salon_id === $this->salonId($user)
            && in_array($user->staffProfile?->access_level ?? 'owner', ['owner','manager','senior']);
    }

    public function void(User $user, PosTransaction $transaction): bool
    {
        return $transaction->salon_id === $this->salonId($user)
            && in_array($user->staffProfile?->access_level ?? 'owner', ['owner','manager']);
    }
}
