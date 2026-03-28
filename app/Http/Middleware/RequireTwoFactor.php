<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RequireTwoFactor
 *
 * After a user passes password authentication, this middleware checks whether
 * their 2FA challenge has been completed for this session.
 *
 * Flow:
 *   1. User logs in with email + password → session flag `two_factor_passed`
 *      is NOT set yet.
 *   2. This middleware detects the missing flag and redirects (web) or returns
 *      403 (API) sending them to the 2FA challenge screen.
 *   3. User submits their TOTP code or email OTP.
 *   4. TwoFactorController sets `two_factor_passed = true` in the session.
 *   5. This middleware now passes them through on subsequent requests.
 *
 * Users without 2FA enabled are passed through immediately.
 * Super-admins who don't have 2FA set up are also passed but shown a nag.
 */
class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            return $next($request);
        }

        // Check if the 2FA challenge has already been passed this session
        if ($request->session()->get('two_factor_passed') === true) {
            return $next($request);
        }

        // 2FA required but not yet completed
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Two-factor authentication required.',
                'code'    => 'TWO_FACTOR_REQUIRED',
            ], 403);
        }

        // Store the intended URL so we can redirect back after 2FA
        session(['url.intended' => $request->url()]);

        return redirect()->route('two-factor.challenge');
    }
}
