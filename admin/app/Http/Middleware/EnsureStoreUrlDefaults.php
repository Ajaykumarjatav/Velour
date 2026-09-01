<?php

namespace App\Http\Middleware;

use App\Support\SalonUrl;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure {store} URL defaults exist for any authenticated user with a salon.
 * Prevents UrlGenerationException on billing/account/settings pages outside /{store}/…
 */
class EnsureStoreUrlDefaults
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $key = SalonUrl::keyForUser($request->user());
            if ($key) {
                URL::defaults(['store' => $key]);
            }
        }

        return $next($request);
    }
}
