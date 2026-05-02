<?php

namespace App\Support;

use App\Models\User;

/**
 * Which sidebar links are shown for the current user (roles + invited stylist scope).
 */
final class SidebarNav
{
    /** Stylist-only accounts: minimal nav, no salon-management screens. */
    private const STYLIST_NAV = [
        'dashboard',
        'calendar',
        'appointments',
        'clients',
        'pos',
        'reviews',
        'notifications',
        'settings',
        'security_support',
    ];

    public static function show(User $user, string $item): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->dashboardScopedStaffId() !== null) {
            return in_array($item, self::STYLIST_NAV, true);
        }

        return match ($item) {
            'dashboard', 'calendar' => true,
            'appointments' => $user->can('appointments.view'),
            'clients' => $user->can('clients.view'),
            'staff' => $user->can('staff.view'),
            'services' => $user->can('services.view'),
            'service_packages' => $user->can('services.view') && $user->hasAnyRole(['tenant_admin', 'manager']),
            'multi_location' => $user->hasAnyRole(['tenant_admin', 'manager']),
            'availability' => $user->hasAnyRole(['tenant_admin', 'manager', 'receptionist']),
            'inventory' => $user->can('inventory.view'),
            'pos' => $user->can('pos.view'),
            'revenue' => $user->can('reports.view'),
            'go_live' => $user->hasAnyRole(['tenant_admin', 'manager']),
            'website_seo' => $user->hasAnyRole(['tenant_admin', 'manager']),
            'customization' => $user->hasAnyRole(['tenant_admin', 'manager']),
            'marketing' => $user->can('marketing.view'),
            'reviews' => $user->can('reviews.view'),
            'analytics', 'reports_menu', 'growth_tips' => $user->can('reports.view'),
            'billing' => $user->can('billing.view'),
            'settings' => $user->can('settings.view'),
            'security_support', 'notifications' => true,
            default => false,
        };
    }

    public static function showManageHeading(User $user): bool
    {
        foreach (['staff', 'services', 'service_packages', 'multi_location', 'availability', 'inventory'] as $key) {
            if (self::show($user, $key)) {
                return true;
            }
        }

        return false;
    }

    public static function showGrowHeading(User $user): bool
    {
        foreach (['go_live', 'website_seo', 'customization', 'marketing', 'reviews', 'analytics', 'reports_menu'] as $key) {
            if (self::show($user, $key)) {
                return true;
            }
        }

        return false;
    }
}
