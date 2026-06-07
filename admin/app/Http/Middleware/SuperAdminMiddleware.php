<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SuperAdminMiddleware
 *
 * Protects routes that are only accessible to platform-level super-admins
 * (system_role = 'super_admin').
 *
 * This is intentionally separate from Spatie's permission middleware so that
 * super-admin access is a single column check and cannot be accidentally
 * granted to tenant users via role assignment.
 */
class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access denied.'], 403);
            }
            abort(403, 'Super-admin access required.');
        }

        return $next($request);
    }
}
