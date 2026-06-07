<?php

namespace App\Http\Controllers\Billing;

use App\Billing\Plan;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SubscriptionCancelledNotification;
use App\Notifications\SubscriptionCreatedNotification;
use App\Notifications\TrialEndingNotification;
use App\Notifications\PaymentFailedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\StripeClient;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;

/**
 * WebhookController
 *
 * Receives and processes Stripe webhook events.
 *
 * Security:
 *  • HMAC-SHA256 signature verification via STRIPE_WEBHOOK_SECRET
 *  • Idempotency via webhook_calls.stripe_event_id unique constraint
 *  • No auth / CSRF (Stripe hits this endpoint externally)
 *
 * All DB mutations are wrapped in transactions.
 * Failed handlers return HTTP 200 to prevent Stripe retry storms for
 * app-logic errors (5xx reserved for genuine infrastructure failures).
 *
 * Route: POST /stripe/webhook
 */
class WebhookController extends Controller
{
    // ── Entry point ───────────────────────────────────────────────────────────

    public function handle(Request $request): Response
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');
        $secret    = config('cashier.webhook.secret');
        $tolerance = (int) config('cashier.webhook.tolerance', 300);

        // 1. Verify Stripe signature
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret, $tolerance);
        } catch (SignatureVerificationException $e) {
            Log::warning('[Webhook] Invalid Stripe signature', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature.'], 400);
        } catch (\UnexpectedValueException $e) {
            Log::warning('[Webhook] Malformed payload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload.'], 400);
        }

        // 2. Record the event (idempotency check via unique stripe_event_id)
        $existing = DB::table('webhook_calls')->where('stripe_event_id', $event->id)->first();
        if ($existing && $existing->status === 'processed') {
            return response()->json(['status' => 'duplicate_skipped']);
        }

        $handledTypes = config('billing.handled_webhook_events', []);
        $isHandled    = in_array($event->type, $handledTypes, true);
        $status       = $isHandled ? 'received' : 'ignored';

        $callId = DB::table('webhook_calls')->insertGetId([
            'stripe_event_id' => $event->id,
            'type'            => $event->type,
            'payload'         => json_encode($event->toArray()),
            'status'          => $status,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        if (! $isHandled) {
            return response()->json(['status' => 'ignored']);
        }

        // 3. Process
        try {
            DB::transaction(function () use ($event) {
                $this->dispatch($event);
            });

            DB::table('webhook_calls')->where('id', $callId)
              ->update(['status' => 'processed', 'updated_at' => now()]);

        } catch (\Throwable $e) {
            DB::table('webhook_calls')->where('id', $callId)->update([
                'status'     => 'failed',
                'exception'  => substr($e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(), 0, 1000),
                'updated_at' => now(),
            ]);

            Log::error('[Webhook] Handler failed', [
                'event' => $event->type,
                'id'    => $event->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    // ── Event dispatcher ──────────────────────────────────────────────────────

    private function dispatch(Event $event): void
    {
        match ($event->type) {
            'customer.subscription.created'        => $this->onSubscriptionCreated($event),
            'customer.subscription.updated'        => $this->onSubscriptionUpdated($event),
            'customer.subscription.deleted'        => $this->onSubscriptionDeleted($event),
            'customer.subscription.trial_will_end' => $this->onTrialWillEnd($event),
            'invoice.payment_succeeded'            => $this->onPaymentSucceeded($event),
            'invoice.payment_failed'               => $this->onPaymentFailed($event),
            'invoice.finalized'                    => $this->onInvoiceFinalized($event),
            'payment_method.attached'              => $this->onPaymentMethodAttached($event),
            default => null,
        };
    }

    // ── Subscription.created ──────────────────────────────────────────────────

    private function onSubscriptionCreated(Event $event): void
    {
        $s    = $event->data->object;
        $user = $this->userByCustomer($s->customer);
        if (! $user) return;

        $planKey = $this->planKeyFromSub($s);
        $user->update([
            'plan'          => $planKey,
            'trial_ends_at' => $s->trial_end ? Carbon::createFromTimestamp($s->trial_end) : null,
        ]);

        Log::info('[Webhook] Subscription created', ['user' => $user->id, 'plan' => $planKey]);
        $user->notify(new SubscriptionCreatedNotification($planKey, (bool) $s->trial_end));
    }

    // ── Subscription.updated ─────────────────────────────────────────────────

    private function onSubscriptionUpdated(Event $event): void
    {
        $s    = $event->data->object;
        $user = $this->userByCustomer($s->customer);
        if (! $user) return;

        $planKey = $this->planKeyFromSub($s);
        $updates = [
            'plan'          => $planKey,
            'trial_ends_at' => $s->trial_end ? Carbon::createFromTimestamp($s->trial_end) : null,
        ];

        $user->update($updates);

        Log::info('[Webhook] Subscription updated', [
            'user'   => $user->id,
            'plan'   => $planKey,
            'status' => $s->status,
        ]);
    }

    // ── Subscription.deleted ─────────────────────────────────────────────────

    private function onSubscriptionDeleted(Event $event): void
    {
        $s    = $event->data->object;
        $user = $this->userByCustomer($s->customer);
        if (! $user) return;

        $user->update(['plan' => 'free', 'trial_ends_at' => null]);

        Log::info('[Webhook] Subscription deleted → free', ['user' => $user->id]);
        $user->notify(new SubscriptionCancelledNotification());
    }

    // ── Trial will end (3-day warning) ────────────────────────────────────────

    private function onTrialWillEnd(Event $event): void
    {
        $s    = $event->data->object;
        $user = $this->userByCustomer($s->customer);
        if (! $user) return;

        $trialEnd = Carbon::createFromTimestamp($s->trial_end);
        Log::info('[Webhook] Trial ending soon', ['user' => $user->id, 'ends' => $trialEnd]);
        $user->notify(new TrialEndingNotification($trialEnd));
    }

    // ── Invoice.payment_succeeded ─────────────────────────────────────────────

    private function onPaymentSucceeded(Event $event): void
    {
        $inv  = $event->data->object;
        $user = $this->userByCustomer($inv->customer);
        if (! $user) return;

        // Sync plan from subscription invoice line
        if ($inv->subscription) {
            $planKey = $this->planKeyFromInvoice($inv);
            if ($planKey) $user->update(['plan' => $planKey]);
        }

        Log::info('[Webhook] Payment succeeded', [
            'user'    => $user->id,
            'invoice' => $inv->id,
            'amount'  => $inv->amount_paid / 100,
        ]);
    }

    // ── Invoice.payment_failed ────────────────────────────────────────────────

    private function onPaymentFailed(Event $event): void
    {
        $inv  = $event->data->object;
        $user = $this->userByCustomer($inv->customer);
        if (! $user) return;

        $nextAttempt = $inv->next_payment_attempt
            ? Carbon::createFromTimestamp($inv->next_payment_attempt)
            : null;

        Log::warning('[Webhook] Payment failed', [
            'user'    => $user->id,
            'invoice' => $inv->id,
            'attempt' => $inv->attempt_count,
        ]);

        $user->notify(new PaymentFailedNotification(
            amount: $inv->amount_due / 100,
            nextAttempt: $nextAttempt
        ));
    }

    // ── Invoice.finalized ─────────────────────────────────────────────────────

    private function onInvoiceFinalized(Event $event): void
    {
        $inv = $event->data->object;
        Log::info('[Webhook] Invoice finalized', ['invoice' => $inv->id]);
        // Cashier streams invoices live from Stripe; no local storage needed.
    }

    // ── Payment method attached ───────────────────────────────────────────────

    private function onPaymentMethodAttached(Event $event): void
    {
        $pm   = $event->data->object;
        $user = $this->userByCustomer($pm->customer);
        if (! $user) return;

        $updates = match ($pm->type) {
            'card'       => ['pm_type' => 'card',       'pm_last_four' => $pm->card->last4 ?? null],
            'sepa_debit' => ['pm_type' => 'sepa_debit', 'pm_last_four' => $pm->sepa_debit->last4 ?? null],
            'bacs_debit' => ['pm_type' => 'bacs_debit', 'pm_last_four' => $pm->bacs_debit->last4 ?? null],
            default      => [],
        };

        if ($updates) $user->update($updates);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function userByCustomer(string $customerId): ?User
    {
        return User::where('stripe_id', $customerId)->first();
    }

    /**
     * Map a Stripe subscription object → Velour plan key.
     * Priority: subscription metadata → price ID lookup → fallback.
     */
    private function planKeyFromSub(object $sub): string
    {
        if (! empty($sub->metadata->plan)) {
            return $sub->metadata->plan;
        }

        $priceId = $sub->items->data[0]->price->id ?? null;
        return $priceId ? $this->priceIdToPlanKey($priceId) : 'starter';
    }

    private function planKeyFromInvoice(object $inv): ?string
    {
        $priceId = $inv->lines->data[0]->price->id ?? null;
        return $priceId ? $this->priceIdToPlanKey($priceId) : null;
    }

    private function priceIdToPlanKey(string $priceId): string
    {
        foreach (config('billing.plans', []) as $key => $cfg) {
            if (in_array($priceId, array_filter([$cfg['stripe_monthly'] ?? null, $cfg['stripe_yearly'] ?? null]), true)) {
                return $key;
            }
        }
        return 'starter';
    }
}
