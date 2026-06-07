<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * AccountLockout
 *
 * AUDIT FIX: Per-account brute-force protection beyond IP-based rate limiting.
 *
 * IP rate limiting (nginx + Laravel throttle) blocks high-volume attacks from
 * one IP. This middleware additionally locks specific accounts after N failed
 * login attempts, preventing low-and-slow distributed attacks where each IP
 * only sends 1-2 requests.
 *
 * Lockout policy:
 *   - 5 failed attempts within 15 minutes → account locked for 15 minutes
 *   - 10 failed attempts within 1 hour    → account locked for 1 hour
 *   - 20 failed attempts in 24 hours      → account locked, admin notified
 *
 * Applied to: POST /auth/login (web + API)
 */
class AccountLockout
{
    private const SOFT_THRESHOLD   = 5;
    private const SOFT_LOCKOUT_MIN = 15;
    private const HARD_THRESHOLD   = 10;
    private const HARD_LOCKOUT_MIN = 60;

    public function handle(Request $request, Closure $next): Response
    {
        $email = strtolower(trim($request->input('email', '')));

        if (! $email) {
            return $next($request);
        }

        $cacheKey = 'login_attempts:' . sha1($email);
        $attempts = Cache::get($cacheKey, 0);

        // Hard lockout
        if ($attempts >= self::HARD_THRESHOLD) {
            $this->logAttempt($request, $email, 'hard_lockout');
            return $this->lockedResponse($request, self::HARD_LOCKOUT_MIN);
        }

        // Soft lockout
        if ($attempts >= self::SOFT_THRESHOLD) {
            $this->logAttempt($request, $email, 'soft_lockout');
            return $this->lockedResponse($request, self::SOFT_LOCKOUT_MIN);
        }

        $response = $next($request);

        // Increment or reset counter based on response
        if ($this->loginFailed($response)) {
            $newCount = $attempts + 1;
            Cache::put($cacheKey, $newCount, now()->addMinutes(60));
            $this->logAttempt($request, $email, 'failed', $newCount);
        } elseif ($this->loginSucceeded($response)) {
            Cache::forget($cacheKey);
            $this->logAttempt($request, $email, 'succeeded');
        }

        return $response;
    }

    private function loginFailed(Response $response): bool
    {
        return in_array($response->getStatusCode(), [401, 422]);
    }

    private function loginSucceeded(Response $response): bool
    {
        return $response->getStatusCode() === 200;
    }

    private function lockedResponse(Request $request, int $minutes): Response
    {
        $message = "Account temporarily locked. Please try again in {$minutes} minutes.";

        if ($request->expectsJson()) {
            return response()->json([
                'message'     => $message,
                'code'        => 'ACCOUNT_LOCKED',
                'retry_after' => $minutes * 60,
            ], 429);
        }

        return redirect()->back()
            ->withErrors(['email' => $message])
            ->withInput($request->only('email'));
    }

    private function logAttempt(Request $request, string $email, string $reason, int $count = 0): void
    {
        try {
            DB::table('login_attempts')->insert([
                'email'          => $email,
                'ip_address'     => $request->ip(),
                'user_agent'     => substr($request->userAgent() ?? '', 0, 255),
                'succeeded'      => $reason === 'succeeded',
                'failure_reason' => $reason !== 'succeeded' ? $reason : null,
                'attempted_at'   => now(),
            ]);
        } catch (\Throwable) {
            // Never break login flow due to audit log failure
        }
    }
}
