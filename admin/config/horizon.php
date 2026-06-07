<?php

use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Laravel Horizon Configuration
|--------------------------------------------------------------------------
| Horizon requires Redis. On XAMPP the queue uses the 'database' driver,
| so Horizon is NOT used. This config file is kept for when the project
| is moved to a server with Redis.
|
| On XAMPP, manage the queue with:
|   php artisan queue:work --sleep=3 --tries=3
|
| Or use scripts/queue-worker.bat (Windows)
*/

return [
    'domain'     => env('HORIZON_DOMAIN'),
    'path'       => env('HORIZON_PATH', 'horizon'),
    'use'        => 'default',
    'prefix'     => env('HORIZON_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_horizon:'),
    'middleware' => ['web'],
    'waits'      => ['redis:default' => 60],
    'trim'       => [
        'recent'         => 60,
        'pending'        => 60,
        'completed'      => 60,
        'recent_failed'  => 10080,
        'failed'         => 10080,
        'monitored'      => 10080,
    ],
    'silenced'         => [],
    'metrics'          => ['trim_snapshots' => ['job' => 24, 'queue' => 24]],
    'fast_termination' => false,
    'memory_limit'     => 256,
    'defaults'         => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue'      => ['default'],
            'balance'    => 'auto',
            'maxProcesses' => 1,
            'memory'     => 128,
            'tries'      => 3,
            'timeout'    => 60,
            'nice'       => 0,
        ],
    ],
    'environments' => [
        'production' => ['supervisor-1' => ['maxProcesses' => 5]],
        'local'      => ['supervisor-1' => ['maxProcesses' => 1]],
    ],
];
