<?php

/*
|──────────────────────────────────────────────────────────────────────────────
| VELOUR — Spatie Laravel Multitenancy Configuration
|──────────────────────────────────────────────────────────────────────────────
|
| Strategy: Single-database multitenancy.
| Tenants are stored in the `salons` table.
| Every tenant-owned row carries a `salon_id` foreign key that acts as the
| tenant identifier.  No data is ever stored in a separate per-tenant schema
| or database — all isolation is enforced through Eloquent global scopes.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    |
    | The Eloquent model that represents a tenant.  It MUST extend
    | Spatie\Multitenancy\Models\Tenant.
    |
    | We point it at the existing `salons` table so that the Salon record
    | doubles as the multitenancy tenant — no extra table required.
    |
    */

    'tenant_model' => \App\Models\Tenant::class,

    /*
    |--------------------------------------------------------------------------
    | Tenant Finder
    |--------------------------------------------------------------------------
    |
    | Resolves which tenant owns the current HTTP request.
    | Velour supports two resolution strategies, tried in order:
    |
    |   1. Exact custom domain   → salons.domain   e.g. "mysalon.com"
    |   2. Subdomain             → salons.subdomain e.g. "mysalon" from
    |                              "mysalon.velour.app"
    |
    | Set the APP_BASE_DOMAIN env variable to your root domain
    | (e.g. "velour.app") so subdomain extraction works correctly.
    |
    */

    'tenant_finder' => \App\Multitenancy\TenantFinder\DomainOrSubdomainTenantFinder::class,

    /*
    |--------------------------------------------------------------------------
    | Tasks — Run When a Tenant Is Made Current
    |--------------------------------------------------------------------------
    |
    | These tasks execute (in order) every time Tenant::makeCurrent() is
    | called — i.e. when the middleware resolves the tenant for a request.
    |
    */

    'switch_tenant_tasks' => [
        \App\Multitenancy\Tasks\SwitchTenantSession::class,
        \App\Multitenancy\Tasks\BindTenantToContainer::class,
        \App\Multitenancy\Tasks\SetTenantCachePrefix::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tasks — Run When Tenant Is Forgotten (request end / artisan)
    |--------------------------------------------------------------------------
    */

    'forget_current_tenant_tasks' => [
        \App\Multitenancy\Tasks\ForgetTenantSession::class,
        \App\Multitenancy\Tasks\ClearTenantCachePrefix::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Column Name
    |--------------------------------------------------------------------------
    |
    | The column used on ALL tenant-owned tables to store the tenant FK.
    | Because the existing schema already uses `salon_id` everywhere, we
    | override Spatie's default of `tenant_id` here.
    |
    */

    'tenant_key_column' => 'salon_id',

    /*
    |--------------------------------------------------------------------------
    | Current Tenant Value Resolution
    |--------------------------------------------------------------------------
    |
    | How to derive the value stored in `salon_id` from the Tenant model.
    | We simply use the model's primary key (Salon.id).
    |
    */

    'tenant_key' => 'id',

    /*
    |--------------------------------------------------------------------------
    | Queued Jobs — Tenant Awareness
    |--------------------------------------------------------------------------
    |
    | When true, queued jobs that implement TenantAware will automatically
    | restore the correct tenant before executing.
    |
    */

    'queues_are_tenant_aware_by_default' => true,

];
