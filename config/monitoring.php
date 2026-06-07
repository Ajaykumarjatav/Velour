<?php

/**
 * Monitoring & Alert Configuration — AUDIT FIX: Logging, Monitoring & Alert System
 *
 * Centralises all alert thresholds, Slack webhook, and uptime check config.
 * Reference this file in console commands and jobs that send alerts.
 */
return [

    /*
    |──────────────────────────────────────────────────────────────────────────
    | Slack Alerts
    |──────────────────────────────────────────────────────────────────────────
    */
    'slack' => [
        'webhook_url'     => env('SLACK_WEBHOOK_URL'),
        'ops_channel'     => env('SLACK_OPS_CHANNEL', '#ops-alerts'),
        'billing_channel' => env('SLACK_BILLING_CHANNEL', '#billing'),
        'security_channel'=> env('SLACK_SECURITY_CHANNEL', '#security'),
    ],

    /*
    |──────────────────────────────────────────────────────────────────────────
    | Alert Thresholds
    |──────────────────────────────────────────────────────────────────────────
    */
    'thresholds' => [
        // Queue
        'failed_jobs_alert'       => env('ALERT_FAILED_JOBS', 10),
        'pending_jobs_alert'      => env('ALERT_PENDING_JOBS', 500),

        // Performance
        'slow_request_ms'         => env('ALERT_SLOW_REQUEST_MS', 2000),
        'slow_query_ms'           => env('ALERT_SLOW_QUERY_MS', 500),
        'high_query_count'        => env('ALERT_HIGH_QUERY_COUNT', 20),

        // Security
        'login_failure_alert'     => env('ALERT_LOGIN_FAILURES', 20),  // per hour, per IP
        'cross_tenant_alert'      => 1,   // any cross-tenant attempt → alert immediately

        // Business
        'trial_expiry_days'       => [7, 3, 1, 0],
        'subscription_failure_retry_days' => 7,

        // Infrastructure
        'disk_usage_percent'      => env('ALERT_DISK_PERCENT', 80),
        'memory_usage_percent'    => env('ALERT_MEMORY_PERCENT', 85),
        'scheduler_stale_seconds' => 150,
        'ssl_expiry_days_warning' => 30,
    ],

    /*
    |──────────────────────────────────────────────────────────────────────────
    | Uptime Monitoring
    |──────────────────────────────────────────────────────────────────────────
    | Configure your uptime service (Better Uptime, Uptime Robot, Pingdom)
    | to hit GET /api/v1/health every 60 seconds.
    | Alert when: HTTP status !== 200 for 2 consecutive checks.
    */
    'uptime' => [
        'health_endpoint'      => '/api/v1/health',
        'check_interval_secs'  => 60,
        'consecutive_failures' => 2,
        'alert_recovery'       => true,
    ],

    /*
    |──────────────────────────────────────────────────────────────────────────
    | Error Tracking (Sentry)
    |──────────────────────────────────────────────────────────────────────────
    */
    'sentry' => [
        'dsn'              => env('SENTRY_LARAVEL_DSN'),
        'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.1),
        'profiles_sample_rate'=> env('SENTRY_PROFILES_SAMPLE_RATE', 0.05),
        'environment'      => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),
        'release'          => env('SENTRY_RELEASE', config('velour.version', '1.0.0')),
    ],

    /*
    |──────────────────────────────────────────────────────────────────────────
    | Audit Log Retention
    |──────────────────────────────────────────────────────────────────────────
    */
    'retention' => [
        'audit_logs_days'       => env('AUDIT_RETENTION_DAYS', 365),
        'gdpr_logs_days'        => env('GDPR_RETENTION_DAYS', 365 * 7),
        'billing_logs_days'     => env('BILLING_RETENTION_DAYS', 365),
        'security_logs_days'    => env('SECURITY_RETENTION_DAYS', 365),
        'login_attempts_days'   => env('LOGIN_ATTEMPTS_RETENTION_DAYS', 90),
    ],
];
