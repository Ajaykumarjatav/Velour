<?php

namespace App\Services\Billing;

use App\Billing\Plan;
use App\Models\BillingTransaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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
        $plan = Plan::findOrFail($planKey);
        $amount = $interval === 'yearly' ? $plan->priceYearly : $plan->priceMonthly;

        BillingTransaction::query()->create([
            'user_id'                  => $user->id,
            'cashfree_subscription_id' => $subscriptionId,
            'plan_key'                 => $planKey,
            'interval'                 => $interval,
            'amount'                   => $amount,
            'currency'                 => strtoupper((string) config('billing.currency', 'inr')),
            'status'                   => BillingTransaction::STATUS_PENDING,
            'activates_at'             => $this->resolveActivationDate($user),
            'meta'                     => ['source' => 'checkout'],
        ]);

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

    /**
     * Handle Cashfree browser return after checkout.
     *
     * @return array{
     *   outcome: 'success'|'failed'|'pending',
     *   message: string,
     *   user: ?User,
     *   plan: ?Plan,
     *   scheduled_plan: ?Plan,
     *   activates_at: ?Carbon,
     *   transaction: ?BillingTransaction
     * }
     */
    public function handlePaymentReturn(string $subscriptionId, array $returnPayload = []): array
    {
        $sub = $this->findByMerchantSubscriptionId($subscriptionId);
        if (! $sub) {
            return $this->returnOutcome('failed', 'Payment session not found. Please contact support if amount was deducted.', null);
        }

        $user = $sub->user;
        if (! $user) {
            return $this->returnOutcome('failed', 'Account not found for this payment.', null);
        }

        $cashfreeData = [];
        try {
            $cashfreeData = $this->cashfree->fetchSubscription($subscriptionId);
        } catch (\Throwable $e) {
            Log::warning('[Billing] Cashfree fetch on return failed', [
                'subscription_id' => $subscriptionId,
                'error'           => $e->getMessage(),
            ]);
        }

        if ($cashfreeData !== []) {
            $this->syncFromCashfreePayload($cashfreeData, $returnPayload);
            $sub->refresh();
            $user->refresh();
        }

        if ($this->paymentSucceeded($cashfreeData, $returnPayload, $sub)) {
            if ($sub->stripe_status !== 'active') {
                $sub->update(['stripe_status' => 'active']);
            }

            $this->activatePaidSubscription($sub, $cashfreeData, $returnPayload);
            $sub->refresh();
            $user->refresh();

            [$planKey] = explode(':', (string) $sub->stripe_price, 2);
            $plan = $planKey !== '' ? Plan::find($planKey) : null;

            $transaction = BillingTransaction::query()
                ->where('cashfree_subscription_id', $subscriptionId)
                ->latest('id')
                ->first();

            return $this->returnOutcome(
                'success',
                $this->successMessage($user, $plan),
                $user,
                $transaction,
            );
        }

        $status = strtoupper($this->resolvePaymentStatus($cashfreeData, $returnPayload, $sub));

        $transaction = BillingTransaction::query()
            ->where('cashfree_subscription_id', $subscriptionId)
            ->latest('id')
            ->first();

        if (in_array($status, ['ACTIVE', 'ACTIVATED', 'BANK_APPROVAL_PENDING'], true)) {
            [$planKey] = explode(':', (string) $sub->stripe_price, 2);
            $plan = $planKey !== '' ? Plan::find($planKey) : null;

            return $this->returnOutcome(
                'success',
                $this->successMessage($user, $plan),
                $user,
                $transaction,
            );
        }

        if (in_array($status, ['INITIALIZED', 'INCOMPLETE', 'INCOMPLETE_EXPIRED'], true)) {
            return $this->returnOutcome(
                'pending',
                'Your payment is being processed. We will update your plan once Cashfree confirms the mandate.',
                $user,
                $transaction,
            );
        }

        $reason = $this->humanizeFailureStatus($status, $returnPayload, $cashfreeData);

        if ($transaction && $transaction->status === BillingTransaction::STATUS_PENDING) {
            $transaction->update([
                'status'         => BillingTransaction::STATUS_FAILED,
                'failure_reason' => $reason,
            ]);
        }

        return $this->returnOutcome('failed', $reason, $user, $transaction);
    }

    public function syncFromCashfreePayload(array $data, array $returnPayload = []): void
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
            $this->activatePaidSubscription($sub, $data, $returnPayload);
        }

        if ($mapped === 'canceled') {
            $sub->update(['ends_at' => now()]);
            $sub->user?->update([
                'scheduled_plan'            => null,
                'scheduled_plan_interval'   => null,
                'scheduled_plan_starts_at' => null,
                'trial_ends_at'             => null,
            ]);

            if (! $sub->user?->scheduled_plan) {
                $sub->user?->update(['plan' => config('billing.default_plan', 'trial')]);
            }
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

    public function activateDueScheduledPlans(): int
    {
        $count = 0;

        User::query()
            ->whereNotNull('scheduled_plan')
            ->whereNotNull('scheduled_plan_starts_at')
            ->where('scheduled_plan_starts_at', '<=', now())
            ->each(function (User $user) use (&$count) {
                $this->applyScheduledPlan($user);
                $count++;
            });

        return $count;
    }

    /**
     * Sync Cashfree state and apply any paid plan that should already be active.
     */
    public function reconcileBillingState(User $user): void
    {
        $this->activateDueScheduledPlans();

        $sub = $user->subscription('default');
        if ($sub?->stripe_id) {
            $this->syncFromApi($sub->stripe_id);
            $user->refresh();
            $sub->refresh();
        }

        $transaction = BillingTransaction::query()
            ->where('user_id', $user->id)
            ->where('status', BillingTransaction::STATUS_SUCCESS)
            ->where(function ($query) {
                $query->whereNull('activates_at')
                    ->orWhere('activates_at', '<=', now());
            })
            ->latest('id')
            ->first();

        if (! $transaction || ! $user->currentPlan()->isTrial()) {
            return;
        }

        $user->update([
            'plan'                      => $transaction->plan_key,
            'trial_ends_at'             => null,
            'scheduled_plan'            => null,
            'scheduled_plan_interval'   => null,
            'scheduled_plan_starts_at' => null,
        ]);

        $sub?->update([
            'stripe_status' => 'active',
            'ends_at'       => null,
        ]);
    }

    public function applyScheduledPlan(User $user): void
    {
        if (! $user->scheduled_plan || ! $user->scheduled_plan_starts_at?->isPast()) {
            return;
        }

        $user->update([
            'plan'                      => $user->scheduled_plan,
            'trial_ends_at'             => null,
            'scheduled_plan'            => null,
            'scheduled_plan_interval'   => null,
            'scheduled_plan_starts_at' => null,
        ]);
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

        $plan = Plan::findOrFail($planKey);
        $this->cashfree->ensurePlanExists($plan, $interval);
        $this->cashfree->changePlan($sub->stripe_id, $plan->cashfreePlanId($interval));

        $sub->update([
            'stripe_price'  => "{$planKey}:{$interval}",
            'stripe_status' => 'active',
        ]);
        $user->update(['plan' => $planKey]);
    }

    protected function activatePaidSubscription(Subscription $sub, array $cashfreeData, array $returnPayload = []): void
    {
        $user = $sub->user;
        if (! $user) {
            return;
        }

        [$planKey, $interval] = array_pad(explode(':', (string) $sub->stripe_price, 2), 2, 'monthly');
        if ($planKey === '') {
            return;
        }

        $plan = Plan::findOrFail($planKey);
        $amount = $interval === 'yearly' ? $plan->priceYearly : $plan->priceMonthly;
        $activatesAt = $this->resolveActivationDate($user);

        $transaction = BillingTransaction::query()
            ->where('cashfree_subscription_id', $sub->stripe_id)
            ->latest('id')
            ->first();

        if ($transaction) {
            $transaction->update([
                'status'       => BillingTransaction::STATUS_SUCCESS,
                'paid_at'      => now(),
                'activates_at' => $activatesAt,
                'failure_reason' => null,
                'meta'         => array_merge($transaction->meta ?? [], [
                    'cashfree_status' => $cashfreeData['subscription_status'] ?? null,
                    'return'          => $returnPayload,
                ]),
            ]);
        } else {
            BillingTransaction::query()->create([
                'user_id'                  => $user->id,
                'cashfree_subscription_id' => $sub->stripe_id,
                'plan_key'                 => $planKey,
                'interval'                 => $interval,
                'amount'                   => $amount,
                'currency'                 => strtoupper((string) config('billing.currency', 'inr')),
                'status'                   => BillingTransaction::STATUS_SUCCESS,
                'paid_at'                  => now(),
                'activates_at'             => $activatesAt,
                'meta'                     => ['source' => 'webhook_or_return'],
            ]);
        }

        if ($activatesAt->isFuture()) {
            $user->update([
                'scheduled_plan'            => $planKey,
                'scheduled_plan_interval'   => $interval,
                'scheduled_plan_starts_at' => $activatesAt,
            ]);

            return;
        }

        $user->update([
            'plan'                      => $planKey,
            'trial_ends_at'             => null,
            'scheduled_plan'            => null,
            'scheduled_plan_interval'   => null,
            'scheduled_plan_starts_at' => null,
        ]);

        $sub->update([
            'stripe_status' => 'active',
            'ends_at'       => null,
        ]);
    }

    protected function resolveActivationDate(User $user): Carbon
    {
        if ($user->trial_ends_at?->isFuture()) {
            return $user->trial_ends_at->copy();
        }

        if (! app(PlanAccessService::class)->hasActiveAccess($user)) {
            return now();
        }

        $sub = $user->subscription('default');
        if ($sub?->onGracePeriod() && $sub->ends_at?->isFuture()) {
            return $sub->ends_at->copy();
        }

        return now();
    }

    protected function paymentSucceeded(array $cashfreeData, array $returnPayload, Subscription $sub): bool
    {
        if ($this->isCheckoutSuccessful($returnPayload)) {
            return true;
        }

        $status = strtoupper($this->resolvePaymentStatus($cashfreeData, $returnPayload, $sub));

        return in_array($status, ['ACTIVE', 'ACTIVATED', 'BANK_APPROVAL_PENDING'], true);
    }

    protected function resolvePaymentStatus(array $cashfreeData, array $returnPayload, Subscription $sub): string
    {
        return (string) (
            $cashfreeData['subscription_status']
            ?? $cashfreeData['status']
            ?? $returnPayload['cf_status']
            ?? $returnPayload['subscription_status']
            ?? $returnPayload['status']
            ?? $sub->stripe_status
        );
    }

    protected function isCheckoutSuccessful(array $returnPayload): bool
    {
        $checkout = strtoupper((string) ($returnPayload['cf_checkoutStatus'] ?? ''));

        return in_array($checkout, [
            'SUCCESS',
            'SUCCESS_DEBIT_PENDING',
            'SUCCESS_TOKENIZATION_PENDING',
        ], true);
    }

    protected function successMessage(User $user, ?Plan $plan): string
    {
        if ($user->scheduled_plan && $user->scheduled_plan_starts_at?->isFuture()) {
            $scheduled = Plan::find($user->scheduled_plan);

            return 'Payment received. '
                .($scheduled?->name ?? 'Your new plan')
                .' will activate on '
                .$user->scheduled_plan_starts_at->format('d M Y')
                .'. Your current plan stays active until then.';
        }

        return 'Payment successful. Your '.($plan?->name ?? 'subscription').' is now active.';
    }

    protected function humanizeFailureStatus(string $status, array $returnPayload, array $cashfreeData): string
    {
        $raw = (string) (
            $returnPayload['failure_reason']
            ?? $returnPayload['message']
            ?? $cashfreeData['failure_reason']
            ?? $status
        );

        return match (strtoupper($status)) {
            'CUSTOMER_CANCELLED', 'CANCELLED' => 'Payment was cancelled before completion.',
            'LINK_EXPIRED' => 'The payment link expired. Please try checkout again.',
            'CARD_EXPIRED' => 'The card on file has expired. Try another payment method.',
            'ON_HOLD', 'PAST_DUE' => 'Payment could not be completed. Please check your bank or try again.',
            default => $raw !== '' ? $raw : 'Payment was not completed. Please try again.',
        };
    }

    /**
     * @return array{
     *   outcome: 'success'|'failed'|'pending',
     *   message: string,
     *   user: ?User,
     *   plan: ?Plan,
     *   scheduled_plan: ?Plan,
     *   activates_at: ?Carbon,
     *   transaction: ?BillingTransaction
     * }
     */
    protected function returnOutcome(
        string $outcome,
        string $message,
        ?User $user,
        ?BillingTransaction $transaction = null,
    ): array {
        $this->activateDueScheduledPlans();

        if ($user) {
            $user->refresh();
        }

        $currentPlan = $user?->currentPlan();
        $scheduledPlan = $user?->scheduled_plan ? Plan::find($user->scheduled_plan) : null;

        return [
            'outcome'        => $outcome,
            'message'        => $message,
            'user'           => $user,
            'plan'           => $currentPlan,
            'scheduled_plan' => $scheduledPlan,
            'activates_at'   => $user?->scheduled_plan_starts_at,
            'transaction'    => $transaction,
        ];
    }

    public function restoreSessionForUser(?User $user): void
    {
        if ($user && ! Auth::check()) {
            Auth::login($user);
        }
    }
}
