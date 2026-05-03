<?php

namespace App\Http\Controllers\Web\Concerns;

use App\Models\Salon;
use App\Models\Staff;
use App\Models\Tenant;
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
        $activeSalonId = (int) session('active_salon_id', 0);

        if ($user->salons()->exists()) {
            $salon = $activeSalonId > 0
                ? $user->salons()->where('id', $activeSalonId)->first()
                : null;

            return $salon ?: $user->salons()->firstOrFail();
        }

        if (Tenant::checkCurrent()) {
            return Salon::query()->withoutGlobalScopes()->findOrFail(Tenant::current()->getKey());
        }

        $staffSalonId = Staff::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->value('salon_id');

        abort_if(! $staffSalonId, 403, 'No salon associated with this account.');

        return Salon::query()->withoutGlobalScopes()->findOrFail($staffSalonId);
    }
}
