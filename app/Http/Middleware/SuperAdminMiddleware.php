<?php

namespace App\Http\Middleware;

use App\Support\AuthPanel;
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

            $user = $request->user();
            if ($user && AuthPanel::canAccessSalonPanel($user)) {
                return redirect()
                    ->route('dashboard')
                    ->with('error', 'That area is for platform administrators only.');
            }

            abort(403, 'Super-admin access required.');
        }

        return $next($request);
    }
}
