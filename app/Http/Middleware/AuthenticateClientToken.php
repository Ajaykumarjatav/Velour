<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateClientToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken();

        if (! $plain) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($plain);

        if (
            ! $accessToken
            || ! ($accessToken->tokenable instanceof Client)
            || ($accessToken->expires_at && $accessToken->expires_at->isPast())
        ) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $client = $accessToken->tokenable->withAccessToken($accessToken);

        Auth::shouldUse('client');
        Auth::guard('client')->setUser($client);
        $request->setUserResolver(static fn () => $client);

        return $next($request);
    }
}
