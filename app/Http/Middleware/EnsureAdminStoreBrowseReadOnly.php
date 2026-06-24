<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use App\Support\AdminStoreBrowse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks mutating requests while super admin is browsing a store in read-only mode.
 */
class EnsureAdminStoreBrowseReadOnly
{
    /** @var list<string> */
    private const ALLOWED_ROUTE_PREFIXES = [
        'reports.',
        'appointments.invoice.',
    ];

    /** @var list<string> */
    private const ALLOWED_ROUTE_NAMES = [
        'ui.hide-profile-bar',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! AdminStoreBrowse::isActive()) {
            return $next($request);
        }

        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';

        foreach (self::ALLOWED_ROUTE_PREFIXES as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return $next($request);
            }
        }

        if (in_array($routeName, self::ALLOWED_ROUTE_NAMES, true)) {
            return $next($request);
        }

        if (app()->bound(AuditLogService::class)) {
            $browse = AdminStoreBrowse::session();
            app(AuditLogService::class)->admin(
                'admin.store_browse.blocked',
                "Blocked {$request->method()} on {$routeName} during read-only store browse",
                null,
                ['salon_id' => $browse['salon_id'] ?? null, 'route' => $routeName]
            );
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Read-only store browse — changes are not allowed.'], 403);
        }

        return back()->withErrors(['error' => 'Read-only mode — you cannot make changes while browsing this store.']);
    }
}
