<?php

/*
|──────────────────────────────────────────────────────────────────────────────
| Laravel 11 — Service Providers
|──────────────────────────────────────────────────────────────────────────────
*/

return [
    App\Providers\AppServiceProvider::class,

    /*
    |--------------------------------------------------------------------------
    | Multitenancy
    |--------------------------------------------------------------------------
    |
    | Registers the Spatie multitenancy integration including the TenantFinder
    | singleton, queue tenant-awareness, and tenant event listeners.
    |
    */
    App\Providers\TenancyServiceProvider::class,
];
