<?php

namespace App\Http\Middleware;

use App\Support\ProfileCompletion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * When salon profile completion is below 100%, redirect to Settings so the setup bar can be cleared.
 * Routes required to complete the checklist (services, categories, staff, settings) stay accessible.
 */
class EnsureSalonProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->expectsJson()) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $activeSalonId = (int) session('active_salon_id', 0);
        $salon = $activeSalonId > 0
            ? $user->salons()->where('id', $activeSalonId)->first()
            : null;
        $salon ??= $user->salons()->first();

        if (! $salon) {
            return $next($request);
        }

        $completion = ProfileCompletion::forSalon($salon);
        if ((int) ($completion['percentage'] ?? 0) >= 100) {
            return $next($request);
        }

        $name = $request->route()?->getName();
        if ($name && self::routeAllowedDuringSetup($name)) {
            return $next($request);
        }

        return redirect()->route('settings.index', array_filter([
            'tab' => 'salon',
            'return_to' => $request->isMethod('GET') ? $request->fullUrl() : null,
        ]))->with('warning', 'Complete your salon setup (profile bar must reach 100%) to use this area. Use Settings plus Services, Categories, and Staff until all checklist items are done.');
    }

    private static function routeAllowedDuringSetup(string $name): bool
    {
        $exact = [
            'set_socket_blocking',
            'setup-progress',
            'quick-create.staff',
            'quick-create.service',
        ];
        if (in_array($name, $exact, true)) {
            return true;
        }

        $prefixes = [
            'settings.',
            'service-categories.',
            'services.',
            'staff.',
            'notifications.',
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
