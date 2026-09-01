<?php

namespace App\Multitenancy\Tasks;

use App\Models\Tenant;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

/**
 * SwitchTenantSession
 *
 * Stores the current tenant's ID in the Laravel session so that it survives
 * across requests in stateful (web/cookie) flows (e.g. the dashboard UI).
 *
 * This is complementary to the request-based domain resolution — if a web
 * user is authenticated and their session already carries a tenant ID, the
 * tenant context can be restored without a DB lookup on every page.
 *
 * Lifecycle:
 *   makeCurrent($tenant)  →  session(['velour_tenant_id' => $tenant->id])
 *   forgetCurrent()       →  session()->forget('velour_tenant_id')
 */
class SwitchTenantSession implements SwitchTenantTask
{
    protected const SESSION_KEY = 'velour_tenant_id';

    /**
     * Store the tenant in the session.
     */
    public function makeCurrent(IsTenant $tenant): void
    {
        if (app()->runningInConsole() || ! app()->bound('session')) {
            return;
        }

        session([self::SESSION_KEY => $tenant->getKey()]);
    }

    /**
     * Remove the tenant from the session.
     */
    public function forgetCurrent(): void
    {
        if (app()->runningInConsole() || ! app()->bound('session')) {
            return;
        }

        session()->forget(self::SESSION_KEY);
    }
}
