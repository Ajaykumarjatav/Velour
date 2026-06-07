<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Exceptions\NoCurrentTenant;
use Symfony\Component\HttpFoundation\Response;

/**
 * TenantMiddleware
 *
 * Ensures a valid, active tenant has been resolved before the request is
 * processed.  Must be applied AFTER InitializeTenancyFromDomain (which does
 * the actual tenant lookup) in the middleware stack.
 *
 * What it checks:
 *  1. A tenant was found and is now "current"  (no cross-tenant leakage)
 *  2. The tenant's `is_active` flag is true    (suspended salons are blocked)
 *
 * Failure modes:
 *  • No tenant resolved  → 404 (unknown domain/subdomain)
 *  • Tenant suspended    → 503 (account suspended)
 *
 * Both responses are JSON when the request expects JSON (API), or a simple
 * Blade abort page for web requests.
 */
class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // ── Guard: tenant must have been resolved by the finder middleware ──
        if (! Tenant::checkCurrent()) {
            return $this->tenantNotFound($request);
        }

        $tenant = Tenant::current();

        // ── Guard: tenant must be active ────────────────────────────────────
        if (! $tenant->is_active) {
            return $this->tenantSuspended($request, $tenant);
        }

        return $next($request);
    }

    // -------------------------------------------------------------------------
    // Error Responses
    // -------------------------------------------------------------------------

    private function tenantNotFound(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Salon not found. Check the domain or subdomain and try again.',
                'code'    => 'TENANT_NOT_FOUND',
            ], 404);
        }

        abort(404, 'Salon not found.');
    }

    private function tenantSuspended(Request $request, Tenant $tenant): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'This salon account has been suspended. Please contact support.',
                'code'    => 'TENANT_SUSPENDED',
                'salon'   => $tenant->name,
            ], 503);
        }

        abort(503, "The salon \"{$tenant->name}\" is currently suspended.");
    }
}
