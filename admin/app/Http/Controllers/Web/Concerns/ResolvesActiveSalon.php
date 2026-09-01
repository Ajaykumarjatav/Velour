<?php

namespace App\Http\Controllers\Web\Concerns;

use App\Models\Salon;
use App\Models\Staff;
use App\Models\Tenant;
use App\Scopes\TenantScope;
use App\Support\AdminStoreBrowse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait ResolvesActiveSalon
{
    /**
     * Query a tenant-scoped model for the active location without double-applying TenantScope
     * (session active salon may differ from Tenant::current()).
     *
     * @template TModel of Model
     * @param  class-string<TModel>  $modelClass
     * @return Builder<TModel>
     */
    protected function salonScoped(string $modelClass): Builder
    {
        return $modelClass::withoutGlobalScopes()->where('salon_id', $this->activeSalon()->id);
    }

    protected function activeSalon(): Salon
    {
        $user = Auth::user();

        if ($user->isSuperAdmin() && AdminStoreBrowse::isActive()) {
            return Salon::query()->withoutGlobalScopes()->findOrFail(AdminStoreBrowse::salonId());
        }

        $activeSalonId = (int) session('active_salon_id', 0);

        if ($user->salons()->exists()) {
            // Keep this in sync with DomainOrSubdomainTenantFinder::resolveTenantForUser
            // so Tenant::current(), BelongsToTenant scopes, and activeSalon() agree.
            $salon = $activeSalonId > 0
                ? $user->salons()->whereKey($activeSalonId)->first()
                : null;

            return $salon ?? $user->salons()->orderBy('id')->firstOrFail();
        }

        if (Tenant::checkCurrent()) {
            return Salon::query()->withoutGlobalScopes()->findOrFail(Tenant::current()->getKey());
        }

        $activeSalonId = (int) session('active_salon_id', 0);
        if ($activeSalonId > 0) {
            $staffAt = Staff::withoutGlobalScope(TenantScope::class)
                ->where('user_id', $user->id)
                ->where('salon_id', $activeSalonId)
                ->first();
            if ($staffAt) {
                return Salon::query()->withoutGlobalScopes()->findOrFail($staffAt->salon_id);
            }
        }

        $staffSalonId = Staff::withoutGlobalScope(TenantScope::class)
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->value('salon_id');

        abort_if(! $staffSalonId, 403, 'No salon associated with this account.');

        return Salon::query()->withoutGlobalScopes()->findOrFail($staffSalonId);
    }
}
