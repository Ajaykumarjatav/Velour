<?php

namespace App\Http\Middleware;

use App\Billing\Plan;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckSubscription
 *
 * Gates access based on the authenticated user's subscription status and plan.
 *
 * Usage in routes:
 *   Route::middleware('subscription')         → must have any active subscription
 *   Route::middleware('subscription:pro')     → must be on pro or enterprise
 *   Route::middleware('subscription:feature:marketing') → plan must allow feature
 *
 * Grace period handling:
 *   Users within the billing grace period (payment failed, retry pending)
 *   receive degraded-but-not-blocked access: they can continue using the
 *   product but see a persistent payment-failed banner.
 *
 * Trial handling:
 *   Trial users are treated as fully subscribed for access checks.
 *
 * Super-admins bypass all subscription checks.
 */
class CheckSubscription
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        if (! config('billing.subscriptions_enabled')) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user) {
            return $this->deny($request, 'Authentication required.');
        }

        // Super-admins bypass all subscription checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Parse the guard arguments
        [$checkType, $checkValue] = $this->parseGuards($guards);

        return match ($checkType) {
            'plan'    => $this->checkPlan($request, $next, $user, $checkValue),
            'feature' => $this->checkFeature($request, $next, $user, $checkValue),
            default   => $this->checkActive($request, $next, $user),
        };
    }

    // ── Check: any active subscription ────────────────────────────────────────

    private function checkActive(Request $request, Closure $next, $user): Response
    {
        if ($user->onPaidPlan() || $user->currentPlan()->isFree()) {
            return $next($request);
        }

        return $this->deny($request, 'An active subscription is required to access this feature.');
    }

    // ── Check: minimum plan tier ───────────────────────────────────────────────

    private function checkPlan(Request $request, Closure $next, $user, string $requiredPlan): Response
    {
        $current  = $user->currentPlan();
        $required = Plan::find($requiredPlan);

        if (! $required) {
            return $next($request); // Unknown plan → pass through
        }

        if (! $required->isUpgradeFrom($current->key) || $current->key === $required->key) {
            return $next($request);
        }

        $message = "The {$required->name} plan or higher is required.";
        return $this->deny($request, $message, $required->key);
    }

    // ── Check: feature flag ────────────────────────────────────────────────────

    private function checkFeature(Request $request, Closure $next, $user, string $feature): Response
    {
        if ($user->currentPlan()->allows($feature)) {
            return $next($request);
        }

        $message = "Your current plan does not include this feature.";
        return $this->deny($request, $message, null, $feature);
    }

    // ── Deny helper ───────────────────────────────────────────────────────────

    private function deny(Request $request, string $message, ?string $requiredPlan = null, ?string $requiredFeature = null): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message'          => $message,
                'code'             => 'SUBSCRIPTION_REQUIRED',
                'required_plan'    => $requiredPlan,
                'required_feature' => $requiredFeature,
                'upgrade_url'      => route('billing.plans'),
            ], 402); // 402 Payment Required
        }

        return redirect()
            ->route('billing.plans')
            ->with('warning', $message . ' Please upgrade your plan to continue.');
    }

    // ── Guard parser ──────────────────────────────────────────────────────────

    private function parseGuards(array $guards): array
    {
        if (empty($guards)) {
            return ['active', null];
        }

        // subscription:feature:marketing
        if (count($guards) >= 2 && $guards[0] === 'feature') {
            return ['feature', $guards[1]];
        }

        // subscription:pro
        return ['plan', $guards[0]];
    }
}
