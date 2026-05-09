<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\SalonActionItem;
use App\Models\Staff;
use App\Support\SalonTime;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    use ResolvesActiveSalon;

    private function canManageDesk(): bool
    {
        $user = Auth::user();
        if ($user->isSuperAdmin()) {
            return true;
        }
        if ($user->dashboardScopedStaffId() !== null) {
            return false;
        }
        $salon = $this->activeSalon();

        return $user->hasAnyRole(['tenant_admin', 'manager', 'receptionist'])
            || $user->salons()->whereKey($salon->id)->exists();
    }

    private function authorizeBoardAccess(): void
    {
        $user = Auth::user();
        abort_unless(
            $this->canManageDesk() || $user->dashboardScopedStaffId() !== null,
            403
        );
    }

    public function index()
    {
        $this->authorizeBoardAccess();

        $salon = $this->activeSalon();
        $user = Auth::user();
        $staffScopeId = $user->dashboardScopedStaffId();
        $canManage = $this->canManageDesk();

        $tz = SalonTime::timezone($salon);
        $todayLocal = Carbon::now($tz)->startOfDay();

        $query = SalonActionItem::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->whereNotIn('status', ['dismissed'])
            ->with(['staff', 'assignedStaff']);

        if ($staffScopeId !== null) {
            $query->where(function ($q) use ($staffScopeId) {
                $q->where('assigned_staff_id', $staffScopeId)
                    ->orWhere('staff_id', $staffScopeId);
            });
        }

        $items = $query->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'in_progress' THEN 1 ELSE 2 END")
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 ELSE 2 END")
            ->orderByDesc('updated_at')
            ->get();

        $countOpen = $items->where('status', 'open')->count();
        $countInProgress = $items->where('status', 'in_progress')->count();
        $countDone = $items->where('status', 'done')->count();

        $countOverdue = $items->filter(function (SalonActionItem $item) use ($tz, $todayLocal) {
            if (! in_array($item->status, ['open', 'in_progress'], true) || ! $item->due_at) {
                return false;
            }
            $dueDay = Carbon::parse($item->due_at)->timezone($tz)->startOfDay();

            return $dueDay->lt($todayLocal);
        })->count();

        $columnTodo = $items->where('status', 'open')->values();
        $columnProgress = $items->where('status', 'in_progress')->values();
        $columnDone = $items->where('status', 'done')->values();

        $staffForAssign = collect();
        if ($canManage) {
            $staffForAssign = Staff::withoutGlobalScopes()
                ->where('salon_id', $salon->id)
                ->where('is_active', true)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();
        }

        $deskKindLabels = SalonActionItem::kindLabels();

        return view('tasks.index', compact(
            'salon',
            'canManage',
            'staffScopeId',
            'tz',
            'todayLocal',
            'countOpen',
            'countInProgress',
            'countDone',
            'countOverdue',
            'columnTodo',
            'columnProgress',
            'columnDone',
            'staffForAssign',
            'deskKindLabels'
        ));
    }

    public function update(Request $request, SalonActionItem $actionItem): RedirectResponse
    {
        abort_unless($this->canManageDesk(), 403);

        $salon = $this->activeSalon();
        abort_unless((int) $actionItem->salon_id === (int) $salon->id, 403);

        $data = $request->validate([
            'kind' => ['required', 'in:'.SalonActionItem::KIND_ADMIN_TODO.','.SalonActionItem::KIND_STAFF_SUGGESTION.','.SalonActionItem::KIND_INVENTORY_REQUEST.','.SalonActionItem::KIND_GENERAL],
            'title' => ['required', 'string', 'max:200'],
            'body' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', 'in:low,normal,high'],
            'status' => ['required', 'in:open,in_progress,done,dismissed'],
            'due_at' => ['nullable', 'date'],
            'assigned_staff_id' => [
                'nullable',
                Rule::exists('staff', 'id')->where(fn ($q) => $q->where('salon_id', $salon->id)),
            ],
        ]);

        $tz = SalonTime::timezone($salon);
        $dueAt = null;
        if (! empty($data['due_at'])) {
            $dueAt = Carbon::parse($data['due_at'], $tz)->endOfDay()->utc();
        }

        $actionItem->update([
            'kind' => $data['kind'],
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'priority' => $data['priority'],
            'status' => $data['status'],
            'due_at' => $dueAt,
            'assigned_staff_id' => $data['assigned_staff_id'] ?? null,
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task updated.');
    }

    public function destroy(SalonActionItem $actionItem): RedirectResponse
    {
        abort_unless($this->canManageDesk(), 403);

        $salon = $this->activeSalon();
        abort_unless((int) $actionItem->salon_id === (int) $salon->id, 403);

        $actionItem->delete();

        return redirect()->route('tasks.index')->with('success', 'Task removed.');
    }
}
