<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration — XAMPP (file-based)
    |--------------------------------------------------------------------------
    | All channels write to storage/logs/*.log
    | Slack alerting channel is disabled by default — add SLACK_LOG_WEBHOOK
    | to .env to enable critical-error notifications.
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace'   => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    'channels' => [

        'stack' => [
            'driver'            => 'stack',
            'channels'          => explode(',', env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver'    => 'single',
            'path'      => storage_path('logs/laravel.log'),
            'level'     => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/laravel.log'),
            'level'  => env('LOG_LEVEL', 'debug'),
            'days'   => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        /* Security-significant events (auth, access, GDPR) */
        'security' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/security.log'),
            'level'  => 'info',
            'days'   => 90,
            'replace_placeholders' => true,
        ],

        /* Billing events — 365-day retention */
        'billing' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/billing.log'),
            'level'  => 'info',
            'days'   => 365,
            'replace_placeholders' => true,
        ],

        /* GDPR audit trail — 7-year retention required by UK GDPR */
        'gdpr' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/gdpr.log'),
            'level'  => 'info',
            'days'   => 2555,  // 7 years
            'replace_placeholders' => true,
        ],

        /* Slow query / performance log */
        'slow-queries' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/slow-queries.log'),
            'level'  => 'info',
            'days'   => 7,
            'replace_placeholders' => true,
        ],

        /* Slack — optional; set SLACK_LOG_WEBHOOK in .env to enable */
        'slack' => [
            'driver'   => 'slack',
            'url'      => env('SLACK_LOG_WEBHOOK'),
            'username' => 'Velour Alerts',
            'emoji'    => ':boom:',
            'level'    => env('LOG_SLACK_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'stderr' => [
            'driver'    => 'monolog',
            'level'     => env('LOG_LEVEL', 'debug'),
            'handler'   => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with'      => ['stream' => 'php://stderr'],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'null' => [
            'driver'  => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path'  => storage_path('logs/laravel.log'),
        ],

    ],

];
