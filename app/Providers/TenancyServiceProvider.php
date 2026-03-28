<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Multitenancy\TenantFinder\DomainOrSubdomainTenantFinder;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Spatie\Multitenancy\Jobs\MakeQueueTenantAwareAction;

/**
 * TenancyServiceProvider
 *
 * Bootstraps all multitenancy concerns:
 *
 *  1. Binds the TenantFinder as a singleton so it is resolved once per
 *     request and injected wherever needed (e.g. InitializeTenancyFromDomain).
 *
 *  2. Wires up queue tenant awareness so that jobs dispatched inside a tenant
 *     context automatically carry and restore the correct tenant when they
 *     execute.
 *
 *  3. Registers Tenant model event listeners for logging / auditing purposes.
 *
 * Registration:
 *   Add \App\Providers\TenancyServiceProvider::class to the `providers` array
 *   in bootstrap/providers.php (Laravel 11) or config/app.php (Laravel 10).
 */
class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind the TenantFinder as a singleton.
        $this->app->singleton(
            DomainOrSubdomainTenantFinder::class,
            fn () => new DomainOrSubdomainTenantFinder()
        );
    }

    public function boot(): void
    {
        $this->makeQueuesTenantsAware();
        $this->registerTenantEventListeners();
    }

    /*
    |--------------------------------------------------------------------------
    | Queue Tenant Awareness
    |--------------------------------------------------------------------------
    |
    | When a job is dispatched inside a tenant context, serialize the tenant
    | ID into the job payload.  Before the job runs, look up the tenant and
    | call makeCurrent() so all DB queries inside the job are scoped correctly.
    |
    */

    private function makeQueuesTenantsAware(): void
    {
        if (! config('multitenancy.queues_are_tenant_aware_by_default', true)) {
            return;
        }

        Queue::before(function (JobProcessing $event) {
            $this->restoreTenantForQueuedJob($event);
        });
    }

    private function restoreTenantForQueuedJob(JobProcessing $event): void
    {
        $payload = $event->job->payload();

        // The Spatie package injects `tenant_id` into the job payload when
        // the job is dispatched inside a tenant context.
        if (! isset($payload['tenant_id'])) {
            return;
        }

        $tenant = Tenant::query()
            ->withoutGlobalScopes()
            ->find($payload['tenant_id']);

        if ($tenant === null) {
            \Illuminate\Support\Facades\Log::warning(
                '[Tenancy] Queued job references a tenant that no longer exists',
                ['tenant_id' => $payload['tenant_id'], 'job' => $event->job->getName()]
            );
            return;
        }

        $tenant->makeCurrent();
    }

    /*
    |--------------------------------------------------------------------------
    | Tenant Lifecycle Events
    |--------------------------------------------------------------------------
    */

    private function registerTenantEventListeners(): void
    {
        // Log whenever a new salon/tenant is created.
        Tenant::created(function (Tenant $tenant) {
            \Illuminate\Support\Facades\Log::info('[Tenancy] New tenant created', [
                'id'        => $tenant->getKey(),
                'name'      => $tenant->name,
                'subdomain' => $tenant->subdomain,
            ]);
        });

        // Log whenever a tenant is deactivated (is_active → false).
        Tenant::updated(function (Tenant $tenant) {
            if ($tenant->wasChanged('is_active') && ! $tenant->is_active) {
                \Illuminate\Support\Facades\Log::warning('[Tenancy] Tenant deactivated', [
                    'id'   => $tenant->getKey(),
                    'name' => $tenant->name,
                ]);
            }
        });
    }
}
