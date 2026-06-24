<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\AdminStoreBrowse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect super admins away from create/edit pages while in store browse mode.
 */
class RedirectAdminBrowseWritePages
{
    /** @var list<string> */
    private const BLOCKED_SUFFIXES = [
        '.create',
        '.edit',
        '.store',
        '.update',
        '.destroy',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! AdminStoreBrowse::isActive() || ! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $name = $request->route()?->getName() ?? '';

        foreach (self::BLOCKED_SUFFIXES as $suffix) {
            if (str_ends_with($name, $suffix)) {
                return redirect()
                    ->back(fallback: route('dashboard'))
                    ->withErrors(['error' => 'Read-only admin browse — you cannot open edit or create pages.']);
            }
        }

        return $next($request);
    }
}
