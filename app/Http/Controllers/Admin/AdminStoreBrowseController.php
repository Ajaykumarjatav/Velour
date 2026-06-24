<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Services\AuditLogService;
use App\Support\AdminStoreBrowse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminStoreBrowseController extends Controller
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function enter(Request $request, int $salon): RedirectResponse
    {
        $salonModel = Salon::withoutGlobalScopes()->with('owner')->findOrFail($salon);

        abort_unless($salonModel->owner_id, 404);
        abort_if(
            ! $salonModel->owner || ! $salonModel->owner->is_active,
            403,
            'This tenant account is blocked. Store preview is not available.'
        );
        abort_unless(
            $salonModel->is_active,
            403,
            'This store is suspended. Store preview is not available.'
        );

        AdminStoreBrowse::start(
            (int) $salonModel->id,
            (int) $salonModel->owner_id,
            (string) $salonModel->name
        );

        $this->audit->admin(
            'admin.store_browse.enter',
            "Admin opened read-only store panel for '{$salonModel->name}' (#{$salonModel->id})",
            $salonModel,
            ['salon_id' => $salonModel->id, 'owner_id' => $salonModel->owner_id]
        );

        return redirect()
            ->route('dashboard')
            ->with('info', "Viewing {$salonModel->name} in read-only mode.");
    }

    public function exit(Request $request): RedirectResponse
    {
        $browse = AdminStoreBrowse::session();
        $ownerId = $browse['owner_id'] ?? null;

        if ($browse) {
            $this->audit->admin(
                'admin.store_browse.exit',
                "Admin left read-only store panel for '{$browse['salon_name']}' (#{$browse['salon_id']})",
                null,
                ['salon_id' => $browse['salon_id'], 'owner_id' => $browse['owner_id']]
            );
        }

        AdminStoreBrowse::clear();

        if ($ownerId) {
            return redirect()
                ->route('admin.tenants.stores', $ownerId)
                ->with('success', 'Returned to admin.');
        }

        return redirect()
            ->route('admin.tenants')
            ->with('success', 'Returned to admin.');
    }
}
