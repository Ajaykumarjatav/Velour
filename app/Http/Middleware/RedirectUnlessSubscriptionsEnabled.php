<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * When subscriptions are disabled in config, billing/plan routes redirect away.
 */
class RedirectUnlessSubscriptionsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('billing.subscriptions_enabled')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return redirect()->route('dashboard');
    }
}
