<?php

namespace App\Multitenancy\Tasks;

use App\Models\Tenant;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

/**
 * ForgetTenantSession
 *
 * Called when the current tenant is forgotten (request teardown or explicit
 * Tenant::forgetCurrent() call).  Removes the tenant session key so the next
 * request starts clean.
 */
class ForgetTenantSession implements SwitchTenantTask
{
    protected const SESSION_KEY = 'velour_tenant_id';

    public function makeCurrent(IsTenant $tenant): void
    {
        // Nothing to do on makeCurrent
    }

    public function forgetCurrent(): void
    {
        if (app()->runningInConsole() || ! app()->bound('session')) {
            return;
        }

        session()->forget(self::SESSION_KEY);
    }
}
