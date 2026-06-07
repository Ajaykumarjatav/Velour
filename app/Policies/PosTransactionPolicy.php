<?php

namespace App\Policies;

use App\Models\PosTransaction;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;
use App\Scopes\TenantScope;

class PosTransactionPolicy
{
    /**
     * User may act on this transaction if they own the salon or have a staff row for that salon.
     * Uses where(salon_id) exists — never value('salon_id') (wrong for multi-location).
     */
    private function userMayAccessTransactionSalon(User $user, PosTransaction $transaction): bool
    {
        $txSalonId = (int) $transaction->salon_id;

        if ($user->salons()->whereKey($txSalonId)->exists()) {
            return true;
        }

        return Staff::withoutGlobalScope(TenantScope::class)
            ->where('user_id', $user->id)
            ->where('salon_id', $txSalonId)
            ->exists();
    }

    public function view(User $user, PosTransaction $transaction): bool
    {
        if ($user->isSupport()) {
            return true;
        }

        // Route model binding + TenantScope already restrict to the current tenant; if those agree
        // with the resolved transaction, the user was admitted to this tenant by TenantFinder (owner
        // or staff) and may view receipts. This avoids 403 when pivot/owner_id data is out of sync.
        if (Tenant::checkCurrent()) {
            return (int) $transaction->salon_id === (int) Tenant::current()->getKey();
        }

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
