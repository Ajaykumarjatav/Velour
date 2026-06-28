<?php

namespace App\Http\Controllers\Billing;

use App\Billing\Plan;
use App\Http\Controllers\Controller;
use App\Services\Billing\CashfreeService;
use App\Services\Billing\SubscriptionBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * BillingController — Cashfree subscription checkout & management.
 *
 * Routes prefix: /billing
 */
class BillingController extends Controller
{
    public function __construct(
        protected CashfreeService $cashfree,
        protected SubscriptionBillingService $billing,
    ) {}

    public function plans(Request $request)
    {
        $user     = Auth::user();
        $plans    = Plan::all();
        $interval = $request->get('interval', 'monthly');
        $current  = $user->currentPlan();
        $sub      = $user->subscription('default');

        return view('billing.plans', compact('plans', 'interval', 'current', 'sub', 'user'));
    }

    public function checkout(Request $request)
    {
        if (! $this->cashfree->isConfigured()) {
            return back()->with('error', 'Cashfree is not configured yet. Please contact support.');
        }

        $request->validate([
            'plan'     => 'required|'.Plan::paidValidationRule(),
            'interval' => 'required|in:monthly,yearly',
        ]);

        $user = Auth::user();
        $plan = Plan::findOrFail($request->plan);

        if ($user->subscribed('default')) {
            return redirect()->route('billing.change', $request->only('plan', 'interval'));
        }

        try {
            $response = $this->cashfree->createSubscription($user, $plan, $request->interval);
        } catch (\Throwable $e) {
            Log::error('[Billing] Cashfree checkout failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return back()->withErrors(['plan' => 'Could not start checkout. '.$e->getMessage()]);
        }

        $subscriptionId = (string) ($response['subscription_id'] ?? '');
        $sessionId      = (string) ($response['subscription_session_id'] ?? '');

        if ($subscriptionId === '' || $sessionId === '') {
            return back()->withErrors(['plan' => 'Cashfree did not return a checkout session.']);
        }

        $this->billing->upsertPending($user, $subscriptionId, $plan->key, $request->interval);

        return view('billing.cashfree-checkout', [
            'sessionId' => $sessionId,
            'mode'      => $this->cashfree->sdkMode(),
            'plan'      => $plan,
            'interval'  => $request->interval,
        ]);
    }

    public function paymentReturn(Request $request)
    {
        $subscriptionId = (string) (
            $request->input('subscription_id')
            ?? $request->query('subscription_id')
            ?? $request->input('cf_subscription_id')
            ?? ''
        );

        if ($subscriptionId === '') {
            return view('billing.failed', [
                'message'        => 'Missing payment reference. If money was deducted, contact support.',
                'plan'           => null,
                'scheduled_plan' => null,
                'activates_at'   => null,
            ]);
        }

        $result = $this->billing->handlePaymentReturn($subscriptionId, $request->all());

        if ($result['user']) {
            $this->billing->restoreSessionForUser($result['user']);
        }

        if ($result['outcome'] === 'success') {
            return view('billing.success', $result);
        }

        if ($result['outcome'] === 'pending') {
            return view('billing.pending', $result);
        }

        return view('billing.failed', $result);
    }

    public function success(Request $request)
    {
        return redirect()->route('billing.dashboard');
    }

    public function showChangePlan(Request $request)
    {
        $request->validate([
            'plan'     => 'required|'.Plan::paidValidationRule(),
            'interval' => 'required|in:monthly,yearly',
        ]);

        $user       = Auth::user();
        $targetPlan = Plan::findOrFail($request->plan);
        $current    = $user->currentPlan();
        $interval   = $request->interval;
        $sub        = $user->subscription('default');

        return view('billing.upgrade-confirm', compact('targetPlan', 'current', 'interval', 'sub', 'user'));
    }

    public function changePlan(Request $request)
    {
        if (! $this->cashfree->isConfigured()) {
            return back()->with('error', 'Cashfree is not configured yet.');
        }

        $request->validate([
            'plan'     => 'required|'.Plan::paidValidationRule(),
            'interval' => 'required|in:monthly,yearly',
        ]);

        $user = Auth::user();
        $plan = Plan::findOrFail($request->plan);
        $sub  = $user->subscription('default');

        if (! $sub || ! in_array($sub->stripe_status, ['active', 'past_due', 'trialing'], true)) {
            return redirect()->route('billing.checkout', $request->only('plan', 'interval'));
        }

        try {
            $this->billing->changePlan($user, $plan->key, $request->interval);
        } catch (\Throwable $e) {
            return back()->withErrors(['plan' => $e->getMessage()]);
        }

        $message = $plan->isUpgradeFrom($user->plan ?? config('billing.default_plan', 'trial'))
            ? "Upgraded to {$plan->name}."
            : "Plan changed to {$plan->name}.";

        return redirect()->route('billing.dashboard')->with('success', $message);
    }

    public function showCancel()
    {
        $user = Auth::user();
        $sub  = $user->subscription('default');

        if (! $sub || $sub->canceled()) {
            return redirect()->route('billing.dashboard')
                ->with('info', 'You do not have an active subscription to cancel.');
        }

        return view('billing.cancel', [
            'user'   => $user,
            'sub'    => $sub,
            'endsAt' => $sub->ends_at ?? now()->endOfMonth(),
        ]);
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'reason'   => 'nullable|string|max:500',
            'password' => 'required',
        ]);

        $user = Auth::user();

        if (! \Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $sub = $user->subscription('default');

        if (! $sub || $sub->canceled()) {
            return back()->with('info', 'No active subscription to cancel.');
        }

        try {
            $this->billing->cancel($user, immediately: false);
        } catch (\Throwable $e) {
            return back()->withErrors(['password' => 'Could not cancel subscription. Please try again.']);
        }

        Log::info('[Billing] Subscription cancelled', [
            'user_id' => $user->id,
            'reason'  => $request->reason,
        ]);

        return redirect()->route('billing.dashboard')
            ->with('success', 'Subscription cancelled. You have access until the end of the current billing period.');
    }

    public function resume(Request $request)
    {
        return back()->with('info', 'Please contact support to resume a cancelled Cashfree subscription.');
    }

    public function portal(Request $request)
    {
        return redirect()->route('billing.dashboard')
            ->with('info', 'Manage your plan and payment method from this billing page.');
    }

    public function dashboard(Request $request)
    {
        $this->billing->activateDueScheduledPlans();

        $user    = Auth::user()->fresh();
        $sub     = $user->subscription('default');
        $current = $user->currentPlan();
        $scheduledPlan = $user->scheduled_plan ? Plan::find($user->scheduled_plan) : null;
        $transactions = $user->billingTransactions()->latest()->limit(50)->get();

        return view('billing.dashboard', compact(
            'user',
            'sub',
            'current',
            'scheduledPlan',
            'transactions',
        ));
    }

    public function downloadInvoice(Request $request, string $invoiceId)
    {
        return redirect()->route('billing.dashboard')
            ->with('info', 'Invoices are sent by Cashfree to your registered email.');
    }

    public function applyPromo(Request $request)
    {
        return back()->with('info', 'Promo codes are not available for Cashfree billing yet.');
    }
}
