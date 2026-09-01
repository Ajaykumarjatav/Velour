<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Support\DeletedItemsRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeletedItemsController extends Controller
{
    use ResolvesActiveSalon;

    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless(DeletedItemsRegistry::userCanAccessTrash($user), 403);

        $salon = $this->activeSalon();
        $allItems = DeletedItemsRegistry::itemsForSalon((int) $salon->id, $user);
        $typeCounts = $allItems->groupBy('type')->map->count();
        $filter = $request->get('type');

        $items = $allItems;
        if (is_string($filter) && $filter !== '') {
            $items = $allItems->where('type', $filter)->values();
        }

        return view('deleted-items.index', [
            'items' => $items,
            'typeCounts' => $typeCounts,
            'filter' => $filter,
            'types' => DeletedItemsRegistry::types(),
        ]);
    }

    public function restore(Request $request, string $type, int $id)
    {
        $user = Auth::user();
        abort_unless(DeletedItemsRegistry::userCanRestoreType($user, $type), 403);

        $config = DeletedItemsRegistry::type($type);
        abort_if($config === null, 404);

        $modelClass = $config['model'];
        $model = $modelClass::withoutGlobalScopes()
            ->onlyTrashed()
            ->where('salon_id', $this->activeSalon()->id)
            ->whereKey($id)
            ->firstOrFail();

        $model->restore();

        return back()->with('success', $config['label'].' restored successfully.');
    }

    public function forceDestroy(Request $request, string $type, int $id)
    {
        $user = Auth::user();
        abort_unless(DeletedItemsRegistry::userCanForceDelete($user), 403);

        $config = DeletedItemsRegistry::type($type);
        abort_if($config === null, 404);

        $modelClass = $config['model'];
        $model = $modelClass::withoutGlobalScopes()
            ->onlyTrashed()
            ->where('salon_id', $this->activeSalon()->id)
            ->whereKey($id)
            ->firstOrFail();

        $model->forceDelete();

        return back()->with('success', $config['label'].' permanently deleted.');
    }
}
