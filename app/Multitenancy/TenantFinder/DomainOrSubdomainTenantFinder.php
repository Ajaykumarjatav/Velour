<?php

namespace App\Multitenancy\TenantFinder;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

/**
 * DomainOrSubdomainTenantFinder
 *
 * Resolves the tenant (salon) for the current HTTP request.
 *
 * In this deployment, tenants are determined by the currently authenticated
 * user rather than by the request host (subdomain/domain). This supports local
 * development and setups where the app is accessed via a single shared domain.
 */
class DomainOrSubdomainTenantFinder extends TenantFinder
{
    /**
     * Resolve the tenant for the current request.
     *
     * @param  Request  $request
     * @return IsTenant|null
     */
    public function findForRequest(Request $request): ?IsTenant
    {
        // Tenant is resolved from the logged-in user (owner or staff member).
        if (Auth::check()) {
            $user = Auth::user();

            // If the user is a salon owner, use their first salon.
            if ($user->salons()->exists()) {
                $salon = $user->salons()->first();
                return Tenant::query()->withoutGlobalScopes()->find($salon->id);
            }

            // If the user is a staff member, use their assigned salon.
            if ($user->staffProfile?->salon_id) {
                return Tenant::query()->withoutGlobalScopes()->find($user->staffProfile->salon_id);
            }
        }

        // No authenticated user or no associated tenant.
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Internal Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get the plain hostname from the request, stripping any port number.
     *
     *   "mysalon.velour.app:8080" → "mysalon.velour.app"
     */
    private function extractHost(Request $request): string
    {
        $host = $request->getHost();          // Already strips the port
        return mb_strtolower(trim($host));
    }

    /**
     * Return the subdomain segment if $host is a direct subdomain of
     * $baseDomain, or null otherwise.
     *
     *   extractSubdomain("mysalon.velour.app", "velour.app")  → "mysalon"
     *   extractSubdomain("a.b.velour.app",     "velour.app")  → null  (nested)
     *   extractSubdomain("velour.app",          "velour.app") → null  (root)
     *   extractSubdomain("otherdomain.com",     "velour.app") → null
     */
    private function extractSubdomain(string $host, string $baseDomain): ?string
    {
        $base   = mb_strtolower(trim($baseDomain, '.'));
        $suffix = '.' . $base;

        // Host must end with ".basedomain"
        if (! str_ends_with($host, $suffix)) {
            return null;
        }

        $subdomain = substr($host, 0, -strlen($suffix));

        // Must be a single-level subdomain (no dots)
        if (empty($subdomain) || str_contains($subdomain, '.')) {
            return null;
        }

        // Sanitise: only allow lowercase alphanumeric + hyphens
        if (! preg_match('/^[a-z0-9\-]+$/', $subdomain)) {
            return null;
        }

        return $subdomain;
    }
}
