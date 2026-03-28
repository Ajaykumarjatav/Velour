<?php

/*
|──────────────────────────────────────────────────────────────────────────────
| VELOUR — Billing & Subscription Configuration
|──────────────────────────────────────────────────────────────────────────────
|
| This file is the single source of truth for:
|   • Plan definitions (name, features, limits)
|   • Monthly and yearly Stripe Price IDs
|   • Trial period settings
|   • Grace period for failed payments
|
| Stripe Price IDs MUST be set via environment variables — never hard-code
| live price IDs. Each price ID corresponds to a recurring price object in
| your Stripe dashboard.
|
| Naming convention for env vars:
|   STRIPE_PRICE_{PLAN}_{INTERVAL}
|   e.g.  STRIPE_PRICE_PRO_MONTHLY=price_1OaBC...
|         STRIPE_PRICE_PRO_YEARLY=price_1OaDE...
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Trial Period
    |--------------------------------------------------------------------------
    |
    | Number of trial days granted on every new subscription.
    | Set to 0 to disable trials entirely.
    |
    */

    'trial_days' => (int) env('BILLING_TRIAL_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Grace Period (days after payment failure before restricting access)
    |--------------------------------------------------------------------------
    */

    'grace_period_days' => (int) env('BILLING_GRACE_PERIOD_DAYS', 3),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */

    'currency'        => env('BILLING_CURRENCY', 'gbp'),
    'currency_symbol' => env('BILLING_CURRENCY_SYMBOL', '£'),

    /*
    |--------------------------------------------------------------------------
    | Yearly Discount (%)
    |--------------------------------------------------------------------------
    |
    | Informational only — the actual discount is baked into the Stripe yearly
    | price.  Used by the pricing UI to display the saving.
    |
    */

    'yearly_discount_percent' => 20,

    /*
    |--------------------------------------------------------------------------
    | Plans
    |--------------------------------------------------------------------------
    |
    | Each plan entry defines:
    |   stripe_monthly  — Stripe Price ID for the monthly billing cycle
    |   stripe_yearly   — Stripe Price ID for the yearly billing cycle
    |   trial_days      — Override the global trial period per-plan (optional)
    |   features        — Boolean feature flags checked by CheckSubscription
    |   limits          — Numeric resource caps enforced by CheckPlanLimits
    |
    */

    'plans' => [

        // ── Free ─────────────────────────────────────────────────────────────
        //
        // No Stripe subscription required. Enforced by the absence of an
        // active subscription record rather than a Stripe price.
        //
        'free' => [
            'name'           => 'Free',
            'tagline'        => 'Get started — no card required',
            'stripe_monthly' => null,
            'stripe_yearly'  => null,
            'price_monthly'  => 0,
            'price_yearly'   => 0,
            'trial_days'     => 0,
            'popular'        => false,
            'color'          => 'gray',
            'features' => [
                'online_booking'      => true,
                'marketing'           => false,
                'reports'             => false,
                'api_access'          => false,
                'custom_domain'       => false,
                'priority_support'    => false,
                'white_label'         => false,
                'multi_location'      => false,
                'remove_branding'     => false,
            ],
            'limits' => [
                'staff'               => 1,
                'clients'             => 50,
                'services'            => 5,
                'appointments_month'  => 50,
                'storage_gb'          => 0.5,
            ],
        ],

        // ── Starter ──────────────────────────────────────────────────────────

        'starter' => [
            'name'           => 'Starter',
            'tagline'        => 'For solo stylists getting started',
            'stripe_monthly' => env('STRIPE_PRICE_STARTER_MONTHLY'),
            'stripe_yearly'  => env('STRIPE_PRICE_STARTER_YEARLY'),
            'price_monthly'  => 19,
            'price_yearly'   => 182,   // 19 * 12 * 0.80 = £182.40 → rounded
            'trial_days'     => (int) env('BILLING_TRIAL_DAYS', 14),
            'popular'        => false,
            'color'          => 'blue',
            'features' => [
                'online_booking'      => true,
                'marketing'           => false,
                'reports'             => false,
                'api_access'          => false,
                'custom_domain'       => false,
                'priority_support'    => false,
                'white_label'         => false,
                'multi_location'      => false,
                'remove_branding'     => false,
            ],
            'limits' => [
                'staff'               => 2,
                'clients'             => 200,
                'services'            => 20,
                'appointments_month'  => 200,
                'storage_gb'          => 2,
            ],
        ],

        // ── Pro ──────────────────────────────────────────────────────────────

        'pro' => [
            'name'           => 'Pro',
            'tagline'        => 'For growing salons with a full team',
            'stripe_monthly' => env('STRIPE_PRICE_PRO_MONTHLY'),
            'stripe_yearly'  => env('STRIPE_PRICE_PRO_YEARLY'),
            'price_monthly'  => 49,
            'price_yearly'   => 470,   // 49 * 12 * 0.80
            'trial_days'     => (int) env('BILLING_TRIAL_DAYS', 14),
            'popular'        => true,
            'color'          => 'velour',
            'features' => [
                'online_booking'      => true,
                'marketing'           => true,
                'reports'             => true,
                'api_access'          => true,
                'custom_domain'       => false,
                'priority_support'    => false,
                'white_label'         => false,
                'multi_location'      => false,
                'remove_branding'     => true,
            ],
            'limits' => [
                'staff'               => 15,
                'clients'             => 5000,
                'services'            => 100,
                'appointments_month'  => -1,  // unlimited
                'storage_gb'          => 20,
            ],
        ],

        // ── Enterprise ───────────────────────────────────────────────────────

        'enterprise' => [
            'name'           => 'Enterprise',
            'tagline'        => 'For multi-location salon groups',
            'stripe_monthly' => env('STRIPE_PRICE_ENTERPRISE_MONTHLY'),
            'stripe_yearly'  => env('STRIPE_PRICE_ENTERPRISE_YEARLY'),
            'price_monthly'  => 149,
            'price_yearly'   => 1430,  // 149 * 12 * 0.80
            'trial_days'     => 30,
            'popular'        => false,
            'color'          => 'amber',
            'features' => [
                'online_booking'      => true,
                'marketing'           => true,
                'reports'             => true,
                'api_access'          => true,
                'custom_domain'       => true,
                'priority_support'    => true,
                'white_label'         => true,
                'multi_location'      => true,
                'remove_branding'     => true,
            ],
            'limits' => [
                'staff'               => -1,  // unlimited
                'clients'             => -1,
                'services'            => -1,
                'appointments_month'  => -1,
                'storage_gb'          => 200,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhook Events
    |--------------------------------------------------------------------------
    |
    | The subset of Stripe webhook events Velour handles.
    | All others are stored in webhook_calls with status=ignored.
    |
    */

    'handled_webhook_events' => [
        'customer.subscription.created',
        'customer.subscription.updated',
        'customer.subscription.deleted',
        'customer.subscription.trial_will_end',
        'invoice.payment_succeeded',
        'invoice.payment_failed',
        'invoice.finalized',
        'payment_method.attached',
    ],

];
