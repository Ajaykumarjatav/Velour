<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Staff;
use Illuminate\Validation\Rule;

/**
 * Salon job titles stored on staff.role (slug) with display labels for UI.
 */
final class StaffJobRoles
{
    /** @var array<string, string> slug => label */
    public const LABELS = [
        'receptionist' => 'Receptionist',
        'hair_stylist' => 'Hair Stylist',
        'hair_colorist' => 'Hair Colorist',
        'barber' => 'Barber',
        'beautician' => 'Beautician',
        'esthetician' => 'Esthetician',
        'nail_technician' => 'Nail Technician',
        'makeup_artist' => 'Makeup Artist',
        'spa_therapist' => 'Spa Therapist',
        'massage_therapist' => 'Massage Therapist',
        'grooming_specialist' => 'Grooming Specialist',
        'tattoo_artist' => 'Tattoo Artist',
        'salon_manager' => 'Salon Manager',
    ];

    /** @var array<string, string> legacy slug => new slug */
    public const LEGACY_MAP = [
        'stylist' => 'hair_stylist',
        'therapist' => 'spa_therapist',
        'manager' => 'salon_manager',
        'receptionist' => 'receptionist',
        'junior' => 'receptionist',
        'owner' => 'salon_manager',
    ];

    /** @return list<string> */
    public static function slugs(): array
    {
        return array_keys(self::LABELS);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return self::LABELS;
    }

    public static function label(?string $slug): string
    {
        $slug = strtolower(trim((string) $slug));
        if ($slug === '') {
            return '';
        }

        if (isset(self::LABELS[$slug])) {
            return self::LABELS[$slug];
        }

        $migrated = self::LEGACY_MAP[$slug] ?? null;
        if ($migrated !== null && isset(self::LABELS[$migrated])) {
            return self::LABELS[$migrated];
        }

        return ucwords(str_replace('_', ' ', $slug));
    }

    public static function normalize(?string $role): ?string
    {
        $slug = strtolower(trim((string) $role));
        if ($slug === '') {
            return null;
        }

        if (isset(self::LABELS[$slug])) {
            return $slug;
        }

        return self::LEGACY_MAP[$slug] ?? $slug;
    }

    /**
     * @return array<int, string|\Illuminate\Validation\Rules\In>
     */
    public static function validationRules(bool $required = true): array
    {
        $in = Rule::in(self::slugs());

        return $required ? ['required', $in] : ['nullable', $in];
    }

    /**
     * Spatie role name for app login — matches store job slug (one role per job title).
     */
    public static function spatieRoleForJob(?string $jobRole): string
    {
        $normalized = self::normalize($jobRole);

        if ($normalized !== null && isset(self::LABELS[$normalized])) {
            return $normalized;
        }

        return 'hair_stylist';
    }

    /**
     * All roles that appear in Team → Configure role permissions.
     *
     * @return array<string, string> slug => label
     */
    public static function permissionRoles(): array
    {
        return array_merge(
            ['tenant_admin' => 'Admin'],
            self::LABELS,
        );
    }

    /** @return list<string> */
    public static function permissionRoleSlugs(): array
    {
        return array_keys(self::permissionRoles());
    }

    /**
     * Job title slugs used on at least one staff profile in this salon.
     *
     * @return list<string>
     */
    public static function jobRoleSlugsInUseForSalon(int $salonId): array
    {
        return Staff::withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->whereNotNull('role')
            ->where('role', '!=', '')
            ->pluck('role')
            ->map(fn ($role) => self::normalize((string) $role))
            ->filter(fn (?string $slug) => $slug !== null && isset(self::LABELS[$slug]))
            ->unique()
            ->sortBy(fn (string $slug) => array_search($slug, self::slugs(), true))
            ->values()
            ->all();
    }

    /**
     * Team permissions UI: Admin + only job titles assigned to staff in this store.
     *
     * @return array<string, string>
     */
    public static function permissionRolesForSalon(int $salonId): array
    {
        $roles = ['tenant_admin' => 'Admin'];

        foreach (self::jobRoleSlugsInUseForSalon($salonId) as $slug) {
            $roles[$slug] = self::LABELS[$slug];
        }

        return $roles;
    }

    /** @return list<string> */
    public static function permissionRoleSlugsForSalon(int $salonId): array
    {
        return array_keys(self::permissionRolesForSalon($salonId));
    }

    /** Roles that only see their own appointments/clients (not full salon calendar). */
    public static function isOwnWorkScopeRole(string $roleSlug): bool
    {
        $roleSlug = self::normalize($roleSlug) ?? $roleSlug;

        return ! in_array($roleSlug, ['tenant_admin', 'salon_manager', 'receptionist'], true);
    }
}
