<?php

namespace App\Scopes;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * TenantScope
 *
 * A global Eloquent scope that automatically appends a `WHERE salon_id = ?`
 * clause to every query on any model that uses the `BelongsToTenant` trait.
 *
 * This ensures that — once a tenant is resolved for a request — all database
 * reads are automatically filtered to that tenant's data, preventing any
 * accidental cross-tenant data leakage.
 *
 * If no tenant is currently set (e.g. during artisan commands running in
 * landlord context, or unauthenticated public routes) the scope is silently
 * skipped so those contexts can still query freely when needed.
 */
class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder  $builder
     * @param  Model    $model
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Only add the constraint when there IS an active tenant.
        if (! Tenant::checkCurrent()) {
            return;
        }

        $tenantId = Tenant::current()->getKey();

        $builder->where(
            $model->qualifyColumn('salon_id'),
            '=',
            $tenantId
        );
    }

    /**
     * Extend the query builder with a macro that intentionally bypasses the
     * tenant scope — useful for super-admin / landlord queries.
     *
     * Usage:  Model::withoutTenantScope()->get();
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenantScope', function (Builder $builder) {
            return $builder->withoutGlobalScope(TenantScope::class);
        });
    }
}
