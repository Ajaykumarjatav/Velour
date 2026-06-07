<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application — XAMPP / Local Development
    |--------------------------------------------------------------------------
    */

    'name'            => env('APP_NAME', 'Velour Salon SaaS'),
    'env'             => env('APP_ENV', 'local'),
    'debug'           => (bool) env('APP_DEBUG', true),
    'url'             => env('APP_URL', 'http://localhost'),
    'frontend_url'    => env('APP_FRONTEND_URL', 'http://localhost'),
    'asset_url'       => env('ASSET_URL'),
    'timezone'        => 'UTC',
    'locale'          => 'en',
    'fallback_locale' => 'en',
    'faker_locale'    => 'en_GB',
    'key'             => env('APP_KEY'),
    'previous_keys'   => array_filter(explode(',', env('APP_PREVIOUS_KEYS', ''))),
    'cipher'          => 'AES-256-CBC',
    'maintenance'     => ['driver' => 'file'],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    | In Laravel 11, service providers are registered in bootstrap/providers.php.
    | This array should only list framework default providers.
    | Do NOT add App\ providers here — they are in bootstrap/providers.php.
    */
    'providers' => \Illuminate\Support\ServiceProvider::defaultProviders()->toArray(),

];
