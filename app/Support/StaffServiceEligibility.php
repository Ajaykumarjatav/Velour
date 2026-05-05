<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Service;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Shared rules for which services a staff role may be assigned (matches Staff & HR create/edit).
 */
final class StaffServiceEligibility
{
    /** @var list<string> */
    private const ROLES = ['stylist', 'therapist', 'manager', 'receptionist', 'junior', 'owner'];

    /**
     * @return array<string, list<array{id: int, name: string}>>
     */
    public static function servicesByRoleForSalon(int $salonId): array
    {
        $out = [];
        foreach (self::ROLES as $role) {
            $out[$role] = self::eligibleServicesForRole($salonId, $role)
                ->map(fn (Service $s) => ['id' => (int) $s->id, 'name' => (string) $s->name])
                ->values()
                ->all();
        }

        return $out;
    }

    public static function eligibleServicesForRole(int $salonId, string $role): Collection
    {
        return Service::withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->active()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'allowed_roles'])
            ->filter(fn (Service $service) => $service->allowsStaffRole($role))
            ->values();
    }

    /** @param  array<int, mixed>  $serviceIds */
    public static function assertEligibleForRole(int $salonId, string $role, array $serviceIds): void
    {
        $ids = array_values(array_unique(array_map('intval', $serviceIds)));
        if ($ids === []) {
            return;
        }

        $services = Service::withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->whereIn('id', $ids)
            ->get(['id', 'name', 'allowed_roles']);
        $blocked = $services->filter(fn (Service $service) => ! $service->allowsStaffRole($role))->pluck('name')->values();
        if ($blocked->isEmpty()) {
            return;
        }

        throw ValidationException::withMessages([
            'services' => ['Selected role cannot be assigned these services: '.$blocked->implode(', ').'.'],
        ]);
    }
}
