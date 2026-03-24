<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * LogSlowRequests — AUDIT FIX: Performance Testing + Monitoring
 *
 * Logs any request taking longer than SLOW_REQUEST_THRESHOLD_MS.
 * Also logs N+1 query counts for any request with >20 DB queries.
 *
 * In production: warnings go to the 'slow-queries' log channel.
 * In development: dumps to the debug bar / telescope.
 */
class LogSlowRequests
{
    private const SLOW_REQUEST_THRESHOLD_MS = 1000; // 1 second
    private const HIGH_QUERY_COUNT          = 20;

    public function handle(Request $request, Closure $next): Response
    {
        $startTime    = microtime(true);
        $queryCount   = 0;
        $queriesTotal = 0.0;

        // Track DB queries in non-production (Telescope covers production)
        if (config('app.debug')) {
            \Illuminate\Support\Facades\DB::listen(function ($query) use (&$queryCount, &$queriesTotal) {
                $queryCount++;
                $queriesTotal += $query->time;
            });
        }

        $response = $next($request);

        $elapsedMs = round((microtime(true) - $startTime) * 1000, 2);

        // Add performance headers on all responses
        $response->headers->set('X-Response-Time', $elapsedMs . 'ms');

        // Log slow requests
        $isSlow       = $elapsedMs >= self::SLOW_REQUEST_THRESHOLD_MS;
        $isQueryHeavy = $queryCount >= self::HIGH_QUERY_COUNT;

        if ($isSlow || $isQueryHeavy) {
            $context = [
                'method'        => $request->method(),
                'url'           => $request->fullUrl(),
                'response_time' => $elapsedMs,
                'db_queries'    => $queryCount,
                'db_time_ms'    => round($queriesTotal, 2),
                'user_id'       => auth()->id(),
                'salon_id'      => $request->attributes->get('salon_id'),
                'request_id'    => $request->header('X-Request-ID'),
                'memory_mb'     => round(memory_get_peak_usage(true) / 1048576, 2),
                'status'        => $response->getStatusCode(),
            ];

            if ($isSlow) {
                Log::channel('slow-queries')->warning('Slow request detected', $context);
            }

            if ($isQueryHeavy) {
                Log::channel('slow-queries')->warning('High query count — potential N+1', $context);
            }
        }

        return $response;
    }
}
