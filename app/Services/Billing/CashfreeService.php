<?php

namespace App\Services\Billing;

use App\Billing\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CashfreeService
{
    public function __construct(
        private ?string $clientId = null,
        private ?string $clientSecret = null,
        private ?string $apiVersion = null,
        private ?string $environment = null,
    ) {
        $this->clientId     = $this->clientId ?? (string) config('cashfree.client_id');
        $this->clientSecret = $this->clientSecret ?? (string) config('cashfree.client_secret');
        $this->apiVersion   = $this->apiVersion ?? (string) config('cashfree.api_version', '2025-01-01');
        $this->environment  = $this->environment ?? (string) config('cashfree.environment', 'sandbox');
    }

    public static function forSalonGateway(string $clientId, string $clientSecret): self
    {
        return new static($clientId, $clientSecret);
    }

    public function isConfigured(): bool
    {
        return filled($this->clientId) && filled($this->clientSecret);
    }

    public function baseUrl(): string
    {
        return $this->environment === 'production'
            ? 'https://api.cashfree.com/pg'
            : 'https://sandbox.cashfree.com/pg';
    }

    public function sdkMode(): string
    {
        return $this->environment === 'production' ? 'production' : 'sandbox';
    }

    /** Map Cashfree subscription status → local stripe_status (Cashier-compatible). */
    public static function mapSubscriptionStatus(string $status): string
    {
        return match (strtoupper($status)) {
            'ACTIVE' => 'active',
            'INITIALIZED', 'BANK_APPROVAL_PENDING' => 'incomplete',
            'ON_HOLD' => 'past_due',
            'CUSTOMER_PAUSED' => 'paused',
            'CUSTOMER_CANCELLED', 'CANCELLED', 'COMPLETED', 'EXPIRED', 'LINK_EXPIRED', 'CARD_EXPIRED' => 'canceled',
            default => 'incomplete',
        };
    }

    public function planDetailsFor(Plan $plan, string $interval): array
    {
        $amount    = $interval === 'yearly' ? $plan->priceYearly : $plan->priceMonthly;
        $intervals = $interval === 'yearly' ? 12 : 1;

        return [
            'plan_id'             => $this->sanitizePlanId($plan->cashfreePlanId($interval)),
            'plan_name'           => $this->sanitizePlanName($plan->name.' '.ucfirst($interval)),
            'plan_type'           => 'PERIODIC',
            'plan_amount'         => $amount,
            'plan_max_amount'     => $amount,
            'plan_currency'       => strtoupper((string) config('billing.currency', 'inr')),
            'plan_interval_type'  => 'MONTH',
            'plan_intervals'      => $intervals,
            'plan_note'           => $this->sanitizePlanNote($this->planNoteFor($plan, $interval)),
        ];
    }

    protected function planNoteFor(Plan $plan, string $interval): string
    {
        $amount = $interval === 'yearly' ? $plan->priceYearly : $plan->priceMonthly;
        $stores = $plan->limit('stores');

        return "INR {$amount} {$interval} up to {$stores} stores all features included";
    }

    /**
     * Cashfree allows only alphanumerics plus . - _ and space in plan_id.
     */
    protected function sanitizePlanId(string $value): string
    {
        $value = preg_replace('/[^a-zA-Z0-9._-]/', '_', $value) ?? '';

        return mb_substr(trim($value, '_'), 0, 40) ?: 'velor_plan';
    }

    /** Cashfree plan_name: max 40 chars, limited charset. */
    protected function sanitizePlanName(string $value): string
    {
        $value = $this->normalizeCashfreeText($value);

        return mb_substr($value, 0, 40) ?: 'Plan';
    }

    /** Cashfree plan_note / description: limited charset, no currency symbols. */
    protected function sanitizePlanNote(string $value): string
    {
        $value = $this->normalizeCashfreeText($value);

        return mb_substr($value, 0, 255) ?: 'Subscription plan';
    }

    protected function sanitizeCustomerName(string $value): string
    {
        $value = $this->normalizeCashfreeText($value);

        return mb_substr($value, 0, 100) ?: 'Customer';
    }

  /**
     * Strip characters Cashfree rejects in text fields (e.g. ₹, em-dash, slashes).
     */
    protected function normalizeCashfreeText(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = str_replace(
            ['₹', '—', '–', '/', '\\', '@', '#', '%', '&', '*', '(', ')', '[', ']', '{', '}', ':', ';', '"', "'", '!', '?', '+', '=', '|', '<', '>'],
            ['INR ', '-', '-', ' ', ' ', ' ', ' ', ' ', ' and ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
            $value
        );
        $value = preg_replace('/[^a-zA-Z0-9 .,_-]/', ' ', $value) ?? '';
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return trim($value);
    }

    /**
     * Ensure a Cashfree subscription plan exists before checkout or plan changes.
     * Plans are created via API using IDs from config/billing.php (or defaults).
     */
    public function ensurePlanExists(Plan $plan, string $interval): string
    {
        $planId = $plan->cashfreePlanId($interval);
        $amount = $interval === 'yearly' ? $plan->priceYearly : $plan->priceMonthly;
        $intervals = $interval === 'yearly' ? 12 : 1;

        $url = $this->baseUrl().'/plans';
        $response = Http::withHeaders([
            'accept'          => 'application/json',
            'content-type'    => 'application/json',
            'x-api-version'   => $this->apiVersion,
            'x-client-id'     => $this->clientId,
            'x-client-secret' => $this->clientSecret,
        ])->timeout(30)->post($url, [
            'plan_id'               => $this->sanitizePlanId($planId),
            'plan_name'             => $this->sanitizePlanName($plan->name.' '.ucfirst($interval)),
            'plan_type'             => 'PERIODIC',
            'plan_currency'         => strtoupper((string) config('billing.currency', 'inr')),
            'plan_recurring_amount' => $amount,
            'plan_max_amount'       => $amount,
            'plan_intervals'        => $intervals,
            'plan_interval_type'    => 'MONTH',
            'plan_note'             => $this->sanitizePlanNote($this->planNoteFor($plan, $interval)),
        ]);

        if ($response->successful()) {
            return $planId;
        }

        $message = strtolower((string) ($response->json('message') ?? $response->json('error_description') ?? $response->body()));

        // Plan already exists — safe to continue.
        if ($response->status() === 409
            || str_contains($message, 'already')
            || (str_contains($message, 'exist') && ! str_contains($message, 'does not exist'))) {
            return $planId;
        }

        Log::error('[Cashfree] Plan create failed', [
            'plan_id' => $planId,
            'status'  => $response->status(),
            'body'    => $response->json() ?? $response->body(),
        ]);

        $error = $response->json('message') ?? $response->json('error_description') ?? 'Could not create subscription plan';

        throw new \RuntimeException(is_string($error) ? $error : json_encode($error));
    }

    public function createSubscription(User $user, Plan $plan, string $interval): array
    {
        $this->ensurePlanExists($plan, $interval);

        $subscriptionId = 'velor_'.$user->id.'_'.Str::lower(Str::random(10));
        $amount         = $interval === 'yearly' ? $plan->priceYearly : $plan->priceMonthly;
        $returnToken    = Str::random(64);

        $this->storeReturnToken($subscriptionId, $returnToken);

        $payload = [
            'subscription_id' => $subscriptionId,
            'customer_details' => [
                'customer_name'  => $this->sanitizeCustomerName((string) $user->name),
                'customer_email' => $user->email,
                'customer_phone' => $this->customerPhone($user),
            ],
            'plan_details' => $this->planDetailsFor($plan, $interval),
            'authorization_details' => [
                'authorization_amount'        => min(max($amount, 1), 1),
                'authorization_amount_refund' => true,
            ],
            'subscription_meta' => [
                'return_url' => $this->subscriptionReturnUrl($subscriptionId, $returnToken),
            ],
            'subscription_expiry_time' => now()->addYears(5)->toIso8601String(),
        ];

        return $this->request('post', '/subscriptions', $payload);
    }

    public function fetchSubscription(string $subscriptionId): array
    {
        return $this->request('get', '/subscriptions/'.urlencode($subscriptionId));
    }

    public function manageSubscription(string $subscriptionId, string $action, array $actionDetails = []): array
    {
        $body = array_filter([
            'subscription_id' => $subscriptionId,
            'action'          => $action,
            'action_details'  => $actionDetails ?: null,
        ]);

        return $this->request('post', '/subscriptions/'.urlencode($subscriptionId).'/manage', $body);
    }

    public function cancelSubscription(string $subscriptionId): array
    {
        return $this->manageSubscription($subscriptionId, 'CANCEL');
    }

    public function changePlan(string $subscriptionId, string $planId): array
    {
        return $this->manageSubscription($subscriptionId, 'CHANGE_PLAN', ['plan_id' => $planId]);
    }

    /** One-time payment order (salon POS / client charge). */
    public function createOrder(
        string $orderId,
        float $amount,
        string $currency,
        array $customerDetails,
        string $returnUrl,
    ): array {
        $payload = [
            'order_id'       => $orderId,
            'order_amount'   => round($amount, 2),
            'order_currency' => strtoupper($currency),
            'customer_details' => $customerDetails,
            'order_meta' => [
                'return_url' => $returnUrl,
            ],
        ];

        return $this->request('post', '/orders', $payload);
    }

    public function verifyWebhook(string $signature, string $timestamp, string $rawBody): bool
    {
        $secret = (string) config('cashfree.webhook_secret');

        if ($secret === '' || $signature === '' || $timestamp === '') {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $timestamp.$rawBody, $secret, true));

        return hash_equals($expected, $signature);
    }

    protected function customerPhone(User $user): string
    {
        $phone = preg_replace('/\D+/', '', (string) ($user->phone ?? ''));

        if (strlen($phone) === 10) {
            return $phone;
        }

        if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
            return substr($phone, 2);
        }

        return '9999999999';
    }

    protected function request(string $method, string $path, array $payload = []): array
    {
        $url = $this->baseUrl().$path;

        $pending = Http::withHeaders([
            'accept'          => 'application/json',
            'content-type'    => 'application/json',
            'x-api-version'   => $this->apiVersion,
            'x-client-id'     => $this->clientId,
            'x-client-secret' => $this->clientSecret,
        ])->timeout(30);

        $response = match (strtolower($method)) {
            'get'    => $pending->get($url, $payload),
            'post'   => $pending->post($url, $payload),
            'patch'  => $pending->patch($url, $payload),
            default  => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };

        if ($response->failed()) {
            Log::error('[Cashfree] API request failed', [
                'method' => $method,
                'path'   => $path,
                'status' => $response->status(),
                'body'   => $response->json() ?? $response->body(),
            ]);

            $message = $this->extractErrorMessage($response);

            throw new \RuntimeException($message);
        }

        return $response->json() ?? [];
    }

    protected function extractErrorMessage(\Illuminate\Http\Client\Response $response): string
    {
        $json = $response->json();

        if (is_array($json)) {
            if (isset($json['message']) && is_string($json['message']) && $json['message'] !== '') {
                return $json['message'];
            }

            if (isset($json['error_description']) && is_string($json['error_description'])) {
                return $json['error_description'];
            }

            // Cashfree often returns field-level errors as nested arrays.
            foreach ($json as $key => $value) {
                if (is_string($value) && $value !== '') {
                    return ucfirst((string) $key).': '.$value;
                }
                if (is_array($value)) {
                    $nested = $this->stringifyErrorValue($value);
                    if ($nested !== '') {
                        return ucfirst((string) $key).': '.$nested;
                    }
                }
            }
        }

        $body = trim((string) $response->body());

        return $body !== '' ? $body : 'Cashfree request failed';
    }

    protected function stringifyErrorValue(array $value): string
    {
        $parts = [];
        foreach ($value as $key => $item) {
            if (is_string($item) && $item !== '') {
                $parts[] = is_int($key) ? $item : "{$key}: {$item}";
            } elseif (is_array($item)) {
                $nested = $this->stringifyErrorValue($item);
                if ($nested !== '') {
                    $parts[] = $nested;
                }
            }
        }

        return implode('; ', $parts);
    }

    /**
     * Validate the browser return from Cashfree (token or legacy signed URL).
     */
    public function isValidReturnRequest(Request $request, string $subscriptionId): bool
    {
        if ($subscriptionId === '') {
            return false;
        }

        $returnToken = $request->input('return_token');
        if (is_string($returnToken) && $returnToken !== '' && $this->validateReturnToken($subscriptionId, $returnToken)) {
            return true;
        }

        if (! $request->has('signature')) {
            return false;
        }

        if ($request->hasValidRelativeSignature()) {
            return true;
        }

        return $this->hasValidAbsoluteReturnSignature($request);
    }

    protected function storeReturnToken(string $subscriptionId, string $token): void
    {
        Cache::put(
            "billing_return:{$subscriptionId}",
            hash('sha256', $token),
            now()->addDays(3),
        );
    }

    protected function validateReturnToken(string $subscriptionId, string $token): bool
    {
        $stored = Cache::get("billing_return:{$subscriptionId}");

        return is_string($stored) && hash_equals($stored, hash('sha256', $token));
    }

    /**
     * Cashfree return URL with a one-time token (no Laravel signed-route middleware).
     */
    protected function subscriptionReturnUrl(string $subscriptionId, string $returnToken): string
    {
        $query = http_build_query([
            'subscription_id' => $subscriptionId,
            'return_token'    => $returnToken,
        ]);

        return rtrim((string) config('app.url'), '/').'/billing/return?'.$query;
    }

    /**
     * Legacy absolute signed URLs generated before subdirectory-safe return handling.
     */
    protected function hasValidAbsoluteReturnSignature(Request $request): bool
    {
        $expires = $request->query('expires');
        $subscriptionId = $request->query('subscription_id');

        if ($expires === null || $subscriptionId === null) {
            return false;
        }

        if (! is_numeric($expires) || (int) $expires < now()->getTimestamp()) {
            return false;
        }

        $base = rtrim((string) config('app.url'), '/');
        $unsigned = $base.'/billing/return?'.http_build_query([
            'expires'         => $expires,
            'subscription_id' => $subscriptionId,
        ]);

        $key = config('app.key');
        $keys = is_array($key) ? $key : [$key];
        $provided = (string) $request->query('signature', '');

        foreach ($keys as $keyValue) {
            if (hash_equals(hash_hmac('sha256', $unsigned, $keyValue), $provided)) {
                return true;
            }
        }

        return false;
    }
}
