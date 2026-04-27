<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->force_password_change) {
            return $next($request);
        }

        if ($request->routeIs('password.force.*') || $request->routeIs('logout') || $request->routeIs('two-factor.*')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Password change required before continuing.',
                'code' => 'PASSWORD_CHANGE_REQUIRED',
            ], 403);
        }

        return redirect()->route('password.force.show');
    }
}
