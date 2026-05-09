<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\SalonActionItem;
use App\Support\SalonTime;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SalonActionItemController extends Controller
{
    use ResolvesActiveSalon;

    public function store(Request $request): RedirectResponse
    {
        $salon = $this->activeSalon();
        $user = Auth::user();
        $scopedStaffId = $user->dashboardScopedStaffId();
        $canAssign = $scopedStaffId === null;

        $kindRules = $scopedStaffId !== null
            ? ['required', 'in:'.SalonActionItem::KIND_STAFF_SUGGESTION.','.SalonActionItem::KIND_INVENTORY_REQUEST.','.SalonActionItem::KIND_GENERAL]
            : ['required', 'in:'.SalonActionItem::KIND_ADMIN_TODO.','.SalonActionItem::KIND_STAFF_SUGGESTION.','.SalonActionItem::KIND_INVENTORY_REQUEST.','.SalonActionItem::KIND_GENERAL];

        $rules = [
            'kind' => $kindRules,
            'title' => ['required', 'string', 'max:200'],
            'body' => ['nullable', 'string', 'max:5000'],
            'priority' => ['nullable', 'in:low,normal,high'],
            'redirect_after' => ['nullable', 'in:dashboard,tasks'],
        ];

        if ($canAssign) {
            $rules['assigned_staff_id'] = [
                'nullable',
                Rule::exists('staff', 'id')->where(fn ($q) => $q->where('salon_id', $salon->id)),
            ];
            $rules['due_at'] = ['nullable', 'date'];
        }

        $data = $request->validate($rules);

        $priority = $data['priority'] ?? 'normal';
        if ($scopedStaffId !== null) {
            $priority = 'normal';
        }

        $tz = SalonTime::timezone($salon);
        $dueAt = null;
        if ($canAssign && ! empty($data['due_at'])) {
            $dueAt = Carbon::parse($data['due_at'], $tz)->endOfDay()->utc();
        }

        $assignedId = $canAssign ? ($data['assigned_staff_id'] ?? null) : null;

        SalonActionItem::create([
            'salon_id' => $salon->id,
            'staff_id' => $scopedStaffId,
            'assigned_staff_id' => $assignedId,
            'kind' => $data['kind'],
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'priority' => $priority,
            'status' => 'open',
            'due_at' => $dueAt,
        ]);

        $redirectTo = match ($data['redirect_after'] ?? 'dashboard') {
            'tasks' => route('tasks.index'),
            default => route('dashboard'),
        };

        return redirect()->to($redirectTo)->with('success', $scopedStaffId !== null
            ? 'Thanks — management will see your message on the dashboard.'
            : 'Task added.');
    }

    public function update(Request $request, SalonActionItem $actionItem): RedirectResponse
    {
        $salon = $this->activeSalon();
        abort_unless((int) $actionItem->salon_id === (int) $salon->id, 403);

        $user = Auth::user();
        abort_if($user->dashboardScopedStaffId() !== null, 403);
        $can = $user->isSuperAdmin()
            || $user->hasAnyRole(['tenant_admin', 'manager', 'receptionist'])
            || $user->salons()->whereKey($salon->id)->exists();
        abort_unless($can, 403);

        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,done,dismissed'],
        ]);

        $actionItem->update(['status' => $data['status']]);

        return redirect()->back()->with('success', 'Updated.');
    }
}
