<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect HTTP to HTTPS in production when the app is not already secure.
 * Works with Cloudflare / load balancers via trusted X-Forwarded-Proto.
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            app()->environment('production')
            && ! $request->secure()
            && ! $request->is('up')
        ) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
