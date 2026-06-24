<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\User;
use App\Services\Admin\TenantBlockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminTenantStoresController extends Controller
{
    public function __construct(private readonly TenantBlockService $blockService) {}

    public function index(Request $request, int $owner): View
    {
        $account = User::query()
            ->whereHas('salons')
            ->findOrFail($owner);

        $stores = Salon::withoutGlobalScopes()
            ->where('owner_id', $account->id)
            ->withCount(['staff', 'clients', 'appointments'])
            ->orderBy('name')
            ->get();

        $salonIds = $stores->pluck('id');
        $revenueBySalon = collect();

        if ($salonIds->isNotEmpty()) {
            $revenueBySalon = DB::table('pos_transactions')
                ->select('salon_id', DB::raw('COALESCE(SUM(total), 0) as revenue'))
                ->whereIn('salon_id', $salonIds)
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->startOfMonth())
                ->groupBy('salon_id')
                ->pluck('revenue', 'salon_id');
        }

        if ($stores->count() === 1 && $request->boolean('auto', true) && ! $request->boolean('list')) {
            return view('admin.tenants.stores.index', [
                'account' => $account,
                'stores' => $stores,
                'revenueBySalon' => $revenueBySalon,
                'autoOpenSalon' => $stores->first(),
                'isBlocked' => $this->blockService->isBlocked($account),
            ]);
        }

        return view('admin.tenants.stores.index', [
            'account' => $account,
            'stores' => $stores,
            'revenueBySalon' => $revenueBySalon,
            'autoOpenSalon' => null,
            'isBlocked' => $this->blockService->isBlocked($account),
        ]);
    }
}
