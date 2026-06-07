<?php

namespace App\Policies\Concerns;

use App\Models\Staff;
use App\Models\Tenant;
use App\Models\User;

/**
 * Matches web multi-location: session active_salon_id when the user owns multiple salons,
 * then domain tenant, then staff-linked salon. API may set request attributes first.
 */
trait ResolvesActiveSalonForPolicy
{
    protected function resolveActiveSalonIdForPolicy(): int
    {
        $fromRequest = (int) request()->attributes->get('salon_id');
        if ($fromRequest > 0) {
            return $fromRequest;
        }

        $user = request()->user();
        if ($user instanceof User && $user->salons()->exists()) {
            $activeSalonId = (int) session('active_salon_id', 0);
            $salon = $activeSalonId > 0
                ? $user->salons()->where('id', $activeSalonId)->first()
                : null;
            $salon = $salon ?: $user->salons()->first();
            if ($salon) {
                return (int) $salon->id;
            }
        }

        if (Tenant::checkCurrent()) {
            return (int) Tenant::current()->getKey();
        }

        if (! $user instanceof User) {
            return 0;
        }

        $staffSalonId = Staff::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->value('salon_id');

        return $staffSalonId ? (int) $staffSalonId : 0;
    }
}
