<?php

use App\Http\Controllers\Billing\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|──────────────────────────────────────────────────────────────────────────────
| Stripe Webhook Routes
|──────────────────────────────────────────────────────────────────────────────
|
| These routes are loaded WITHOUT the 'web' middleware group so that:
|   1. CSRF verification is bypassed (Stripe POSTs from external servers).
|   2. Session cookies are not required or started.
|
| Security is provided by Stripe's HMAC-SHA256 webhook signature, verified
| inside WebhookController before any processing begins.
|
| To register with Stripe:
|   Dashboard → Developers → Webhooks → Add endpoint
|   URL: https://velour.app/stripe/webhook
|
| For local development use the Stripe CLI:
|   stripe listen --forward-to localhost:8000/stripe/webhook
|
*/

Route::post('stripe/webhook', [WebhookController::class, 'handle'])
    ->name('stripe.webhook');
