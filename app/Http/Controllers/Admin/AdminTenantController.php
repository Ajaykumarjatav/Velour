<?php

namespace App\Http\Controllers\Admin;

use App\Billing\Plan;
use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\TenantPlanOverride;
use App\Models\User;
use App\Notifications\Admin\TenantSuspendedNotification;
use App\Notifications\Admin\TenantUnsuspendedNotification;
use App\Notifications\Admin\PlanOverrideNotification;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * AdminTenantController
 *
 * Full super-admin tenant management:
 *   • Paginated, searchable, filterable tenant list with plan + revenue columns
 *   • Per-tenant detail with usage stats, revenue, suspension history, plan overrides
 *   • Suspend / unsuspend with reason, internal notes, customer-facing message
 *   • Plan overrides (grant custom plan, extend trial, add feature flags)
 *   • Bulk suspend / unsuspend
 *   • CSV export
 *
 * Routes prefix: /admin/tenants
 * Guard: auth, verified, 2fa, super_admin
 */
class AdminTenantController extends Controller
{
    public function __construct(protected AuditLogService $audit) {}

    // ── Tenant List ───────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = User::query()
            ->whereHas('salons', fn ($q) => $q->withoutGlobalScopes())
            ->withCount(['salons as stores_count'])
            ->with(['salons' => fn ($q) => $q->withoutGlobalScopes()->select('id', 'owner_id', 'name', 'is_active')]);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('salons', fn ($s) => $s->withoutGlobalScopes()
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%"));
            });
        }

        if ($plan = $request->plan) {
            $query->where('plan', $plan);
        }

        if ($request->status === 'active') {
            $query->where('is_active', true)
                ->whereHas('salons', fn ($q) => $q->withoutGlobalScopes()->where('is_active', true));
        } elseif ($request->status === 'blocked') {
            $query->where('is_active', false);
        } elseif ($request->status === 'suspended') {
            $query->where('is_active', true)
                ->whereHas('salons', fn ($q) => $q->withoutGlobalScopes()->where('is_active', false))
                ->whereDoesntHave('salons', fn ($q) => $q->withoutGlobalScopes()->where('is_active', true));
        }

        match ($request->sort) {
            'name' => $query->orderBy('name'),
            'stores' => $query->orderByDesc('stores_count'),
            'oldest' => $query->oldest('created_at'),
            default => $query->latest('created_at'),
        };

        $stats = [
            'total'     => User::whereHas('salons', fn ($q) => $q->withoutGlobalScopes())->count(),
            'blocked'   => User::whereHas('salons', fn ($q) => $q->withoutGlobalScopes())->where('is_active', false)->count(),
            'active'    => Salon::withoutGlobalScopes()->where('is_active', true)->distinct('owner_id')->count('owner_id'),
            'suspended' => Salon::withoutGlobalScopes()->where('is_active', false)->count(),
            'new_month' => User::whereHas('salons', fn ($q) => $q->withoutGlobalScopes()->whereMonth('salons.created_at', now()->month))->count(),
        ];

        $planOptions = Plan::keys();

        $ownerIds = (clone $query)->pluck('id');
        $aggregateStats = collect();

        if ($ownerIds->isNotEmpty()) {
            $aggregateStats = DB::table('salons')
                ->leftJoin('clients', 'clients.salon_id', '=', 'salons.id')
                ->leftJoin('appointments', 'appointments.salon_id', '=', 'salons.id')
                ->whereIn('salons.owner_id', $ownerIds)
                ->whereNull('salons.deleted_at')
                ->groupBy('salons.owner_id')
                ->select(
                    'salons.owner_id',
                    DB::raw('COUNT(DISTINCT clients.id) as clients_total'),
                    DB::raw('COUNT(DISTINCT appointments.id) as appointments_total'),
                    DB::raw('SUM(CASE WHEN salons.is_active = 1 THEN 1 ELSE 0 END) as active_stores')
                )
                ->get()
                ->keyBy('owner_id');
        }

        $accounts = $query->paginate(30)->withQueryString();

        return view('admin.tenants.index', [
            'accounts' => $accounts,
            'aggregateStats' => $aggregateStats,
            'stats' => $stats,
            'planOptions' => $planOptions,
        ]);
    }

    // ── Tenant Detail (moved to AdminTenantHubController) ─────────────────────

    // ── Suspend ───────────────────────────────────────────────────────────────

    public function suspend(Request $request, int $id)
    {
        $request->validate([
            'reason'           => 'required|in:payment_failure,policy_violation,fraud,abuse,requested,other',
            'notes'            => 'nullable|string|max:2000',
            'customer_message' => 'nullable|string|max:1000',
        ]);

        $salon = Salon::withoutGlobalScopes()->findOrFail($id);

        if (! $salon->is_active) {
            return back()->withErrors(['error' => 'This salon is already suspended.']);
        }

        DB::transaction(function () use ($salon, $request) {
            $salon->update([
                'is_active'          => false,
                'suspension_reason'  => $request->reason,
                'suspended_at'       => now(),
                'suspended_by'       => Auth::id(),
            ]);

            DB::table('salon_suspensions')->insert([
                'salon_id'         => $salon->id,
                'suspended_by'     => Auth::id(),
                'reason'           => $request->reason,
                'notes'            => $request->notes,
                'customer_message' => $request->customer_message,
                'suspended_at'     => now(),
            ]);
        });

        // Notify owner (non-blocking — SMTP misconfig must not roll back suspend)
        if ($request->boolean('notify_owner', true) && $salon->owner) {
            try {
                $salon->owner->notify(new TenantSuspendedNotification(
                    $salon, $request->reason, $request->customer_message
                ));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[TenantSuspend] Notification failed', ['error' => $e->getMessage()]);
            }
        }

        $this->audit->admin('admin.tenant.suspend',
            "Suspended salon '{$salon->name}' (#{$salon->id}) — reason: {$request->reason}",
            $salon,
            ['reason' => $request->reason]
        );

        return back()->with('success', "'{$salon->name}' has been suspended.");
    }

    // ── Unsuspend ─────────────────────────────────────────────────────────────

    public function unsuspend(Request $request, int $id)
    {
        $request->validate([
            'unsuspend_reason'  => 'nullable|string|max:500',
            'customer_message'  => 'nullable|string|max:1000',
        ]);

        $salon = Salon::withoutGlobalScopes()->with('owner')->findOrFail($id);

        if ($salon->is_active) {
            return back()->withErrors(['error' => 'This salon is already active.']);
        }

        if ($salon->owner && ! $salon->owner->is_active) {
            return back()->withErrors(['error' => 'Unblock the tenant account before reactivating individual stores.']);
        }

        DB::transaction(function () use ($salon, $request) {
            $salon->update([
                'is_active'         => true,
                'suspension_reason' => null,
                'suspended_at'      => null,
                'suspended_by'      => null,
            ]);

            DB::table('salon_suspensions')
                ->where('salon_id', $salon->id)
                ->whereNull('unsuspended_at')
                ->update([
                    'unsuspended_at' => now(),
                    'unsuspended_by' => Auth::id(),
                    'unsuspend_reason' => $request->unsuspend_reason,
                ]);
        });

        if ($request->boolean('notify_owner', true) && $salon->owner) {
            try {
                $salon->owner->notify(new TenantUnsuspendedNotification(
                    $salon, $request->customer_message
                ));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[TenantUnsuspend] Notification failed', ['error' => $e->getMessage()]);
            }
        }

        $this->audit->admin('admin.tenant.unsuspend',
            "Unsuspended salon '{$salon->name}' (#{$salon->id})",
            $salon
        );

        return back()->with('success', "'{$salon->name}' has been reinstated.");
    }

    // ── Bulk Suspend ──────────────────────────────────────────────────────────

    public function bulkSuspend(Request $request)
    {
        $request->validate([
            'salon_ids'        => 'required|array|min:1|max:100',
            'salon_ids.*'      => 'integer|exists:salons,id',
            'reason'           => 'required|in:payment_failure,policy_violation,fraud,abuse,requested,other',
            'notify_owners'    => 'nullable|boolean',
        ]);

        $count = 0;
        Salon::withoutGlobalScopes()
            ->whereIn('id', $request->salon_ids)
            ->where('is_active', true)
            ->each(function (Salon $salon) use ($request, &$count) {
                $salon->update([
                    'is_active'         => false,
                    'suspension_reason' => $request->reason,
                    'suspended_at'      => now(),
                    'suspended_by'      => Auth::id(),
                ]);
                DB::table('salon_suspensions')->insert([
                    'salon_id'     => $salon->id,
                    'suspended_by' => Auth::id(),
                    'reason'       => $request->reason,
                    'suspended_at' => now(),
                ]);
                if ($request->boolean('notify_owners') && $salon->owner) {
                    $salon->owner->notify(new TenantSuspendedNotification($salon, $request->reason));
                }
                $count++;
            });

        $this->audit->admin('admin.tenant.bulk_suspend',
            "Bulk suspended {$count} salons — reason: {$request->reason}"
        );

        return back()->with('success', "{$count} salon(s) suspended.");
    }

    // ── Plan Override ─────────────────────────────────────────────────────────

    public function applyPlanOverride(Request $request, int $id)
    {
        $request->validate([
            'override_type'           => 'required|in:plan,trial_extension,custom_limit,discount,feature_flag',
            'override_plan'           => 'nullable|'.Plan::validationRule(),
            'override_staff_limit'    => 'nullable|integer|min:-1',
            'override_client_limit'   => 'nullable|integer|min:-1',
            'override_services_limit' => 'nullable|integer|min:-1',
            'additional_features'     => 'nullable|array',
            'additional_features.*'   => 'string|max:50',
            'trial_extension_days'    => 'nullable|integer|min:1|max:365',
            'discount_percentage'     => 'nullable|integer|min:1|max:100',
            'reason'                  => 'required|string|max:500',
            'expires_at'              => 'nullable|date|after:today',
        ]);

        $salon = Salon::withoutGlobalScopes()->findOrFail($id);

        // Deactivate previous overrides of the same type
        TenantPlanOverride::where('salon_id', $id)
            ->where('override_type', $request->override_type)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $override = TenantPlanOverride::create([
            'salon_id'                => $id,
            'applied_by'              => Auth::id(),
            'override_type'           => $request->override_type,
            'override_plan'           => $request->override_plan,
            'override_staff_limit'    => $request->override_staff_limit,
            'override_client_limit'   => $request->override_client_limit,
            'override_services_limit' => $request->override_services_limit,
            'additional_features'     => $request->additional_features,
            'trial_extension_days'    => $request->trial_extension_days,
            'discount_percentage'     => $request->discount_percentage,
            'reason'                  => $request->reason,
            'expires_at'              => $request->expires_at,
            'is_active'               => true,
        ]);

        // Sync the owner's plan column if a plan override was applied
        if ($request->override_type === 'plan' && $request->override_plan && $salon->owner) {
            $salon->owner->update(['plan' => $request->override_plan]);
        }

        // Notify owner
        if ($salon->owner) {
            $salon->owner->notify(new PlanOverrideNotification($salon, $override));
        }

        $this->audit->admin('admin.tenant.plan_override',
            "Applied {$request->override_type} override to '{$salon->name}'",
            $salon,
            ['override_type' => $request->override_type, 'plan' => $request->override_plan]
        );

        return back()->with('success', 'Plan override applied successfully.');
    }

    public function revokeOverride(int $salonId, int $overrideId)
    {
        $override = TenantPlanOverride::where('salon_id', $salonId)->findOrFail($overrideId);
        $override->update(['is_active' => false]);

        $this->audit->admin('admin.tenant.override_revoked',
            "Revoked override #{$overrideId} for salon #{$salonId}"
        );

        return back()->with('success', 'Override revoked.');
    }

    // ── Domain Update ─────────────────────────────────────────────────────────

    public function updateDomain(Request $request, int $id)
    {
        $salon = Salon::withoutGlobalScopes()->findOrFail($id);

        $data = $request->validate([
            'domain' => 'nullable|string|max:253|unique:salons,domain,' . $id,
            'slug'   => 'nullable|string|max:63|alpha_dash|unique:salons,slug,' . $id,
        ]);

        $salon->update($data);

        return back()->with('success', 'Domain settings updated.');
    }

    // ── CSV Export ────────────────────────────────────────────────────────────

    public function export(Request $request)
    {
        $this->audit->data('data.export', 'Admin exported tenant list CSV');

        $salons = Salon::withoutGlobalScopes()
            ->with('owner:id,name,email,plan')
            ->withCount(['staff', 'clients', 'appointments'])
            ->get();

        $headers = ['Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="tenants-' . now()->format('Y-m-d') . '.csv"'];

        $callback = function () use ($salons) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['Name','Slug','City','Owner','Email','Plan','Staff','Clients','Appointments','Status','Created']);
            foreach ($salons as $s) {
                fputcsv($h, [
                    $s->name, $s->slug, $s->city ?? '',
                    $s->owner?->name, $s->owner?->email, $s->owner?->plan,
                    $s->staff_count, $s->clients_count, $s->appointments_count,
                    $s->is_active ? 'Active' : 'Suspended',
                    $s->created_at->toDateString(),
                ]);
            }
            fclose($h);
        };

        return response()->stream($callback, 200, $headers);
    }
}
