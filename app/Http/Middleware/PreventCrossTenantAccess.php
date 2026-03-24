<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PreventCrossTenantAccess
 *
 * The last line of defence against IDOR (Insecure Direct Object Reference)
 * and cross-tenant data leakage.
 *
 * After route model binding resolves models from the URL (e.g. /clients/42),
 * this middleware verifies every resolved model's salon_id matches the current
 * tenant's salon_id from the request attributes.
 *
 * If a mismatch is found:
 *   1. The event is written to audit_logs as security.cross_tenant (critical)
 *   2. The request is rejected with 403 (not 404 — hiding the existence of
 *      the resource would be fine too, but 403 is clearer for debugging)
 *
 * Models that are tenant-scoped must have a salon_id column.
 * Platform-level models (User, Subscription, etc.) are skipped.
 *
 * Applied globally on all authenticated tenant routes.
 */
class PreventCrossTenantAccess
{
    /** Models with a salon_id that must match the current tenant. */
    protected array $tenantScopedModels = [
        \App\Models\Appointment::class,
        \App\Models\Client::class,
        \App\Models\Staff::class,
        \App\Models\Service::class,
        \App\Models\InventoryItem::class,
        \App\Models\MarketingCampaign::class,
        \App\Models\PosTransaction::class,
        \App\Models\Review::class,
    ];

    public function __construct(
        protected AuditLogService $audit
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $salonId = (int) $request->attributes->get('salon_id');

        // No salon context = no cross-tenant check needed
        if (! $salonId) {
            return $next($request);
        }

        // Check every route-bound model parameter
        foreach ($request->route()?->parameters() ?? [] as $param) {
            if (! ($param instanceof Model)) {
                continue;
            }

            if (! in_array(get_class($param), $this->tenantScopedModels, true)) {
                continue;
            }

            $modelSalonId = $param->salon_id ?? null;

            if ($modelSalonId && $modelSalonId !== $salonId) {
                // Audit the cross-tenant attempt
                $this->audit->crossTenantAttempt(
                    class_basename($param),
                    $param->getKey()
                );

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Resource not found.',
                    ], 404); // Return 404 to avoid confirming the resource exists
                }

                abort(404);
            }
        }

        return $next($request);
    }
}
