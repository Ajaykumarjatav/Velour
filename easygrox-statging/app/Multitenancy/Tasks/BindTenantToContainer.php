<?php

namespace App\Multitenancy\Tasks;

use App\Models\Tenant;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

/**
 * BindTenantToContainer
 *
 * Registers the current Tenant in the Laravel service container under two
 * bindings so any class can type-hint it for injection:
 *
 *   app(Tenant::class)                  → current Tenant (Salon)
 *   app(\App\Models\Tenant::class)      → same
 *
 * This lets controllers and services receive the tenant via constructor
 * injection without calling the static Tenant::current() themselves:
 *
 *   class AppointmentController {
 *       public function __construct(protected Tenant $tenant) {}
 *   }
 */
class BindTenantToContainer implements SwitchTenantTask
{
    public function makeCurrent(IsTenant $tenant): void
    {
        app()->instance(Tenant::class, $tenant);
        app()->instance(\App\Models\Tenant::class, $tenant);
    }

    public function forgetCurrent(): void
    {
        // Remove the bindings so the next request starts fresh.
        if (app()->bound(Tenant::class)) {
            app()->forgetInstance(Tenant::class);
        }
        if (app()->bound(\App\Models\Tenant::class)) {
            app()->forgetInstance(\App\Models\Tenant::class);
        }
    }
}
