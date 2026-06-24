<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\CashfreeService;
use App\Services\Billing\SubscriptionBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Cashfree subscription & payment webhooks.
 *
 * Route: POST /cashfree/webhook
 */
class CashfreeWebhookController extends Controller
{
    public function __construct(
        protected CashfreeService $cashfree,
        protected SubscriptionBillingService $billing,
    ) {}

    public function handle(Request $request)
    {
        $rawBody   = $request->getContent();
        $signature = (string) $request->header('x-webhook-signature', '');
        $timestamp = (string) $request->header('x-webhook-timestamp', '');

        if (! $this->cashfree->verifyWebhook($signature, $timestamp, $rawBody)) {
            Log::warning('[Cashfree Webhook] Invalid signature');

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = json_decode($rawBody, true);
        if (! is_array($payload)) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $eventType = (string) ($payload['type'] ?? $payload['event'] ?? 'unknown');
        $eventId   = (string) ($payload['event_id'] ?? $payload['cf_event_id'] ?? md5($rawBody));

        $existing = DB::table('webhook_calls')->where('stripe_event_id', $eventId)->first();
        if ($existing) {
            return response()->json(['message' => 'Already processed']);
        }

        $callId = DB::table('webhook_calls')->insertGetId([
            'stripe_event_id' => $eventId,
            'type'            => $eventType,
            'payload'         => $rawBody,
            'status'          => 'received',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        try {
            $this->processEvent($eventType, $payload);
            DB::table('webhook_calls')->where('id', $callId)->update([
                'status'     => 'processed',
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            DB::table('webhook_calls')->where('id', $callId)->update([
                'status'     => 'failed',
                'exception'  => $e->getMessage(),
                'updated_at' => now(),
            ]);
            Log::error('[Cashfree Webhook] Handler failed', [
                'type'  => $eventType,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['message' => 'ok']);
    }

    protected function processEvent(string $eventType, array $payload): void
    {
        $data = $payload['data'] ?? $payload;

        match (true) {
            str_contains($eventType, 'SUBSCRIPTION_STATUS_CHANGED'),
            str_contains($eventType, 'SUBSCRIPTION_AUTH_STATUS') => $this->billing->syncFromCashfreePayload(
                is_array($data['subscription'] ?? null) ? $data['subscription'] : $data
            ),
            default => Log::info('[Cashfree Webhook] Ignored event', ['type' => $eventType]),
        };
    }
}
