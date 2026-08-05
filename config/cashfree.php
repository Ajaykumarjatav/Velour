<?php

return [

    /*
    |──────────────────────────────────────────────────────────────────────────
    | Cashfree Payment Gateway (platform billing + optional salon payments)
    |──────────────────────────────────────────────────────────────────────────
    |
    | Dashboard: Payment Gateway → Developers → API Keys
    | Webhooks:  Payment Gateway → Developers → Webhooks
    |
    */

    // sandbox | production (aliases prod/live → production, test → sandbox)
    'environment' => env('CASHFREE_ENVIRONMENT', 'sandbox'),

    'client_id'     => env('CASHFREE_CLIENT_ID'),
    'client_secret' => env('CASHFREE_CLIENT_SECRET'),

    'api_version' => env('CASHFREE_API_VERSION', '2025-01-01'),

    'webhook_secret' => env('CASHFREE_WEBHOOK_SECRET'),

    'sdk_mode' => in_array(strtolower((string) env('CASHFREE_ENVIRONMENT', 'sandbox')), ['production', 'prod', 'live'], true)
        ? 'production'
        : 'sandbox',

];
