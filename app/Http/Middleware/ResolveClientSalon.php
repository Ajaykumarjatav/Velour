<?php

namespace App\Http\Middleware;

use App\Models\Client;
use App\Models\Salon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveClientSalon
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('salonSlug');
        $salon = Salon::where('slug', $slug)->where('is_active', true)->first();

        if (! $salon) {
            return response()->json(['message' => 'Salon not found.'], 404);
        }

        $request->attributes->set('salon', $salon);
        $request->attributes->set('salon_id', $salon->id);

        $user = $request->user();
        if ($user instanceof Client && (int) $user->salon_id !== (int) $salon->id) {
            return response()->json(['message' => 'This account does not belong to this salon.'], 403);
        }

        return $next($request);
    }
}
