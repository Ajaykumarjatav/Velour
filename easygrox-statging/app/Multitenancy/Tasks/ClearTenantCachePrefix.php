<?php

namespace App\Multitenancy\Tasks;

use Illuminate\Support\Facades\Cache;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

/**
 * ClearTenantCachePrefix
 *
 * Counterpart to SetTenantCachePrefix.  Called when the tenant is forgotten
 * (e.g. at the end of a request in forget_current_tenant_tasks) to restore
 * the default cache prefix and prevent prefix bleed into subsequent work.
 */
class ClearTenantCachePrefix implements SwitchTenantTask
{
    public function makeCurrent(IsTenant $tenant): void
    {
        // Nothing needed on makeCurrent — this task only handles cleanup.
    }

    public function forgetCurrent(): void
    {
        try {
            $store = Cache::getStore();

            if (method_exists($store, 'setPrefix')) {
                $store->setPrefix(config('cache.prefix', 'velour') . ':');
            }
        } catch (\Throwable) {
            // Silently skip
        }
    }
}
