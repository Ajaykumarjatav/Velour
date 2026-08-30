<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\AppUrl;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keep generated URLs and login redirects on the same subdirectory the browser used
 * (e.g. /easygrox/admin from APP_URL). Otherwise Laravel emits Location: /login → Apache 404 at
 * http://localhost/login.
 */
class AlignUrlWithRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $base = AppUrl::requestBasePath();
        if ($base !== '') {
            URL::forceRootUrl($request->getSchemeAndHttpHost().$base);
            config(['session.path' => $base]);
        }

        $response = $next($request);

        $location = $response->headers->get('Location');
        if ($base !== '' && is_string($location) && $this->isHostRootLogin($location)) {
            $response->headers->set('Location', AppUrl::login());
        }

        return $response;
    }

    private function isHostRootLogin(string $location): bool
    {
        $path = parse_url($location, PHP_URL_PATH) ?: $location;

        return $location === '/login'
            || $location === 'login'
            || $path === '/login';
    }
}
