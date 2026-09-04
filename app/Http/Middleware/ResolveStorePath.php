<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\SalonUrl;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve /{store}/… salon panel URLs and sync session + URL defaults.
 */
class ResolveStorePath
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = (string) $request->route('store');
        $salon = SalonUrl::findByKey($key);
        abort_unless($salon, 404, 'Salon not found.');

        $canonical = SalonUrl::key($salon);
        if ($canonical !== '' && strtolower($key) !== $canonical) {
            $path = $request->path();
            $suffix = preg_replace('#^'.preg_quote($key, '#').'#i', $canonical, $path, 1) ?: $canonical;
            $target = url($suffix);
            $query = $request->getQueryString();
            if ($query) {
                $target .= '?'.$query;
            }

            return redirect()->to($target, 301);
        }

        $user = $request->user();
        abort_unless($user && SalonUrl::userCanAccess($user, $salon), 403, 'You do not have access to this salon.');

        if ($request->hasSession()) {
            $request->session()->put('active_salon_id', $salon->id);
        }

        URL::defaults(['store' => $canonical]);

        $tenant = Tenant::query()->withoutGlobalScopes()->find($salon->id);
        if ($tenant) {
            $tenant->makeCurrent();
        }

        $request->attributes->set('store_salon', $salon);
        $request->attributes->set('salon_id', $salon->id);

        // Prevent {store} from being passed into controller actions
        // (…array_values would otherwise shift typed model args → TypeError).
        $request->route()?->forgetParameter('store');

        return $next($request);
    }
}
