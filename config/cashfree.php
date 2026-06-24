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

    'environment' => env('CASHFREE_ENVIRONMENT', 'sandbox'), // sandbox | production

    'client_id'     => env('CASHFREE_CLIENT_ID'),
    'client_secret' => env('CASHFREE_CLIENT_SECRET'),

    'api_version' => env('CASHFREE_API_VERSION', '2025-01-01'),

    'webhook_secret' => env('CASHFREE_WEBHOOK_SECRET'),

    'sdk_mode' => env('CASHFREE_ENVIRONMENT', 'sandbox') === 'production' ? 'production' : 'sandbox',

];
