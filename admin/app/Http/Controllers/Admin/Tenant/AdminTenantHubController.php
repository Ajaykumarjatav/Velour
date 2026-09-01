<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Admin\Concerns\AdminTenantContext;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TenantPlanOverride;
use App\Models\User;
use App\Services\Admin\AdminTenantDataService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminTenantHubController extends Controller
{
    use AdminTenantContext;

    public function __construct(
        private readonly AdminTenantDataService $data,
    ) {}

    public function show(int $salon): View
    {
        $salonModel = $this->resolveSalon($salon)->load(['owner']);
        $salonModel->loadCount(['staff', 'clients', 'appointments', 'services']);

        $this->logTenantView($salonModel, 'hub');

        $owner = $salonModel->owner;
        $hub = $this->data->hubPayload($salonModel);

        $suspensions = DB::table('salon_suspensions')
            ->where('salon_id', $salonModel->id)
            ->orderByDesc('suspended_at')
            ->limit(10)
            ->get();

        $overrides = TenantPlanOverride::where('salon_id', $salonModel->id)
            ->with('appliedBy:id,name')
            ->latest()
            ->get();

        $subscription = $owner?->subscription('default');

        $tickets = SupportTicket::where('salon_id', $salonModel->id)
            ->latest()
            ->limit(5)
            ->get();

        $monthlyRevenue = $hub['monthlyRevenue']->pluck('revenue', 'month');

        return view('admin.tenants.show', [
            'salon' => $salonModel,
            'owner' => $owner,
            'counts' => $hub['counts'],
            'revenueThisMonth' => $hub['revenue']['month'],
            'revenueAllTime' => $hub['revenue']['all_time'],
            'appointmentsThisMonth' => $hub['revenue']['appointments_month'],
            'monthlyRevenue' => $monthlyRevenue,
            'recent' => $hub['recent'],
            'suspensions' => $suspensions,
            'overrides' => $overrides,
            'subscription' => $subscription,
            'tickets' => $tickets,
        ]);
    }
}
