<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

/**
 * Maps authenticated users to platform admin vs salon (tenant/staff) panels.
 */
final class AuthPanel
{
    public const PLATFORM = 'platform';

    public const TENANT = 'tenant';

    public const STAFF = 'staff';

    public static function typeFor(User $user): string
    {
        if ($user->isSuperAdmin()) {
            return self::PLATFORM;
        }

        if ($user->dashboardScopedStaffId() !== null) {
            return self::STAFF;
        }

        return self::TENANT;
    }

    public static function labelFor(User $user): string
    {
        return match (self::typeFor($user)) {
            self::PLATFORM => 'Platform admin',
            self::STAFF => 'Staff',
            default => 'Salon admin',
        };
    }

    public static function homeUrl(User $user): string
    {
        return self::typeFor($user) === self::PLATFORM
            ? route('admin.dashboard')
            : route('dashboard');
    }

    public static function canAccessPlatformPanel(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public static function canAccessSalonPanel(User $user): bool
    {
        if (self::typeFor($user) !== self::PLATFORM) {
            return true;
        }

        return session('impersonating') === true;
    }

    public static function canAccessUrl(User $user, string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        if (self::isPlatformPath($path)) {
            return self::canAccessPlatformPanel($user);
        }

        if (self::isSalonAppPath($path)) {
            return self::canAccessSalonPanel($user);
        }

        return true;
    }

    public static function isPlatformPath(string $path): bool
    {
        return (bool) preg_match('#/admin(/|$)#', $path);
    }

    public static function isSalonAppPath(string $path): bool
    {
        if (self::isPlatformPath($path)) {
            return false;
        }

        $prefixes = [
            'dashboard', 'calendar', 'appointments', 'clients', 'staff', 'services',
            'service-packages', 'service-categories', 'availability', 'inventory',
            'pos', 'marketing', 'reports', 'revenue', 'reviews', 'settings', 'payments',
            'go-live', 'website-seo', 'customization', 'multi-location', 'tasks',
            'action-items', 'notifications', 'guide', 'deleted-items', 'security-support',
            'salon-admin', 'setup-progress', 'billing', 'onboarding',
        ];

        foreach ($prefixes as $prefix) {
            if (preg_match('#/'.$prefix.'(/|$)#', $path)) {
                return true;
            }
        }

        return false;
    }
}
