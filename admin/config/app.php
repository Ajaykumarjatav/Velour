<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application — XAMPP / Local Development
    |--------------------------------------------------------------------------
    */

    'name'            => env('APP_NAME', 'EasyGrox'),
    'env'             => env('APP_ENV', 'local'),
    'debug'           => (bool) env('APP_DEBUG', true),
    'url'             => env('APP_URL', 'http://localhost'),
    // Public / marketing / booking origin. Falls back to APP_URL without trailing /admin
    // so local → localhost/{app folder}, staging → staging host, production → production host.
    'frontend_url'    => rtrim(
        (string) (env('APP_FRONTEND_URL') ?: preg_replace('#/admin/?$#', '', rtrim((string) env('APP_URL', 'http://localhost'), '/'))),
        '/'
    ),
    'asset_url'       => env('ASSET_URL'),
    // Keep UTC for Eloquent datetime casts — appointments are stored as UTC instants.
    'timezone'           => 'UTC',
    // Default business / display timezone when a salon has none set (India).
    'business_timezone'  => env('APP_BUSINESS_TIMEZONE', 'Asia/Kolkata'),
    // Default business currency when a salon has none set (India).
    'business_currency'  => env('APP_BUSINESS_CURRENCY', 'INR'),
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
