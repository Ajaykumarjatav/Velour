<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Support\TenantModuleAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTenantModuleAccessController extends Controller
{
    public function __construct(protected AuditLogService $audit) {}

    public function index(): View
    {
        $disabled = TenantModuleAccess::disabledKeys();
        $groups = [];
        foreach (TenantModuleAccess::modules() as $key => $meta) {
            $groups[$meta['group']][] = [
                'key' => $key,
                'label' => $meta['label'],
                'always_on' => (bool) ($meta['always_on'] ?? false),
                'enabled' => TenantModuleAccess::isEnabled($key),
            ];
        }

        $settingsTabs = [];
        foreach (TenantModuleAccess::settingsTabs() as $key => $label) {
            $settingsTabs[] = [
                'key' => $key,
                'label' => $label,
                'enabled' => TenantModuleAccess::isEnabled($key),
            ];
        }

        return view('admin.tenant-modules.index', [
            'groups' => $groups,
            'settingsTabs' => $settingsTabs,
            'disabledCount' => count($disabled),
            'settingsEnabled' => TenantModuleAccess::isEnabled('settings'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $flags = $request->input('modules', []);
        if (! is_array($flags)) {
            $flags = [];
        }

        TenantModuleAccess::syncFromFlags($flags);

        $this->audit->write(
            'admin',
            'tenant_modules.updated',
            'info',
            'Super admin updated global tenant module access',
            null,
            ['disabled' => TenantModuleAccess::disabledKeys()]
        );

        return back()->with('success', 'Tenant tabs updated. Changes apply to every salon immediately.');
    }
}
