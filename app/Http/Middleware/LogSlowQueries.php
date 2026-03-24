<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs any database query exceeding the threshold to the daily log.
 * Enabled in non-production by default; configurable via env.
 * Set LOG_SLOW_QUERY_MS=0 to disable entirely.
 */
class LogSlowQueries
{
    private int $thresholdMs;

    public function __construct()
    {
        $this->thresholdMs = (int) env('LOG_SLOW_QUERY_MS', app()->isProduction() ? 500 : 200);
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->thresholdMs === 0) {
            return $next($request);
        }

        DB::listen(function ($query) use ($request) {
            if ($query->time >= $this->thresholdMs) {
                Log::warning('Slow SQL query detected', [
                    'sql'          => $query->sql,
                    'bindings'     => $query->bindings,
                    'time_ms'      => $query->time,
                    'threshold_ms' => $this->thresholdMs,
                    'url'          => $request->fullUrl(),
                    'method'       => $request->method(),
                    'user_id'      => $request->user()?->id,
                    'salon_id'     => $request->attributes->get('salon_id'),
                ]);
            }
        });

        return $next($request);
    }
}
