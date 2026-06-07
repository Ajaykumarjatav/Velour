<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Staff;
use App\Models\Tenant;
use App\Support\StaffJobRoles;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps a staff member's Spatie role aligned with their HR job title (staff.role).
 * Prevents stale roles (e.g. stylist) from granting permissions after matrix changes.
 */
class SyncStaffSpatieRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->isSuperAdmin()) {
            $salonId = (int) (Tenant::current()?->getKey() ?? session('active_salon_id', 0));

            $isOwner = $salonId > 0
                ? $user->ownsSalonId($salonId)
                : $user->salons()->exists();

            if (! $isOwner && ! $user->hasRole('tenant_admin')) {
                $staff = Staff::withoutGlobalScopes()
                    ->where('user_id', $user->id)
                    ->when($salonId > 0, fn ($q) => $q->where('salon_id', $salonId))
                    ->whereNull('deleted_at')
                    ->first();

                if ($staff !== null) {
                    $expectedRole = StaffJobRoles::spatieRoleForJob($staff->role);
                    $currentRoles = $user->getRoleNames();

                    if ($currentRoles->count() !== 1 || ! $user->hasRole($expectedRole)) {
                        $user->syncRoles([$expectedRole]);
                        app(PermissionRegistrar::class)->forgetCachedPermissions();
                    }
                }
            }
        }

        return $next($request);
    }
}
