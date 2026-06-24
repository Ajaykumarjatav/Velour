<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Admin\Concerns\AdminTenantContext;
use App\Http\Controllers\Controller;
use App\Support\DeletedItemsRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

class AdminTenantDeletedController extends Controller
{
    use AdminTenantContext;

    public function index(int $salon): View
    {
        $salonModel = $this->resolveSalon($salon);
        $this->logTenantView($salonModel, 'deleted.index');

        $groups = [];
        foreach (DeletedItemsRegistry::types() as $key => $config) {
            $modelClass = $config['model'];
            $items = $modelClass::withoutGlobalScopes()
                ->onlyTrashed()
                ->where('salon_id', $salonModel->id)
                ->latest('deleted_at')
                ->limit(20)
                ->get();

            if ($items->isNotEmpty()) {
                $groups[] = [
                    'key' => $key,
                    'label' => $config['plural'],
                    'items' => $items->map(fn (Model $m) => [
                        'id' => $m->id,
                        'name' => $config['name']($m),
                        'deleted_at' => $m->deleted_at,
                    ]),
                ];
            }
        }

        return view('admin.tenants.deleted.index', [
            'salon' => $salonModel,
            'module' => 'deleted',
            'moduleLabel' => 'Deleted items',
            'groups' => $groups,
        ]);
    }
}
