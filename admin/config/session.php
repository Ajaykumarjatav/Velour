<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Session Driver — file (XAMPP compatible)
    |--------------------------------------------------------------------------
    | Changed from 'redis' to 'file' for XAMPP.
    | Sessions are stored in storage/framework/sessions.
    |
    | Alternatives for production:
    |   'database' — stored in the sessions table (already migrated)
    |   'redis'    — if you install Redis separately
    */
    'driver' => env('SESSION_DRIVER', 'file'),

    'lifetime'            => env('SESSION_LIFETIME', 120),
    'expire_on_close'     => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    | Set to true only when running HTTPS (not local XAMPP http).
    | Override in .env: SESSION_SECURE_COOKIE=false for local dev.
    */
    'encrypt'             => env('SESSION_ENCRYPT', false),
    'files'               => storage_path('framework/sessions'),
    'connection'          => env('SESSION_CONNECTION'),
    'table'               => env('SESSION_TABLE', 'sessions'),
    'store'               => env('SESSION_STORE'),
    'lottery'             => [2, 100],
    'cookie'              => env('SESSION_COOKIE', Str::slug(env('APP_NAME', 'laravel'), '_').'_session'),
    'path'                => env('SESSION_PATH', '/'),
    'domain'              => env('SESSION_DOMAIN'),
    'secure'              => env('SESSION_SECURE_COOKIE', false),
    'http_only'           => env('SESSION_HTTP_ONLY', true),
    'same_site'           => env('SESSION_SAME_SITE', 'lax'),
    'partitioned'         => env('SESSION_PARTITIONED_COOKIE', false),

];
