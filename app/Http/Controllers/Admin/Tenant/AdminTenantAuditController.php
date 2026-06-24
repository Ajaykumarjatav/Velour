<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Admin\Concerns\AdminTenantContext;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTenantAuditController extends Controller
{
    use AdminTenantContext;

    public function index(Request $request, int $salon): View
    {
        $salonModel = $this->resolveSalon($salon);
        $this->logTenantView($salonModel, 'audit.index');

        $logs = AuditLog::query()
            ->forTenant($salonModel->id)
            ->when($request->search, fn ($q, $term) => $q->search($term))
            ->orderByDesc('occurred_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.tenants.audit.index', [
            'salon' => $salonModel,
            'module' => 'audit',
            'moduleLabel' => 'Activity log',
            'logs' => $logs,
        ]);
    }
}
