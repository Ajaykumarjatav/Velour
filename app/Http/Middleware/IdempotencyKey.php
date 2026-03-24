<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * IdempotencyKey — SECURITY + SaaS ESSENTIAL
 *
 * Prevents duplicate charges and double-bookings from network retries.
 * Applied to: POST /pos, POST /book/confirm, POST /pos/stripe/intent
 *
 * Client sends: Idempotency-Key: <uuid>
 * Server caches the response for 24h. Same key = same response.
 */
class IdempotencyKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');

        if (! $key) {
            return $next($request);
        }

        // Validate key format (must be UUID)
        if (! preg_match('/^[0-9a-f\-]{36}$/i', $key)) {
            return response()->json([
                'message' => 'Idempotency-Key must be a valid UUID v4.',
                'code'    => 'INVALID_IDEMPOTENCY_KEY',
            ], 400);
        }

        $cacheKey = 'idempotency:' . sha1($request->user()?->id . ':' . $key);

        // Return cached response if key was already used
        if ($cached = Cache::get($cacheKey)) {
            return response()->json(
                json_decode($cached['body'], true),
                $cached['status']
            )->header('Idempotency-Replayed', 'true');
        }

        $response = $next($request);

        // Cache successful mutation responses for 24 hours
        if ($response->getStatusCode() < 500) {
            Cache::put($cacheKey, [
                'status' => $response->getStatusCode(),
                'body'   => $response->getContent(),
            ], now()->addHours(24));
        }

        return $response;
    }
}
