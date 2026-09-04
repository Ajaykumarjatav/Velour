<?php

declare(strict_types=1);

namespace App\Support;

use App\Jobs\OnboardNewTenant;
use App\Models\Salon;
use App\Models\User;

/**
 * Ensures salon owners have a default salon (registration normally does this).
 * Fallback name is a placeholder — real business name should be set in Settings.
 */
final class DefaultSalonProvisioner
{
    public static function ensure(User $user): ?Salon
    {
        if ($user->isSuperAdmin()) {
            return null;
        }

        $existing = $user->salons()->orderBy('id')->first();
        if ($existing) {
            if (! $user->hasRole('tenant_admin')) {
                $user->assignRole('tenant_admin');
            }

            return $existing;
        }

        if ($user->staffProfile()->exists()) {
            return null;
        }

        // Prefer a temporary business name from the owner's full name (not "{name}'s Salon").
        $ownerName = trim((string) $user->name);
        $defaultSalonName = $ownerName !== '' ? $ownerName : 'My Business';
        $slug = SalonSlug::uniqueFromName($defaultSalonName);

        $defaultBusinessTypeId = (int) \App\Models\BusinessType::query()->orderBy('sort_order')->value('id');
        if ($defaultBusinessTypeId < 1) {
            $defaultBusinessTypeId = (int) \App\Models\BusinessType::query()->orderBy('id')->value('id');
        }

        if ($defaultBusinessTypeId < 1) {
            return null;
        }

        $salon = Salon::withoutGlobalScopes()->create([
            'owner_id'         => $user->id,
            'business_type_id' => $defaultBusinessTypeId,
            'name'             => $defaultSalonName,
            'slug'             => $slug,
            'subdomain'        => $slug,
            'phone'            => null,
            'currency'         => \App\Helpers\CurrencyHelper::defaultCode(),
            'timezone'         => SalonTime::defaultTimezone(),
            'is_active'        => true,
        ]);

        dispatch(new OnboardNewTenant($user, $salon));

        if (app()->bound('session')) {
            session(['active_salon_id' => $salon->id]);
        }

        return $salon;
    }
}
