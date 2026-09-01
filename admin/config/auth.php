<?php
return [
    'defaults'  => ['guard' => 'web', 'passwords' => 'users'],

    // "Stay signed in" cookie lifetime (minutes). Default: 30 days.
    'remember_lifetime' => (int) env('AUTH_REMEMBER_LIFETIME', 43_200),

    'guards'    => [
        'web'     => ['driver' => 'session', 'provider' => 'users'],
        'sanctum' => ['driver' => 'sanctum', 'provider' => 'users'],
        'client'  => ['driver' => 'sanctum', 'provider' => 'clients'],
    ],
    'providers' => [
        'users'   => ['driver' => 'eloquent', 'model' => App\Models\User::class],
        'clients' => ['driver' => 'eloquent', 'model' => App\Models\Client::class],
    ],
    'passwords' => [
        'users'   => ['provider' => 'users', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 60],
        'clients' => ['provider' => 'clients', 'table' => 'password_reset_tokens', 'expire' => 60, 'throttle' => 60],
    ],
    'password_timeout' => 10800,
];
