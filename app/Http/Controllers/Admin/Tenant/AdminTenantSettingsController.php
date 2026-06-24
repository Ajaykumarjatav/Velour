<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Admin\Concerns\AdminTenantContext;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminTenantSettingsController extends Controller
{
    use AdminTenantContext;

    public function show(int $salon): View
    {
        $salonModel = $this->resolveSalon($salon)->load('settings');
        $this->logTenantView($salonModel, 'settings');

        $settings = $salonModel->settings->pluck('value', 'key');

        return view('admin.tenants.settings.show', [
            'salon' => $salonModel,
            'module' => 'settings',
            'moduleLabel' => 'Settings',
            'settings' => $settings,
        ]);
    }
}
