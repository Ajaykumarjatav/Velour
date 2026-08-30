<?php

use App\Http\Controllers\Billing\CashfreeWebhookController;
use Illuminate\Support\Facades\Route;

/*
|──────────────────────────────────────────────────────────────────────────────
| Cashfree Webhook Routes (no CSRF / session)
|──────────────────────────────────────────────────────────────────────────────
|
| Dashboard → Payment Gateway → Developers → Webhooks
| URL: https://your-domain.com/cashfree/webhook
|
*/

Route::post('cashfree/webhook', [CashfreeWebhookController::class, 'handle'])
    ->name('cashfree.webhook');
