<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureEmailIsVerified
 *
 * Blocks access for users who haven't verified their email address yet.
 *
 * - Web requests  → redirect to a "check your email" notice page
 * - API requests  → 403 JSON with a clear message + resend instructions
 *
 * Super-admins bypass this check because their accounts are seeded with
 * email_verified_at already set.
 */
class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasVerifiedEmail()) {
            return $next($request);
        }

        // Super-admins are always considered verified
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your email address is not verified.',
                'code'    => 'EMAIL_UNVERIFIED',
                'action'  => 'POST ' . route('verification.send') . ' to resend the verification email.',
            ], 403);
        }

        return redirect()->route('verification.notice');
    }
}
