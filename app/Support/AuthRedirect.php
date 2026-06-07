<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Post-authentication redirects that respect url.intended without sending
 * platform super-admins to tenant-only routes (they have no salon tenant).
 */
final class AuthRedirect
{
    public static function afterLoginUrl(Request $request, User $user): string
    {
        $default = $user->isSuperAdmin()
            ? route('admin.dashboard')
            : route('dashboard');

        $intended = $request->session()->pull('url.intended');

        if (! $intended) {
            return $default;
        }

        $intended = AppUrl::absolute($intended);

        if ($user->isSuperAdmin()) {
            $path = parse_url($intended, PHP_URL_PATH) ?? '';

            if (str_contains($path, '/admin')) {
                return $intended;
            }

            return $default;
        }

        return $intended;
    }
}
