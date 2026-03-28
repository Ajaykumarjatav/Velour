<?php

namespace App\Http\Controllers\Admin;

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
        $query = Salon::withoutGlobalScopes()
            ->with(['owner:id,name,email,plan'])
            ->withCount(['staff', 'clients', 'appointments'])
            ->latest('salons.created_at');

        // Search
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('salons.name',   'like', "%{$search}%")
                  ->orWhere('slug',        'like', "%{$search}%")
                  ->orWhere('salons.email','like', "%{$search}%")
                  ->orWhere('city',        'like', "%{$search}%");
            });
        }

        // Status filter
        match ($request->status) {
            'active'    => $query->where('is_active', true),
            'suspended' => $query->where('is_active', false),
            default     => null,
        };

        // Plan filter (via owner)
        if ($plan = $request->plan) {
            $query->whereHas('owner', fn ($q) => $q->where('plan', $plan));
        }

        // Sort
        match ($request->sort) {
            'name'       => $query->orderBy('salons.name'),
            'clients'    => $query->orderByDesc('clients_count'),
            'staff'      => $query->orderByDesc('staff_count'),
            'oldest'     => $query->oldest('salons.created_at'),
            default      => $query->latest('salons.created_at'),
        };

        // Summary stats (for the stats strip)
        $stats = [
            'total'     => Salon::withoutGlobalScopes()->count(),
            'active'    => Salon::withoutGlobalScopes()->where('is_active', true)->count(),
            'suspended' => Salon::withoutGlobalScopes()->where('is_active', false)->count(),
            'new_month' => Salon::withoutGlobalScopes()->whereMonth('created_at', now()->month)->count(),
        ];

        $planOptions = ['free', 'starter', 'pro', 'enterprise'];

        $tenants = $query->paginate(30)->withQueryString();

        return view('admin.tenants.index', compact('tenants', 'stats', 'planOptions'));
    }

    // ── Tenant Detail ─────────────────────────────────────────────────────────

    public function show(int $id)
    {
        $salon = Salon::withoutGlobalScopes()
            ->with(['owner', 'staff', 'services'])
            ->withCount(['staff', 'clients', 'appointments', 'services'])
            ->findOrFail($id);

        $owner = $salon->owner;

        // Usage stats
        $appointmentsThisMonth = DB::table('appointments')
            ->where('salon_id', $id)
            ->whereMonth('created_at', now()->month)
            ->count();

        $revenueThisMonth = DB::table('pos_transactions')
            ->where('salon_id', $id)
            ->whereMonth('created_at', now()->month)
            ->where('status', 'completed')
            ->sum('total');

        $revenueAllTime = DB::table('pos_transactions')
            ->where('salon_id', $id)
            ->where('status', 'completed')
            ->sum('total');

        // Monthly revenue for last 6 months
        $monthlyRevenue = DB::table('pos_transactions')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('SUM(total) as revenue'))
            ->where('salon_id', $id)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('revenue', 'month');

        // Suspension history
        $suspensions = DB::table('salon_suspensions')
            ->where('salon_id', $id)
            ->orderByDesc('suspended_at')
            ->get();

        // Plan overrides
        $overrides = TenantPlanOverride::where('salon_id', $id)
            ->with('appliedBy:id,name')
            ->latest()
            ->get();

        // Subscription from Cashier
        $subscription = $owner?->subscription('default');

        // Recent support tickets
        $tickets = \App\Models\SupportTicket::where('salon_id', $id)
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.tenants.show', compact(
            'salon', 'owner', 'appointmentsThisMonth', 'revenueThisMonth',
            'revenueAllTime', 'monthlyRevenue', 'suspensions', 'overrides',
            'subscription', 'tickets'
        ));
    }

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

        // Notify owner
        if ($request->boolean('notify_owner', true) && $salon->owner) {
            $salon->owner->notify(new TenantSuspendedNotification(
                $salon, $request->reason, $request->customer_message
            ));
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

        $salon = Salon::withoutGlobalScopes()->findOrFail($id);

        if ($salon->is_active) {
            return back()->withErrors(['error' => 'This salon is already active.']);
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
            $salon->owner->notify(new TenantUnsuspendedNotification(
                $salon, $request->customer_message
            ));
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
            'override_plan'           => 'nullable|in:free,starter,pro,enterprise',
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
            fputcsv($h, ['ID','Name','Slug','City','Owner','Email','Plan','Staff','Clients','Appointments','Status','Created']);
            foreach ($salons as $s) {
                fputcsv($h, [
                    $s->id, $s->name, $s->slug, $s->city ?? '',
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
