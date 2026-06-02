<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\RoutePermissionMap;
use App\Support\SettingsTabPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks direct URL access when the user lacks the permission for the current route.
 */
class EnsureRoutePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || $user->isSuperAdmin() || $user->ownsCurrentSalon()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';

        if (str_starts_with($routeName, 'settings.')) {
            if (! SettingsTabPermissions::userCanAccessRoute($user, $routeName)) {
                abort(403, 'You do not have permission to access settings.');
            }

            return $next($request);
        }

        $permission = RoutePermissionMap::permissionForRoute($routeName);
        if ($permission !== null && ! $user->can($permission)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
