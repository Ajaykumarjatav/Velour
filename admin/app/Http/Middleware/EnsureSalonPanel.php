<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\AuthPanel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Salon app routes are for tenant owners and staff — not platform super-admins
 * (unless impersonating a salon user).
 */
class EnsureSalonPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active && ! AuthPanel::isAdminStoreBrowse()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your account has been suspended.'], 403);
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been suspended. Please contact support.']);
        }

        if (! $user || AuthPanel::canAccessSalonPanel($user)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Salon panel access required.'], 403);
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('info', 'Platform admin accounts use the admin panel. Impersonate a salon user to access their dashboard.');
    }
}
