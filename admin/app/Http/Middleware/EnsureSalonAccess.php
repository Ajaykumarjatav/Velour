<?php

namespace App\Http\Middleware;

use App\Models\Salon;
use App\Models\Staff;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the salon for the authenticated user and injects salon_id,
 * access_level, and staff_id into request attributes.
 * Cached for 5 minutes to reduce DB hits on every request.
 */
class EnsureSalonAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Your account has been suspended.'], 403);
        }

        $activeSalonId = (int) session('active_salon_id', 0);
        // Include active location in the cache key so multi-location owners get the correct salon_id per branch.
        $cacheKey = "salon_access_user_{$user->id}_{$activeSalonId}";

        $access = Cache::remember($cacheKey, 300, function () use ($user, $activeSalonId) {
            // Owner check — prefer session active branch when the user owns it (matches web ResolvesActiveSalon).
            $baseOwned = fn () => Salon::query()->where('owner_id', $user->id)->where('is_active', true);

            $salon = $activeSalonId > 0
                ? $baseOwned()->where('id', $activeSalonId)->first()
                : null;
            if (! $salon) {
                $salon = $baseOwned()->orderBy('id')->first();
            }

            if ($salon) {
                return ['salon_id' => $salon->id, 'access_level' => 'owner', 'staff_id' => null];
            }

            // Staff member check
            $staff = Staff::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereHas('salon', fn ($q) => $q->where('is_active', true))
                ->first();

            if ($staff) {
                return ['salon_id' => $staff->salon_id, 'access_level' => $staff->access_level, 'staff_id' => $staff->id];
            }

            return null;
        });

        if (! $access) {
            return response()->json(['message' => 'No active salon associated with this account.'], 403);
        }

        $request->attributes->set('salon_id',     $access['salon_id']);
        $request->attributes->set('access_level', $access['access_level']);
        $request->attributes->set('staff_id',     $access['staff_id']);

        return $next($request);
    }
}
