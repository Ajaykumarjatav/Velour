<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store — file (XAMPP compatible)
    |--------------------------------------------------------------------------
    | Changed from 'redis' to 'file' for XAMPP.
    | Cache is stored in storage/framework/cache/data.
    |
    | For production: switch to 'redis' and install Redis,
    | or use 'database' with the cache table.
    */
    'default' => env('CACHE_STORE', 'file'),

    'stores' => [

        'file' => [
            'driver'     => 'file',
            'path'       => storage_path('framework/cache/data'),
            'lock_path'  => storage_path('framework/cache/data'),
        ],

        'database' => [
            'driver'     => 'database',
            'connection' => env('DB_CACHE_CONNECTION'),
            'table'      => env('DB_CACHE_TABLE', 'cache'),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
            'lock_table' => env('DB_CACHE_LOCK_TABLE'),
        ],

        'array' => [
            'driver'    => 'array',
            'serialize' => false,
        ],

        /* Redis — only used if CACHE_STORE=redis is set in .env */
        'redis' => [
            'driver'          => 'redis',
            'connection'      => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
        ],

    ],

    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache_'),

];
