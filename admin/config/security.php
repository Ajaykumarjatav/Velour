<?php

/*
|──────────────────────────────────────────────────────────────────────────────
| VELOUR — Security Configuration
|──────────────────────────────────────────────────────────────────────────────
|
| Central configuration for:
|   • Security response headers (CSP, HSTS, Permissions-Policy, etc.)
|   • Plan-tiered rate limits
|   • Audit log retention
|   • CORS dynamic origin rules
|   • Suspicious activity thresholds
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy (CSP)
    |--------------------------------------------------------------------------
    |
    | Controls what resources the browser is permitted to load.
    | Nonces are generated per-request by SecurityHeaders middleware and
    | injected into Blade via $cspNonce.
    |
    | In report-only mode the policy is enforced without blocking, allowing
    | you to tune it before enabling enforcement.
    |
    */
    'csp' => [
        'enabled'     => env('CSP_ENABLED', true),
        'report_only' => env('CSP_REPORT_ONLY', false),
        'report_uri'  => env('CSP_REPORT_URI', null), // e.g. https://velour.report-uri.com/r/d/csp/enforce

        'directives' => [
            'default-src'     => ["'self'"],
            'script-src'      => ["'self'", "'nonce'", 'https://js.stripe.com', 'https://cdn.jsdelivr.net'],
            'style-src'       => ["'self'", "'nonce'", "'unsafe-inline'", 'https://fonts.googleapis.com', 'https://cdn.jsdelivr.net'],
            'font-src'        => ["'self'", 'https://fonts.gstatic.com', 'data:'],
            'img-src'         => ["'self'", 'data:', 'blob:', 'https:', 'https://api.qrserver.com'],
            'connect-src'     => ["'self'", 'https://api.stripe.com', 'https://vitals.vercel-insights.com'],
            'frame-src'       => ["'self'", 'https://js.stripe.com', 'https://hooks.stripe.com'],
            'frame-ancestors' => ["'none'"],
            'base-uri'        => ["'self'"],
            'form-action'     => ["'self'"],
            'object-src'      => ["'none'"],
            'media-src'       => ["'self'"],
            'worker-src'      => ["'self'", 'blob:'],
            'manifest-src'    => ["'self'"],
            'upgrade-insecure-requests' => [],   // empty value = directive with no value
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Strict Transport Security (HSTS)
    |--------------------------------------------------------------------------
    */
    'hsts' => [
        'enabled'            => env('HSTS_ENABLED', true),
        'max_age'            => 31536000,      // 1 year in seconds
        'include_subdomains' => true,
        'preload'            => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions Policy
    |--------------------------------------------------------------------------
    |
    | Controls browser feature access. Only enable what the app actually uses.
    |
    */
    'permissions_policy' => [
        'accelerometer'       => '()',
        'autoplay'            => '()',
        'camera'              => '()',
        'cross-origin-isolated' => '()',
        'display-capture'     => '()',
        'encrypted-media'     => '()',
        'fullscreen'          => '(self)',
        'geolocation'         => '()',
        'gyroscope'           => '()',
        'keyboard-map'        => '()',
        'magnetometer'        => '()',
        'microphone'          => '()',
        'midi'                => '()',
        'payment'             => '(self)',     // Required for Stripe
        'picture-in-picture'  => '()',
        'screen-wake-lock'    => '()',
        'sync-xhr'            => '()',
        'usb'                 => '()',
        'web-share'           => '(self)',
        'xr-spatial-tracking' => '()',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limits by Plan Tier
    |--------------------------------------------------------------------------
    |
    | TenantAwareThrottle middleware reads these to scale limits based on
    | the authenticated user's subscription plan.
    |
    */
    'rate_limits' => [
        // API requests per minute per user
        'api' => [
            'free'       => 30,
            'starter'    => 60,
            'pro'        => 120,
            'enterprise' => 300,
            'default'    => 60,
        ],
        // Auth attempts per minute per IP
        'auth' => [
            'per_minute'         => env('RATE_LIMIT_AUTH', 10),
            'decay_minutes'      => 1,
            'lockout_minutes'    => 15,
            'failed_before_lock' => 5,
        ],
        // Public booking widget
        'booking' => [
            'per_minute'  => env('RATE_LIMIT_BOOKING', 30),
        ],
        // Data export endpoints (CSV, PDF)
        'exports' => [
            'free'       => 2,
            'starter'    => 5,
            'pro'        => 20,
            'enterprise' => 100,
            'decay_minutes' => 60,   // per hour, not per minute
        ],
        // Campaign sending
        'sending' => [
            'per_minute' => 5,
        ],
        // Super-admin actions
        'admin' => [
            'per_minute' => 60,
        ],
        // Webhook delivery (from Stripe — not throttled, verified by signature)
        'stripe' => [
            'per_minute' => 200,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Suspicious Activity Thresholds
    |--------------------------------------------------------------------------
    |
    | Triggers for raising severity on audit log events.
    |
    */
    'suspicious' => [
        'failed_logins_per_hour'    => 10,
        'policy_denials_per_hour'   => 20,
        'exports_per_hour'          => 30,
        'api_errors_per_minute'     => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Log Retention (days)
    |--------------------------------------------------------------------------
    */
    'retention' => [
        'security_audit'  => env('AUDIT_RETENTION_DAYS',    365),
        'activity_log'    => env('ACTIVITY_RETENTION_DAYS',  90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Events to Log
    |--------------------------------------------------------------------------
    |
    | Events written to audit_logs by AuditLogService.
    | Set to false to disable a category (not recommended in production).
    |
    */
    'audit' => [
        'auth'     => true,  // login, logout, failed, 2FA, password reset
        'access'   => true,  // policy denials, cross-tenant attempts
        'data'     => true,  // exports, bulk deletes, GDPR requests
        'billing'  => true,  // plan changes, cancellations
        'admin'    => true,  // impersonation, user management, tenant ops
        'security' => true,  // suspicious activity, lockouts, header violations
    ],

];
