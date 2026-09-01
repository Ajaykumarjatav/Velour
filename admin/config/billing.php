<?php

/*
|──────────────────────────────────────────────────────────────────────────────
| EasyGrox — Billing & Subscription Configuration
|──────────────────────────────────────────────────────────────────────────────
|
| Three plans:
|   trial    — 15-day trial, 3 stores, all features (default on registration)
|   standard — ₹500/mo, 5 stores, all features
|   premium  — ₹1000/mo, 10 stores, all features
|
*/

$allFeatures = [
    'online_booking'      => true,
    'marketing'           => true,
    'reports'             => true,
    'inventory'           => true,
    'api_access'          => true,
    'custom_domain'       => true,
    'priority_support'    => true,
    'white_label'         => true,
    'multi_location'      => true,
    'remove_branding'     => true,
];

$unlimitedLimits = [
    'staff'              => -1,
    'clients'            => -1,
    'services'           => -1,
    'appointments_month' => -1,
    'storage_gb'         => -1,
];

return [

    'subscriptions_enabled' => filter_var(env('SUBSCRIPTIONS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'trial_days' => (int) env('BILLING_TRIAL_DAYS', 15),

    'grace_period_days' => (int) env('BILLING_GRACE_PERIOD_DAYS', 3),

    'currency'        => env('BILLING_CURRENCY', 'inr'),
    'currency_symbol' => env('BILLING_CURRENCY_SYMBOL', '₹'),

    'yearly_discount_percent' => 20,

    'default_plan' => 'trial',

    'plans' => [

        'trial' => [
            'name'           => '15-Day Trial',
            'tagline'        => 'Full access for 15 days — up to 3 stores',
            'stripe_monthly' => null,
            'stripe_yearly'  => null,
            'price_monthly'  => 0,
            'price_yearly'   => 0,
            'trial_days'     => (int) env('BILLING_TRIAL_DAYS', 15),
            'popular'        => false,
            'color'          => 'blue',
            'features'       => $allFeatures,
            'limits'         => array_merge($unlimitedLimits, ['stores' => 3]),
        ],

        'standard' => [
            'name'           => 'Standard',
            'tagline'        => '₹500/month — up to 5 stores, all features',
            'cashfree_monthly' => env('CASHFREE_PLAN_STANDARD_MONTHLY', 'velor_standard_monthly'),
            'cashfree_yearly'  => env('CASHFREE_PLAN_STANDARD_YEARLY', 'velor_standard_yearly'),
            'stripe_monthly' => null,
            'stripe_yearly'  => null,
            'price_monthly'  => 500,
            'price_yearly'   => 4800,
            'trial_days'     => 0,
            'popular'        => true,
            'color'          => 'velour',
            'features'       => $allFeatures,
            'limits'         => array_merge($unlimitedLimits, ['stores' => 5]),
        ],

        'premium' => [
            'name'           => 'Premium',
            'tagline'        => '₹1000/month — up to 10 stores, all features',
            'cashfree_monthly' => env('CASHFREE_PLAN_PREMIUM_MONTHLY', 'velor_premium_monthly'),
            'cashfree_yearly'  => env('CASHFREE_PLAN_PREMIUM_YEARLY', 'velor_premium_yearly'),
            'stripe_monthly' => null,
            'stripe_yearly'  => null,
            'price_monthly'  => 1000,
            'price_yearly'   => 9600,
            'trial_days'     => 0,
            'popular'        => false,
            'color'          => 'amber',
            'features'       => $allFeatures,
            'limits'         => array_merge($unlimitedLimits, ['stores' => 10]),
        ],

    ],

    'handled_webhook_events' => [
        'SUBSCRIPTION_STATUS_CHANGED',
        'SUBSCRIPTION_AUTH_STATUS',
        'SUBSCRIPTION_PAYMENT_SUCCESS',
        'SUBSCRIPTION_PAYMENT_FAILED',
        'SUBSCRIPTION_PAYMENT_CANCELLED',
    ],

];
