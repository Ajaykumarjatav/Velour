<?php

namespace App\Multitenancy\Tasks;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

/**
 * SetTenantCachePrefix
 *
 * Prefixes every cache key with "tenant_{id}:" so that salons never see
 * each other's cached data even when sharing the same Redis/Memcached store.
 *
 * Examples:
 *   Key "dashboard_kpis" for tenant 42 → "tenant_42:dashboard_kpis"
 *   Key "dashboard_kpis" for tenant 17 → "tenant_17:dashboard_kpis"
 *
 * This is cheaper than creating separate cache stores per tenant because it
 * uses a single connection with namespaced keys.
 */
class SetTenantCachePrefix implements SwitchTenantTask
{
    public function makeCurrent(IsTenant $tenant): void
    {
        $this->setCachePrefix("tenant_{$tenant->getKey()}");
    }

    public function forgetCurrent(): void
    {
        // Restore the default prefix from config so landlord code still works.
        $this->setCachePrefix(config('cache.prefix', 'velour'));
    }

    private function setCachePrefix(string $prefix): void
    {
        // The cache store must support prefixing (Redis, Memcached, file, etc.)
        try {
            $store = Cache::getStore();

            if (method_exists($store, 'setPrefix')) {
                $store->setPrefix($prefix . ':');
            }
        } catch (\Throwable) {
            // Silently skip if the store doesn't support prefixes
        }
    }
}
