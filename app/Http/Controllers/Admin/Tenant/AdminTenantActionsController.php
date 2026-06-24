<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Admin\Concerns\AdminTenantContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\PosTransaction;
use App\Services\AuditLogService;
use App\Support\DeletedItemsRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminTenantActionsController extends Controller
{
    use AdminTenantContext;

    public function __construct(private readonly AuditLogService $audit) {}

    public function cancelAppointment(Request $request, int $salon, int $appointment): RedirectResponse
    {
        $salonModel = $this->resolveSalon($salon);
        $appt = Appointment::withoutGlobalScopes()->findOrFail($appointment);
        $this->authorizeSalonRecord($salonModel, $appt);

        $request->validate(['reason' => 'nullable|string|max:500']);

        $appt->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason ?? 'Cancelled by platform admin',
        ]);

        $this->audit->admin(
            'admin.tenant.appointment.cancel',
            "Admin cancelled appointment #{$appt->id} for salon '{$salonModel->name}'",
            $appt,
            ['salon_id' => $salonModel->id]
        );

        return back()->with('success', 'Appointment cancelled.');
    }

    public function refundPos(Request $request, int $salon, int $transaction): RedirectResponse
    {
        $salonModel = $this->resolveSalon($salon);
        $tx = PosTransaction::withoutGlobalScopes()->findOrFail($transaction);
        $this->authorizeSalonRecord($salonModel, $tx);

        if ($tx->status === 'refunded') {
            return back()->withErrors(['error' => 'Already refunded.']);
        }

        $tx->update(['status' => 'refunded']);

        $this->audit->admin(
            'admin.tenant.pos.refund',
            "Admin refunded POS #{$tx->id} for salon '{$salonModel->name}'",
            $tx,
            ['salon_id' => $salonModel->id]
        );

        return back()->with('success', 'Transaction marked as refunded.');
    }

    public function restoreDeleted(Request $request, int $salon, string $type, int $id): RedirectResponse
    {
        $salonModel = $this->resolveSalon($salon);
        $types = DeletedItemsRegistry::types();
        abort_unless(isset($types[$type]), 404);

        $modelClass = $types[$type]['model'];
        $item = $modelClass::withoutGlobalScopes()->onlyTrashed()->findOrFail($id);
        $this->authorizeSalonRecord($salonModel, $item);

        $item->restore();

        $this->audit->admin(
            'admin.tenant.deleted.restore',
            "Admin restored {$type} #{$id} for salon '{$salonModel->name}'",
            $item,
            ['salon_id' => $salonModel->id]
        );

        return back()->with('success', 'Item restored.');
    }
}
