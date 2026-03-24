<?php

/*
|──────────────────────────────────────────────────────────────────────────────
| VELOUR — CORS Configuration (tenant-aware)
|──────────────────────────────────────────────────────────────────────────────
|
| Origin model:
|   • All *.velour.app subdomains (tenant domains) via regex pattern.
|   • Explicit origins from env (APP_URL, APP_FRONTEND_URL, CORS_ALLOWED_ORIGINS).
|   • Localhost on any port — only in local/testing environments.
|   • Custom tenant domains are validated by ValidateCorsOrigin middleware.
|
*/

$appDomain   = env('APP_DOMAIN',       'velour.app');
$appUrl      = env('APP_URL',          'https://velour.app');
$frontendUrl = env('APP_FRONTEND_URL', 'https://app.velour.app');

// use environment variable directly since the container
// may not yet have the "env" binding when config files are
// being loaded. calling app()->environment() earlier will
// trigger the "Target class [env] does not exist" error.
$env = env('APP_ENV', 'production');
$trusted = array_filter(array_unique(array_merge(
    [$appUrl, $frontendUrl],
    in_array($env, ['local', 'testing', 'staging'], true) ? [
        'http://localhost:3000', 'http://localhost:5173',
        'http://127.0.0.1:3000', 'http://127.0.0.1:8000',
    ] : [],
    array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', '')))
)));

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'up'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_values($trusted),

    'allowed_origins_patterns' => array_values(array_filter([
        // All tenant subdomains: https://*.velour.app
        '#^https://[a-z0-9\-]+\.' . preg_quote($appDomain, '#') . '$#',
        // Staging: https://*.staging.velour.app
        '#^https://[a-z0-9\-]+\.staging\.' . preg_quote($appDomain, '#') . '$#',
        // Local dev any port (non-production only)
        in_array($env, ['local', 'testing'], true)
            ? '#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#'
            : null,
    ])),

    'allowed_headers' => [
        'Content-Type', 'Authorization', 'X-Requested-With', 'Accept',
        'Accept-Language', 'X-XSRF-TOKEN', 'X-Request-ID',
        'X-Salon-Subdomain', 'Idempotency-Key',
    ],

    'exposed_headers' => [
        'X-RateLimit-Limit', 'X-RateLimit-Remaining', 'X-RateLimit-Reset',
        'Retry-After', 'X-Request-ID', 'X-Tenant-ID',
    ],

    'max_age' => 7200,

    'supports_credentials' => true,
];
