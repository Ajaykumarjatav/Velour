<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\MultiLocationPermissions;
use App\Support\RoutePermissionMap;
use App\Support\SettingsTabPermissions;
use App\Support\WebsitePermissions;
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
        if ($permission !== null && ! $this->userHasPermission($user, $permission)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }

    private function userHasPermission($user, string $permission): bool
    {
        if ($user->can($permission)) {
            return true;
        }

        return match ($permission) {
            'website.view' => WebsitePermissions::canView($user),
            'website.edit', 'website.share' => WebsitePermissions::canEdit($user),
            'multi-location.view' => MultiLocationPermissions::canView($user),
            'multi-location.edit', 'multi-location.switch' => MultiLocationPermissions::canEdit($user),
            default => false,
        };
    }
}
