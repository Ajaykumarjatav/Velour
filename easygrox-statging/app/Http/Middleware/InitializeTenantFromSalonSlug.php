<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * InitializeTenantFromSalonSlug
 *
 * Public salon endpoints (booking widget, client portal) are unauthenticated, so
 * DomainOrSubdomainTenantFinder — which resolves the tenant from the logged-in user —
 * cannot find one. Queues are tenant aware by default, so without a current tenant every
 * mail queued from these requests dies with CurrentTenantCouldNotBeDetermined and the
 * salon never learns about the booking.
 *
 * The salon slug is already part of the route, so use it to establish the tenant.
 * Unknown or inactive slugs are left alone: the controllers own those error responses.
 */
class InitializeTenantFromSalonSlug
{
    public function handle(Request $request, Closure $next): Response
    {
        $madeCurrent = false;

        if (! Tenant::checkCurrent()) {
            $slug = $request->route('salonSlug') ?? $request->route('slug');

            if (is_string($slug) && $slug !== '') {
                $tenant = Tenant::query()
                    ->withoutGlobalScopes()
                    ->where('slug', $slug)
                    ->where('is_active', true)
                    ->first();

                if ($tenant) {
                    $tenant->makeCurrent();
                    $madeCurrent = true;
                }
            }
        }

        $response = $next($request);

        // Only unwind what this middleware set up, so an authenticated preview keeps its tenant.
        if ($madeCurrent) {
            Tenant::forgetCurrent();
        }

        return $response;
    }
}
