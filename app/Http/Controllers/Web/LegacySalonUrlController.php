<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\AuthPanel;
use App\Support\SalonUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Send bookmarks of old /dashboard paths to /{store}/dashboard.
 */
class LegacySalonUrlController extends Controller
{
    public function __invoke(Request $request, string $legacy = ''): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && AuthPanel::canAccessSalonPanel($user), 403);

        $key = SalonUrl::keyForUser($user);
        abort_unless($key, 404, 'No salon found for this account.');

        $path = trim($legacy !== '' ? $legacy : $request->path(), '/');
        $target = '/'.$key.($path !== '' ? '/'.$path : '/dashboard');
        $qs = $request->getQueryString();

        return redirect()->to($target.($qs ? '?'.$qs : ''));
    }
}
