<?php

/*
|──────────────────────────────────────────────────────────────────────────────
| VELOUR — Laravel Cashier (Stripe) Configuration
|──────────────────────────────────────────────────────────────────────────────
|
| This file configures the Laravel Cashier integration.
|
| Cashier uses the `users` table as the billing model (salon owners pay).
| The `stripe_id`, `pm_type`, `pm_last_four`, and `trial_ends_at` columns
| are added via the cashier billing migration.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Cashier Model
    |--------------------------------------------------------------------------
    |
    | The Eloquent model used for billing — must have the Billable trait.
    |
    */
    'model' => App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Stripe Keys
    |--------------------------------------------------------------------------
    */
    'key'    => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | The signing secret from the Stripe dashboard → Webhooks section.
    | Used to verify incoming webhook payloads.
    |
    */
    'webhook' => [
        'secret'    => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | GBP for the UK market. All plan prices in config/billing.php are in
    | pence when sent to Stripe (multiply by 100).
    |
    */
    'currency'        => env('CASHIER_CURRENCY', 'gbp'),
    'currency_locale' => env('CASHIER_CURRENCY_LOCALE', 'en_GB'),

    /*
    |--------------------------------------------------------------------------
    | Payment Confirmation Behaviour
    |--------------------------------------------------------------------------
    |
    | 'automatic' — Cashier redirects to a confirmation page for 3DS cards.
    | Set to the route name that renders the Stripe.js payment confirmation UI.
    |
    */
    'payment_action' => env('CASHIER_PAYMENT_ACTION', 'automatic'),

    /*
    |--------------------------------------------------------------------------
    | Logger
    |--------------------------------------------------------------------------
    */
    'logger' => env('CASHIER_LOGGER', null),

    /*
    |--------------------------------------------------------------------------
    | Invoice Paper
    |--------------------------------------------------------------------------
    */
    'paper' => env('CASHIER_PAPER', 'a4'),

];
