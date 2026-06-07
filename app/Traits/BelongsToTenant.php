<?php

namespace App\Traits;

use App\Models\Tenant;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;

/**
 * BelongsToTenant
 *
 * Add this trait to every Eloquent model whose rows belong to a salon/tenant.
 *
 * What it does automatically:
 *
 *  1. GLOBAL SCOPE — attaches TenantScope on boot, so every SELECT query
 *     automatically includes `WHERE salon_id = <current_tenant_id>`.
 *
 *  2. AUTO-FILL — on the `creating` Eloquent event, sets `salon_id` from the
 *     active Tenant if the attribute is not already set, so you never need to
 *     manually pass `salon_id` when creating records inside a tenant context.
 *
 *  3. ESCAPE HATCH — adds a `withoutTenantScope()` query macro so super-admin
 *     code can bypass the scope when needed.
 *
 * Usage:
 *   class Appointment extends Model {
 *       use BelongsToTenant;
 *   }
 *
 * Bypass (landlord context / artisan):
 *   Appointment::withoutTenantScope()->get();
 *   Appointment::withoutGlobalScope(TenantScope::class)->get();
 */
trait BelongsToTenant
{
    /**
     * Boot the trait — register global scope and creating listener.
     */
    public static function bootBelongsToTenant(): void
    {
        // 1. Register the global WHERE salon_id = ? scope.
        static::addGlobalScope(new TenantScope());

        // 2. Auto-fill salon_id on new records.
        static::creating(function (self $model): void {
            // Only auto-fill if the attribute is not already explicitly set.
            if (empty($model->salon_id) && Tenant::checkCurrent()) {
                $model->salon_id = Tenant::current()->getKey();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scope Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Bypass the tenant scope for this query.
     *
     * Convenience alias for withoutGlobalScope(TenantScope::class).
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }

    /**
     * Scope to a specific tenant (useful in artisan commands / admin panels).
     *
     * Usage:
     *   Appointment::forTenant($salon)->get();
     *
     * @param  Builder  $query
     * @param  int|\App\Models\Tenant  $tenant
     * @return Builder
     */
    public function scopeForTenant(Builder $query, int|\App\Models\Tenant $tenant): Builder
    {
        $id = $tenant instanceof \App\Models\Tenant ? $tenant->getKey() : $tenant;

        return $query
            ->withoutGlobalScope(TenantScope::class)
            ->where($this->qualifyColumn('salon_id'), $id);
    }

    /*
    |--------------------------------------------------------------------------
    | Relation
    |--------------------------------------------------------------------------
    */

    /**
     * Relation back to the owning Tenant (Salon).
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'salon_id');
    }
}
