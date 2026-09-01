<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Multitenancy\TenantFinder\DomainOrSubdomainTenantFinder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * InitializeTenancyFromDomain
 *
 * The FIRST tenant-related middleware in the pipeline.
 *
 * Responsibilities:
 *  1. Call the DomainOrSubdomainTenantFinder to look up the tenant for the
 *     current request host.
 *  2. If found, call $tenant->makeCurrent() which fires all the configured
 *     `switch_tenant_tasks` (session, container binding, cache prefix, etc.).
 *  3. After the request is handled, call Tenant::forgetCurrent() to clean up
 *     state so it does not bleed into the next request on the same worker.
 *
 * If no tenant is found this middleware simply proceeds — TenantMiddleware
 * (which runs after this) will handle the 404/abort.
 *
 * This separation of concerns allows some routes (e.g. the Stripe webhook,
 * the health-check endpoint) to skip TenantMiddleware while still running
 * through this initialiser without crashing.
 */
class InitializeTenancyFromDomain
{
    public function __construct(
        protected DomainOrSubdomainTenantFinder $finder
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // ── Resolve tenant from request domain/subdomain ────────────────────
        $tenant = $this->finder->findForRequest($request);

        if ($tenant !== null) {
            $tenant->makeCurrent();

            if (config('app.debug')) {
                Log::debug('[Tenancy] Tenant resolved', [
                    'id'        => $tenant->getKey(),
                    'name'      => $tenant->name,
                    'host'      => $request->getHost(),
                ]);
            }
        }

        // ── Handle the request ──────────────────────────────────────────────
        $response = $next($request);

        // ── Cleanup: forget the tenant so the worker process starts fresh ───
        // This is critical for long-running PHP processes (Octane, Horizon)
        // where the same worker handles many requests sequentially.
        Tenant::forgetCurrent();

        return $response;
    }
}
