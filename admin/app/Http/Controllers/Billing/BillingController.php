<?php

namespace App\Http\Controllers\Billing;

use App\Billing\Plan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Exceptions\IncompletePayment;

/**
 * BillingController
 *
 * Handles all user-facing subscription actions.
 *
 * Routes prefix: /billing
 * Middleware:    auth, verified, 2fa, tenant
 */
class BillingController extends Controller
{
    // ── Pricing page ──────────────────────────────────────────────────────────

    public function plans(Request $request)
    {
        $user     = Auth::user();
        $plans    = Plan::all();
        $interval = $request->get('interval', 'monthly');
        $current  = $user->currentPlan();
        $sub      = $user->subscription('default');

        return view('billing.plans', compact('plans', 'interval', 'current', 'sub', 'user'));
    }

    // ── Checkout — new subscription ───────────────────────────────────────────

    public function checkout(Request $request)
    {
        if (! config('cashier.secret')) {
            return back()->with('error', 'Payment processing is not configured yet. Please contact support.');
        }

        $request->validate([
            'plan'     => 'required|in:starter,pro,enterprise',
            'interval' => 'required|in:monthly,yearly',
        ]);

        $user     = Auth::user();
        $plan     = Plan::findOrFail($request->plan);
        $priceId  = $plan->stripePriceId($request->interval);

        if (! $priceId) {
            return back()->withErrors(['plan' => 'This plan is not available. Please contact support.']);
        }

        // Already subscribed → redirect to upgrade flow
        if ($user->subscribed('default')) {
            return redirect()->route('billing.change', [
                'plan'     => $request->plan,
                'interval' => $request->interval,
            ]);
        }

        // Build Checkout session
        $checkout = $user->newSubscription('default', $priceId)
            ->trialDays($plan->trialDays)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('billing.plans'),
                'metadata' => [
                    'plan'     => $plan->key,
                    'interval' => $request->interval,
                    'user_id'  => $user->id,
                ],
                'subscription_data' => [
                    'metadata' => [
                        'plan'     => $plan->key,
                        'interval' => $request->interval,
                    ],
                ],
                'customer_email' => $user->email,
            ]);

        return redirect($checkout->url);
    }

    // ── Checkout success ──────────────────────────────────────────────────────

    public function success(Request $request)
    {
        // Cashier auto-processes the session via webhooks; just show a page
        return view('billing.success', [
            'plan' => Auth::user()->currentPlan(),
        ]);
    }

    // ── Change plan (upgrade or downgrade) ────────────────────────────────────

    /**
     * Show the change-plan confirmation page.
     */
    public function showChangePlan(Request $request)
    {
        $request->validate([
            'plan'     => 'required|in:starter,pro,enterprise',
            'interval' => 'required|in:monthly,yearly',
        ]);

        $user       = Auth::user();
        $targetPlan = Plan::findOrFail($request->plan);
        $current    = $user->currentPlan();
        $interval   = $request->interval;
        $sub        = $user->subscription('default');

        return view('billing.upgrade-confirm', compact('targetPlan', 'current', 'interval', 'sub', 'user'));
    }

    // ── Change plan (upgrade or downgrade) — execute ──────────────────────────

    public function changePlan(Request $request)
    {
        $request->validate([
            'plan'     => 'required|in:starter,pro,enterprise',
            'interval' => 'required|in:monthly,yearly',
        ]);

        $user    = Auth::user();
        $plan    = Plan::findOrFail($request->plan);
        $priceId = $plan->stripePriceId($request->interval);

        if (! $priceId) {
            return back()->withErrors(['plan' => 'This plan configuration is unavailable.']);
        }

        $sub = $user->subscription('default');

        if (! $sub) {
            return redirect()->route('billing.checkout', $request->only('plan', 'interval'));
        }

        // If currently on trial, swap immediately (no proration needed)
        if ($sub->onTrial()) {
            $sub->swapAndInvoice($priceId);
            $this->syncPlanToUser($user, $plan->key);

            return redirect()->route('billing.dashboard')
                ->with('success', "Plan changed to {$plan->name}. Trial continues until " . $sub->trial_ends_at->format('d M Y') . '.');
        }

        // Upgrade: swap and invoice immediately (user pays the prorated diff now)
        if ($plan->isUpgradeFrom($user->plan ?? 'free')) {
            $sub->swapAndInvoice($priceId);
            $this->syncPlanToUser($user, $plan->key);

            return redirect()->route('billing.dashboard')
                ->with('success', "Upgraded to {$plan->name}. You've been charged the prorated difference.");
        }

        // Downgrade: swap at end of billing period (no immediate charge)
        $sub->swap($priceId);
        $this->syncPlanToUser($user, $plan->key);

        return redirect()->route('billing.dashboard')
            ->with('success', "Plan changed to {$plan->name}. The change takes effect at your next billing date.");
    }

    // ── Cancel subscription ───────────────────────────────────────────────────

    public function showCancel()
    {
        $user = Auth::user();
        $sub  = $user->subscription('default');

        if (! $sub || $sub->cancelled()) {
            return redirect()->route('billing.dashboard')
                ->with('info', 'You do not have an active subscription to cancel.');
        }

        return view('billing.cancel', [
            'user'    => $user,
            'sub'     => $sub,
            'endsAt'  => $sub->ends_at ?? now()->endOfMonth(),
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

        if (! $sub || $sub->cancelled()) {
            return back()->with('info', 'No active subscription to cancel.');
        }

        // Cancel at end of billing period (not immediate)
        $sub->cancel();

        Log::info('[Billing] Subscription cancelled', [
            'user_id' => $user->id,
            'reason'  => $request->reason,
            'ends_at' => $sub->ends_at,
        ]);

        return redirect()->route('billing.dashboard')
            ->with('success', 'Subscription cancelled. You have access until ' . $sub->ends_at->format('d M Y') . '.');
    }

    // ── Resume cancelled subscription ─────────────────────────────────────────

    public function resume(Request $request)
    {
        $user = Auth::user();
        $sub  = $user->subscription('default');

        if (! $sub || ! $sub->onGracePeriod()) {
            return back()->with('info', 'Nothing to resume — subscription is already active or fully expired.');
        }

        $sub->resume();

        return redirect()->route('billing.dashboard')
            ->with('success', 'Subscription resumed. Billing continues as normal.');
    }

    // ── Stripe Customer Portal ────────────────────────────────────────────────

    public function portal(Request $request)
    {
        if (! config('cashier.secret')) {
            return redirect()->route('billing.dashboard')
                ->with('error', 'Payment processing is not configured yet. Please contact support.');
        }

        $user = Auth::user();

        // Create Stripe customer if they don't have one yet
        if (! $user->stripe_id) {
            $user->createAsStripeCustomer([
                'email' => $user->email,
                'name'  => $user->name,
                'metadata' => ['user_id' => $user->id],
            ]);
        }

        return $user->redirectToCustomerPortal(
            route('billing.dashboard')
        );
    }

    // ── Billing dashboard ─────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $user     = Auth::user();
        $sub      = $user->subscription('default');
        $current  = $user->currentPlan();
        $invoices = [];

        if ($user->stripe_id) {
            try {
                $invoices = $user->invoices();
            } catch (\Throwable) {
                $invoices = [];
            }
        }

        return view('billing.dashboard', compact('user', 'sub', 'current', 'invoices'));
    }

    // ── Invoice download ──────────────────────────────────────────────────────

    public function downloadInvoice(Request $request, string $invoiceId)
    {
        $user = Auth::user();

        return $user->downloadInvoice($invoiceId, [
            'vendor'  => 'Velour',
            'product' => 'Salon Management Subscription',
            'street'  => '1 Velour Way',
            'location'=> 'London',
            'country' => 'United Kingdom',
            'url'     => config('app.url'),
        ]);
    }

    // ── Apply promo / coupon ──────────────────────────────────────────────────

    public function applyPromo(Request $request)
    {
        $request->validate(['promo_code' => 'required|string|max:50']);

        $user = Auth::user();

        try {
            $user->applyPromotionCode($request->promo_code);
            return back()->with('success', 'Promo code applied successfully.');
        } catch (\Throwable $e) {
            return back()->withErrors(['promo_code' => 'Invalid or expired promo code.']);
        }
    }

    // ── Private ───────────────────────────────────────────────────────────────

    /**
     * Keep users.plan in sync with the Stripe subscription.
     * This is the app-side record; Stripe is the source of truth.
     */
    private function syncPlanToUser($user, string $planKey): void
    {
        $user->update(['plan' => $planKey]);
    }
}
