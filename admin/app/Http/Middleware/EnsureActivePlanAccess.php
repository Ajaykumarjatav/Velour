<?php

namespace App\Http\Middleware;

use App\Services\Billing\PlanAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * When a tenant's plan/trial has expired, only billing (subscription) routes remain accessible.
 */
class EnsureActivePlanAccess
{
    public function __construct(private readonly PlanAccessService $planAccess) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('billing.subscriptions_enabled')) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $routeName = (string) ($request->route()?->getName() ?? '');

        if ($this->isAllowedWhenExpired($routeName)) {
            return $next($request);
        }

        if (! $this->planAccess->isExpired($user)) {
            return $next($request);
        }

        $message = 'Your plan has expired. Renew your subscription to access the salon panel.';
        $renewUrl = $this->planAccess->renewalUrl();

        if ($request->expectsJson()) {
            return response()->json([
                'message'   => $message,
                'code'      => 'PLAN_EXPIRED',
                'renew_url' => $renewUrl,
            ], 402);
        }

        return redirect()
            ->to($renewUrl)
            ->with('warning', $message);
    }

    private function isAllowedWhenExpired(string $routeName): bool
    {
        if ($routeName === '' || $routeName === 'logout') {
            return true;
        }

        if (str_starts_with($routeName, 'billing.')) {
            return true;
        }

        return in_array($routeName, [
            'salon-admin.subscription',
        ], true);
    }
}
