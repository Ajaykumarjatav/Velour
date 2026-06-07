<?php

namespace App\Http\Middleware;

use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientPortalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof Client) {
            return response()->json(['message' => 'Please log in to continue.'], 401);
        }

        if ($user->status === 'blocked' || $user->status === 'erased') {
            return response()->json(['message' => 'This account is not available.'], 403);
        }

        return $next($request);
    }
}
