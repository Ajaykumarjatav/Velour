<?php

namespace App\Http\Middleware;

use App\Billing\Plan;
use App\Models\Staff;
use App\Models\Service;
use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Spatie\Multitenancy\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckPlanLimits
 *
 * Enforces numeric resource caps defined in config/billing.php per plan.
 *
 * Applied to write operations (store/create routes) to block creation
 * once the current count hits the plan limit.
 *
 * Usage in routes:
 *   ->middleware('plan.limit:staff')
 *   ->middleware('plan.limit:services')
 *   ->middleware('plan.limit:clients')
 *
 * Limits of -1 = unlimited (always passes).
 * Super-admins bypass all checks.
 */
class CheckPlanLimits
{
    public function handle(Request $request, Closure $next, string $resource = ''): Response
    {
        // Only enforce on write operations
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            return $next($request);
        }

        $user = Auth::user();

        // Super-admins and missing resources bypass
        if (! $user || ! $resource || $user->isSuperAdmin()) {
            return $next($request);
        }

        $plan  = $user->currentPlan();
        $limit = $plan->limit($resource);

        // -1 = unlimited
        if ($limit === -1) {
            return $next($request);
        }

        $salonId = Tenant::current()?->id ?? $user->salons()->value('id');
        if (! $salonId) {
            return $next($request);
        }

        $current = $this->count($resource, $salonId);

        if ($current >= $limit) {
            $message = "You've reached the {$resource} limit ({$limit}) on the {$plan->name} plan.";

            if ($request->expectsJson()) {
                return response()->json([
                    'message'    => $message,
                    'code'       => 'PLAN_LIMIT_REACHED',
                    'resource'   => $resource,
                    'limit'      => $limit,
                    'current'    => $current,
                    'upgrade_url'=> route('billing.plans'),
                ], 402);
            }

            return redirect()->back()
                ->with('warning', $message . ' <a href="'.route('billing.plans').'" class="underline font-semibold">Upgrade your plan →</a>');
        }

        return $next($request);
    }

    private function count(string $resource, int $salonId): int
    {
        $cacheKey = "plan_limit_{$resource}_{$salonId}";

        return Cache::remember($cacheKey, 60, function () use ($resource, $salonId) {
            return match ($resource) {
                'staff'    => Staff::withoutGlobalScopes()->where('salon_id', $salonId)->where('is_active', true)->count(),
                'services' => Service::withoutGlobalScopes()->where('salon_id', $salonId)->count(),
                'clients'  => Client::withoutGlobalScopes()->where('salon_id', $salonId)->count(),
                default    => 0,
            };
        });
    }
}
