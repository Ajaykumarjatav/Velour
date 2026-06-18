<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Maps named routes to required Spatie permissions (view-level minimum).
 */
final class RoutePermissionMap
{
    /** @var array<string, string> pattern => permission */
    private const MAP = [
        'appointments.*' => 'appointments.view',
        'calendar' => 'appointments.view',
        'clients.*' => 'clients.view',
        'staff.*' => 'staff.view',
        'services.*' => 'services.view',
        'service-packages.*' => 'services.view',
        'service-categories.*' => 'services.view',
        'multi-location.*' => 'multi-location.view',
        'availability.*' => 'staff.view',
        'inventory.*' => 'inventory.view',
        'expenses.*' => 'expenses.view',
        'quick-create.expense-category' => 'expenses.create',
        'pos.*' => 'pos.view',
        'marketing.*' => 'marketing.view',
        'reports.*' => 'reports.view',
        'revenue.*' => 'reports.view',
        'reviews.*' => 'reviews.view',
        'settings.salon' => 'settings.business.edit',
        'settings.booking' => 'settings.booking.edit',
        'settings.buffer-rules' => 'settings.booking.edit',
        'settings.services' => 'settings.services.edit',
        'settings.hours' => 'settings.hours.edit',
        'settings.notifications' => 'settings.notifications.edit',
        'settings.profile' => 'settings.profile.edit',
        'settings.team-members' => 'settings.team.edit',
        'settings.password' => 'settings.security.edit',
        'settings.social-links' => 'settings.social.edit',
        'payments.gateway*' => 'settings.business.edit',
        'payments.charge*' => 'pos.view',
        'go-live*' => 'website.view',
        'website-seo.*' => 'website.view',
        'customization.*' => 'website.view',
        'salon-admin.team*' => 'users.view',
        'salon-admin.subscription*' => 'billing.view',
        'salon-admin.transfer*' => 'users.edit',
        'tasks.*' => 'appointments.view',
        'action-items.*' => 'appointments.view',
        'activity.index' => 'settings.business.view',
    ];

    public static function permissionForRoute(?string $routeName): ?string
    {
        if ($routeName === null || $routeName === '') {
            return null;
        }

        foreach (self::MAP as $pattern => $permission) {
            if (self::matches($pattern, $routeName)) {
                return $permission;
            }
        }

        return null;
    }

    private static function matches(string $pattern, string $routeName): bool
    {
        if ($pattern === $routeName) {
            return true;
        }

        if (str_ends_with($pattern, '.*')) {
            $prefix = substr($pattern, 0, -2);

            return $routeName === $prefix || str_starts_with($routeName, $prefix.'.');
        }

        if (str_ends_with($pattern, '*')) {
            $prefix = rtrim($pattern, '*');

            return str_starts_with($routeName, $prefix);
        }

        return false;
    }
}
