<?php

namespace App\Policies;

use App\Models\PosTransaction;
use App\Models\Staff;
use App\Models\User;

class PosTransactionPolicy
{
    /**
     * User may act on this transaction if they own the salon or work at that salon.
     * (Avoid salons()->value('id') — wrong for multi-location; avoid loose === without int cast.)
     */
    private function userMayAccessTransactionSalon(User $user, PosTransaction $transaction): bool
    {
        $txSalonId = (int) $transaction->salon_id;

        if ($user->salons()->whereKey($txSalonId)->exists()) {
            return true;
        }

        $staffSalonId = Staff::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->value('salon_id');

        return $staffSalonId !== null && (int) $staffSalonId === $txSalonId;
    }

    public function view(User $user, PosTransaction $transaction): bool
    {
        return $this->userMayAccessTransactionSalon($user, $transaction);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function refund(User $user, PosTransaction $transaction): bool
    {
        return $this->userMayAccessTransactionSalon($user, $transaction)
            && in_array($user->staffProfile?->access_level ?? 'owner', ['owner', 'manager', 'senior'], true);
    }

    public function void(User $user, PosTransaction $transaction): bool
    {
        return $this->userMayAccessTransactionSalon($user, $transaction)
            && in_array($user->staffProfile?->access_level ?? 'owner', ['owner', 'manager'], true);
    }
}
