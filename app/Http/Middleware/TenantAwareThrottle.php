<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * TenantAwareThrottle
 *
 * A plan-aware rate limiter that scales API limits based on the
 * authenticated user's subscription tier.
 *
 * Usage in routes:
 *   ->middleware('throttle.tenant')         → uses default 'api' plan limits
 *   ->middleware('throttle.tenant:exports') → uses 'exports' plan limits (per-hour)
 *
 * Limit tiers (from config/security.php):
 *   free        →  30 req/min
 *   starter     →  60 req/min
 *   pro         → 120 req/min
 *   enterprise  → 300 req/min
 *
 * Super-admins get enterprise-tier limits on all endpoints.
 *
 * The rate limit key combines user ID + plan for isolation, so a plan
 * change takes immediate effect at the next window reset.
 *
 * Response headers added (RFC 6585 / IETF draft):
 *   X-RateLimit-Limit      → window capacity
 *   X-RateLimit-Remaining  → remaining in current window
 *   X-RateLimit-Reset      → Unix timestamp when window resets
 *   Retry-After            → seconds until next allowed request (on 429)
 */
class TenantAwareThrottle
{
    public function __construct(
        protected ThrottleRequests $throttle
    ) {}

    public function handle(Request $request, Closure $next, string $limiterName = 'api'): Response
    {
        $user  = $request->user();
        $plan  = $user?->plan ?? 'free';
        $plan  = $user?->isSuperAdmin() ? 'enterprise' : $plan;

        $limits    = config("security.rate_limits.{$limiterName}", []);
        $perMinute = $limits[$plan] ?? $limits['default'] ?? 60;
        $decayMin  = $limits['decay_minutes'] ?? 1;

        // Unique key: limiter:user_id:plan (or limiter:ip for guests)
        $key = $limiterName . ':' . ($user?->id ? "u{$user->id}:{$plan}" : 'ip:' . $request->ip());

        // Build the Limit and check it
        $limit = Limit::perMinutes($decayMin, $perMinute)->by($key);

        if (RateLimiter::tooManyAttempts($key, $perMinute)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->tooManyAttempts($request, $seconds, $perMinute, $key);
        }

        RateLimiter::hit($key, $decayMin * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $perMinute,
            RateLimiter::remaining($key, $perMinute),
            RateLimiter::availableIn($key)
        );
    }

    protected function addHeaders(Response $response, int $limit, int $remaining, int $retryAfter): Response
    {
        $response->headers->set('X-RateLimit-Limit',     $limit);
        $response->headers->set('X-RateLimit-Remaining', max(0, $remaining));
        $response->headers->set('X-RateLimit-Reset',     now()->addSeconds($retryAfter)->timestamp);

        return $response;
    }

    protected function tooManyAttempts(Request $request, int $retryAfter, int $limit, string $key): Response
    {
        $message = $request->expectsJson()
            ? response()->json([
                'message'     => 'Too many requests. Please slow down.',
                'retry_after' => $retryAfter,
                'limit'       => $limit,
            ], 429)
            : response()->view('errors.429', ['retryAfter' => $retryAfter], 429);

        $message->headers->set('X-RateLimit-Limit',     $limit);
        $message->headers->set('X-RateLimit-Remaining', 0);
        $message->headers->set('Retry-After',            $retryAfter);

        return $message;
    }
}
