<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Post-authentication redirects that respect url.intended and panel boundaries.
 */
final class AuthRedirect
{
    public static function afterLoginUrl(Request $request, User $user): string
    {
        $default = AuthPanel::homeUrl($user);

        $intended = $request->session()->pull('url.intended');

        if (! $intended) {
            return $default;
        }

        $intended = AppUrl::absolute($intended);

        if (AuthPanel::canAccessUrl($user, $intended)) {
            return $intended;
        }

        return $default;
    }
}
