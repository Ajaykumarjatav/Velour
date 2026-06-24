<?php

namespace App\Services\Billing;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;

class SubscriptionBillingService
{
    public function __construct(protected CashfreeService $cashfree) {}

    public function findByMerchantSubscriptionId(string $subscriptionId): ?Subscription
    {
        return Subscription::query()->where('stripe_id', $subscriptionId)->first();
    }

    public function upsertPending(User $user, string $subscriptionId, string $planKey, string $interval): Subscription
    {
        return Subscription::query()->updateOrCreate(
            ['user_id' => $user->id, 'type' => 'default'],
            [
                'stripe_id'     => $subscriptionId,
                'stripe_status' => 'incomplete',
                'stripe_price'  => "{$planKey}:{$interval}",
                'quantity'      => 1,
            ]
        );
    }

    public function syncFromCashfreePayload(array $data): void
    {
        $merchantId = (string) ($data['subscription_id'] ?? $data['subscriptionId'] ?? '');
        $status     = (string) ($data['subscription_status'] ?? $data['status'] ?? '');

        if ($merchantId === '') {
            return;
        }

        $sub = $this->findByMerchantSubscriptionId($merchantId);
        if (! $sub) {
            return;
        }

        $mapped = CashfreeService::mapSubscriptionStatus($status);
        $sub->update(['stripe_status' => $mapped]);

        if ($mapped === 'active') {
            [$planKey] = explode(':', (string) $sub->stripe_price, 2);
            if ($planKey !== '') {
                $sub->user?->update([
                    'plan'          => $planKey,
                    'trial_ends_at' => null,
                ]);
            }
        }

        if ($mapped === 'canceled') {
            $sub->update(['ends_at' => now()]);
            $sub->user?->update([
                'plan'          => config('billing.default_plan', 'trial'),
                'trial_ends_at' => null,
            ]);
        }
    }

    public function syncFromApi(string $subscriptionId): void
    {
        try {
            $data = $this->cashfree->fetchSubscription($subscriptionId);
            $this->syncFromCashfreePayload($data);
        } catch (\Throwable $e) {
            Log::warning('[Billing] Cashfree subscription sync failed', [
                'subscription_id' => $subscriptionId,
                'error'           => $e->getMessage(),
            ]);
        }
    }

    public function cancel(User $user, bool $immediately = false): void
    {
        $sub = $user->subscription('default');
        if (! $sub?->stripe_id) {
            return;
        }

        try {
            $this->cashfree->cancelSubscription($sub->stripe_id);
        } catch (\Throwable $e) {
            Log::error('[Billing] Cashfree cancel failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
            throw $e;
        }

        if ($immediately) {
            $sub->update([
                'stripe_status' => 'canceled',
                'ends_at'       => now(),
            ]);
            $user->update(['plan' => config('billing.default_plan', 'trial')]);
        } else {
            $sub->update([
                'stripe_status' => 'canceled',
                'ends_at'       => $sub->ends_at ?? now()->endOfMonth(),
            ]);
        }
    }

    public function changePlan(User $user, string $planKey, string $interval): void
    {
        $sub = $user->subscription('default');
        if (! $sub?->stripe_id) {
            throw new \RuntimeException('No active subscription to change.');
        }

        $plan = \App\Billing\Plan::findOrFail($planKey);
        $this->cashfree->changePlan($sub->stripe_id, $plan->cashfreePlanId($interval));

        $sub->update([
            'stripe_price'  => "{$planKey}:{$interval}",
            'stripe_status' => 'active',
        ]);
        $user->update(['plan' => $planKey]);
    }
}
