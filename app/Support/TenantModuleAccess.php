<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Super-admin global on/off for tenant-panel modules.
 * Disabled modules are hidden from every tenant (including salon owners).
 */
final class TenantModuleAccess
{
    public const CACHE_KEY = 'platform.tenant_modules_disabled';

    public const DISABLED_SETTING_KEY = 'tenant_modules_disabled';

    /**
     * Tenant sidebar / account modules super admin can toggle.
     *
     * @return array<string, array{label: string, group: string, always_on?: bool}>
     */
    public static function modules(): array
    {
        return [
            'dashboard' => ['label' => 'Dashboard', 'group' => 'Main', 'always_on' => true],
            'tasks' => ['label' => 'Tasks', 'group' => 'Main'],
            'calendar' => ['label' => 'Calendar', 'group' => 'Main'],
            'appointments' => ['label' => 'Appointments', 'group' => 'Main'],
            'clients' => ['label' => 'Clients', 'group' => 'Main'],

            'staff' => ['label' => 'Staff & HR', 'group' => 'Business'],
            'services' => ['label' => 'Services', 'group' => 'Business'],
            'service_packages' => ['label' => 'Plans / Packages', 'group' => 'Business'],
            'multi_location' => ['label' => 'Multi-location', 'group' => 'Business'],
            'availability' => ['label' => 'Availability & Resources', 'group' => 'Business'],
            'inventory' => ['label' => 'Inventory & Retail', 'group' => 'Business'],
            'expenses' => ['label' => 'Expenses', 'group' => 'Business'],
            'pos' => ['label' => 'Point of Sale', 'group' => 'Business'],

            'go_live' => ['label' => 'Go Live & Share', 'group' => 'Growth'],
            'marketing' => ['label' => 'Marketing', 'group' => 'Growth'],
            'reviews' => ['label' => 'Reviews', 'group' => 'Growth'],
            'analytics' => ['label' => 'Analytics', 'group' => 'Growth'],
            'reports_menu' => ['label' => 'Reports', 'group' => 'Growth'],
            'growth_tips' => ['label' => 'Growth Tips', 'group' => 'Growth'],

            'billing' => ['label' => 'Billing', 'group' => 'Account'],
            'settings' => ['label' => 'Settings', 'group' => 'Account'],
            'security_support' => ['label' => 'Security & 2FA', 'group' => 'Account'],
            'notifications' => ['label' => 'Notifications', 'group' => 'Account'],
            'deleted_items' => ['label' => 'Deleted Items', 'group' => 'Account'],
            'support' => ['label' => 'Support / Assistant', 'group' => 'Account'],
            'guide' => ['label' => 'Guide & Setup', 'group' => 'Account'],

            'team' => ['label' => 'Team (Admin)', 'group' => 'Admin'],
        ];
    }

    /**
     * Settings inner tabs (only apply when Settings itself is enabled).
     *
     * @return array<string, string> key => label
     */
    public static function settingsTabs(): array
    {
        $tabs = [];
        foreach (SettingsTabPermissions::TABS as $key => $meta) {
            $tabs['settings_tab.'.$key] = $meta['label'];
        }

        return $tabs;
    }

    /**
     * @return list<string>
     */
    public static function allToggleableKeys(): array
    {
        $keys = [];
        foreach (self::modules() as $key => $meta) {
            if (! ($meta['always_on'] ?? false)) {
                $keys[] = $key;
            }
        }

        return array_merge($keys, array_keys(self::settingsTabs()));
    }

    public static function isEnabled(string $moduleKey): bool
    {
        $meta = self::modules()[$moduleKey] ?? null;
        if ($meta && ($meta['always_on'] ?? false)) {
            return true;
        }

        return ! in_array($moduleKey, self::disabledKeys(), true);
    }

    public static function isSettingsTabEnabled(string $tab): bool
    {
        if (! self::isEnabled('settings')) {
            return false;
        }

        return self::isEnabled('settings_tab.'.$tab);
    }

    /**
     * @return list<string>
     */
    public static function disabledKeys(): array
    {
        try {
            if (! Schema::hasTable('platform_settings')) {
                return [];
            }
        } catch (\Throwable) {
            return [];
        }

        $raw = PlatformSetting::query()
            ->where('key', self::DISABLED_SETTING_KEY)
            ->value('value');
        $decoded = is_string($raw) ? json_decode($raw, true) : [];

        return is_array($decoded)
            ? array_values(array_filter($decoded, fn ($k) => is_string($k) && $k !== ''))
            : [];
    }

    /**
     * @param  array<string, mixed>  $flags  module key => '1'|'0'|bool
     */
    public static function syncFromFlags(array $flags): void
    {
        $disabled = [];
        foreach (self::allToggleableKeys() as $key) {
            $on = filter_var($flags[$key] ?? false, FILTER_VALIDATE_BOOLEAN);
            if (! $on) {
                $disabled[] = $key;
            }
        }

        self::setDisabledKeys($disabled);
    }

    /**
     * @param  list<string>  $disabledKeys
     */
    public static function setDisabledKeys(array $disabledKeys): void
    {
        $allowed = self::allToggleableKeys();
        $clean = array_values(array_unique(array_intersect($disabledKeys, $allowed)));

        PlatformSetting::query()->updateOrCreate(
            ['key' => self::DISABLED_SETTING_KEY],
            ['value' => json_encode($clean)]
        );

        Cache::forget(self::CACHE_KEY);
        Cache::forget('platform.tenant_modules_disabled');
    }

    public static function moduleForRoute(?string $routeName): ?string
    {
        if ($routeName === null || $routeName === '') {
            return null;
        }

        $map = [
            'dashboard' => 'dashboard',
            'tasks.*' => 'tasks',
            'action-items.*' => 'tasks',
            'calendar' => 'calendar',
            'appointments.*' => 'appointments',
            'clients.*' => 'clients',
            'staff.*' => 'staff',
            'services.*' => 'services',
            'service-packages.*' => 'service_packages',
            'service-categories.*' => 'services',
            'multi-location.*' => 'multi_location',
            'availability.*' => 'availability',
            'inventory.*' => 'inventory',
            'expenses.*' => 'expenses',
            'quick-create.expense-category' => 'expenses',
            'pos.*' => 'pos',
            'go-live*' => 'go_live',
            'website-seo.*' => 'go_live',
            'customization.*' => 'go_live',
            'marketing.*' => 'marketing',
            'reviews.*' => 'reviews',
            'reports.analytics' => 'analytics',
            'reports.growth-tips' => 'growth_tips',
            'reports.*' => 'reports_menu',
            'revenue.*' => 'reports_menu',
            'billing.*' => 'billing',
            'salon-admin.subscription*' => 'billing',
            'settings.*' => 'settings',
            'two-factor.*' => 'security_support',
            'security-support.*' => 'security_support',
            'notifications.*' => 'notifications',
            'deleted-items.*' => 'deleted_items',
            'guide.*' => 'guide',
            'salon-admin.team*' => 'team',
            'salon-admin.transfer*' => 'team',
        ];

        foreach ($map as $pattern => $module) {
            if (self::routeMatches($pattern, $routeName)) {
                return $module;
            }
        }

        return null;
    }

    public static function settingsTabForRoute(?string $routeName): ?string
    {
        return match ($routeName) {
            'settings.salon' => 'salon',
            'settings.booking', 'settings.buffer-rules' => 'booking',
            'settings.services' => 'services',
            'settings.hours' => 'hours',
            'settings.social-links' => 'social',
            'settings.notifications' => 'notifications',
            'settings.profile' => 'profile',
            'settings.team-members' => 'team',
            'settings.password' => 'security',
            default => null,
        };
    }

    private static function routeMatches(string $pattern, string $routeName): bool
    {
        if ($pattern === $routeName) {
            return true;
        }

        if (str_ends_with($pattern, '.*')) {
            $prefix = substr($pattern, 0, -2);

            return $routeName === $prefix || str_starts_with($routeName, $prefix.'.');
        }

        if (str_ends_with($pattern, '*')) {
            return str_starts_with($routeName, substr($pattern, 0, -1));
        }

        return false;
    }
}
