<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TenantAdminMiddleware
 *
 * Restricts access to routes that require the `tenant_admin` role OR
 * super-admin privileges.  This is used for sensitive sections like
 * billing, user management, and advanced settings within a salon.
 */
class TenantAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        // Super-admins can access everything
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check for tenant_admin role
        if ($user->hasRole('tenant_admin')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Tenant admin access required.'], 403);
        }

        abort(403, 'You need tenant admin privileges to access this area.');
    }
}
