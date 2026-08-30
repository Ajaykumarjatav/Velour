<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ValidateApiVersion — API SECURITY + ARCHITECTURE
 *
 * Rejects requests targeting deprecated API versions with a clear migration message.
 * Currently only v1 is supported. When v2 ships, v1 gets a sunset header.
 */
class ValidateApiVersion
{
    private const SUPPORTED = ['v1'];
    private const DEPRECATED = [];

    public function handle(Request $request, Closure $next): Response
    {
        $path    = $request->path();
        $version = explode('/', ltrim($path, '/'))[1] ?? null;

        if ($version && str_starts_with($version, 'v') && ! in_array($version, self::SUPPORTED)) {
            return response()->json([
                'message'   => "API {$version} is not supported. Please use v1.",
                'docs_url'  => config('app.url') . '/docs/api',
            ], 400);
        }

        $response = $next($request);

        // Add version and deprecation headers
        $response->headers->set('API-Version', 'v1');
        if (in_array($version, self::DEPRECATED)) {
            $response->headers->set('Sunset', 'Sat, 01 Jan 2026 00:00:00 GMT');
            $response->headers->set('Deprecation', 'true');
            $response->headers->set('Link', '</docs/api/migration>; rel="deprecation"');
        }

        return $response;
    }
}
