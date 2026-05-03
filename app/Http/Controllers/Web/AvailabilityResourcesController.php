<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\SalonBufferRule;
use App\Models\SalonResource;
use App\Models\Staff;
use App\Models\StaffLeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AvailabilityResourcesController extends Controller
{
    use ResolvesActiveSalon;

    /** @var list<string> */
    public const WEEK_DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    private function salon()
    {
        return $this->activeSalon();
    }

    private function authoriseSalonResource(SalonResource $resource): void
    {
        abort_unless($resource->salon_id === $this->salon()->id, 403);
    }

    private function authoriseLeave(StaffLeaveRequest $leave): void
    {
        abort_unless($leave->salon_id === $this->salon()->id, 403);
    }

    public function index(Request $request): View
    {
        $salon = $this->salon();
        $tab   = $request->query('tab', 'availability');
        if (! in_array($tab, ['availability', 'resources', 'leave', 'buffer'], true)) {
            $tab = 'availability';
        }

        $staff = $this->salonScoped(Staff::class)
            ->orderBy('sort_order')
            ->orderBy('first_name')
            ->get();

        $resources = $this->salonScoped(SalonResource::class)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $leaveRequests = $this->salonScoped(StaffLeaveRequest::class)
            ->with('staff')
            ->latest()
            ->get();

        $bufferRule = SalonBufferRule::withoutGlobalScopes()->firstOrCreate(
            ['salon_id' => $salon->id],
            []
        );

        return view('availability.index', compact(
            'salon',
            'tab',
            'staff',
            'resources',
            'leaveRequests',
            'bufferRule'
        ));
    }

    public function toggleStaffDay(Request $request, Staff $staff): RedirectResponse
    {
        $salon = $this->salon();
        abort_unless($staff->salon_id === $salon->id, 403);

        $data = $request->validate([
            'day' => ['required', 'string', 'in:Mon,Tue,Wed,Thu,Fri,Sat,Sun'],
        ]);

        $day  = $data['day'];
        $days = $staff->working_days;
        if ($days === null) {
            $days = self::WEEK_DAYS;
        } else {
            $days = array_values(array_unique($days));
        }

        if (in_array($day, $days, true)) {
            $days = array_values(array_diff($days, [$day]));
        } else {
            $days[] = $day;
            $days = array_values(array_unique($days));
        }

        $order = array_flip(self::WEEK_DAYS);
        usort($days, fn ($a, $b) => ($order[$a] ?? 99) <=> ($order[$b] ?? 99));

        $staff->update(['working_days' => $days]);

        return redirect()
            ->route('availability.index', ['tab' => 'availability'])
            ->with('success', 'Weekly availability updated.');
    }

    public function storeResource(Request $request): RedirectResponse
    {
        $salon = $this->salon();

        $data = $request->validate([
            'name'                 => ['required', 'string', 'max:150'],
            'type'                 => ['required', 'string', 'in:room,chair,station'],
            'capacity'             => ['required', 'integer', 'min:1', 'max:99'],
            'equipment'            => ['nullable', 'string', 'max:500'],
            'bookable'             => ['nullable', 'boolean'],
            'status'               => ['nullable', 'string', 'in:active,pending'],
            'availability_status'  => ['nullable', 'string', 'in:available,in_use'],
        ]);

        $equipment = $this->parseEquipment($data['equipment'] ?? null);

        SalonResource::create([
            'salon_id'            => $salon->id,
            'name'                => $data['name'],
            'type'                => $data['type'],
            'capacity'            => $data['capacity'],
            'equipment'           => $equipment,
            'bookable'            => $request->boolean('bookable'),
            'status'              => $data['status'] ?? 'active',
            'availability_status' => $data['availability_status'] ?? 'available',
            'sort_order'          => (int) $this->salonScoped(SalonResource::class)->max('sort_order') + 1,
        ]);

        return redirect()
            ->route('availability.index', ['tab' => 'resources'])
            ->with('success', 'Resource added.');
    }

    public function updateResource(Request $request, SalonResource $resource): RedirectResponse
    {
        $this->authoriseSalonResource($resource);

        $data = $request->validate([
            'name'                 => ['required', 'string', 'max:150'],
            'type'                 => ['required', 'string', 'in:room,chair,station'],
            'capacity'             => ['required', 'integer', 'min:1', 'max:99'],
            'equipment'            => ['nullable', 'string', 'max:500'],
            'bookable'             => ['nullable', 'boolean'],
            'status'               => ['nullable', 'string', 'in:active,pending'],
            'availability_status'  => ['nullable', 'string', 'in:available,in_use'],
        ]);

        $resource->update([
            'name'                => $data['name'],
            'type'                => $data['type'],
            'capacity'            => $data['capacity'],
            'equipment'           => $this->parseEquipment($data['equipment'] ?? null),
            'bookable'            => $request->boolean('bookable'),
            'status'              => $data['status'] ?? $resource->status,
            'availability_status' => $data['availability_status'] ?? $resource->availability_status,
        ]);

        return redirect()
            ->route('availability.index', ['tab' => 'resources'])
            ->with('success', 'Resource updated.');
    }

    public function destroyResource(SalonResource $resource): RedirectResponse
    {
        $this->authoriseSalonResource($resource);
        $resource->delete();

        return redirect()
            ->route('availability.index', ['tab' => 'resources'])
            ->with('success', 'Resource removed.');
    }

    public function storeLeave(Request $request): RedirectResponse
    {
        $salon = $this->salon();

        $data = $request->validate([
            'staff_id'      => ['required', 'integer', 'exists:staff,id'],
            'leave_type'    => ['required', 'string', 'max:64'],
            'start_date'    => ['required', 'date'],
            'end_date'      => ['required', 'date', 'after_or_equal:start_date'],
            'notes'         => ['nullable', 'string', 'max:500'],
            'blocks_slots'  => ['nullable', 'boolean'],
        ]);

        $staff = $this->salonScoped(Staff::class)->where('id', $data['staff_id'])->firstOrFail();

        StaffLeaveRequest::create([
            'salon_id'      => $salon->id,
            'staff_id'      => $staff->id,
            'leave_type'    => $data['leave_type'],
            'start_date'    => $data['start_date'],
            'end_date'      => $data['end_date'],
            'notes'         => $data['notes'] ?? null,
            'blocks_slots'  => $request->boolean('blocks_slots'),
            'status'        => 'pending',
        ]);

        return redirect()
            ->route('availability.index', ['tab' => 'leave'])
            ->with('success', 'Leave request submitted.');
    }

    public function approveLeave(StaffLeaveRequest $leave): RedirectResponse
    {
        $this->authoriseLeave($leave);
        abort_unless($leave->isPending(), 403);

        $leave->update(['status' => 'approved']);

        return redirect()
            ->route('availability.index', ['tab' => 'leave'])
            ->with('success', 'Leave approved.');
    }

    public function rejectLeave(StaffLeaveRequest $leave): RedirectResponse
    {
        $this->authoriseLeave($leave);
        abort_unless($leave->isPending(), 403);

        $leave->update(['status' => 'rejected']);

        return redirect()
            ->route('availability.index', ['tab' => 'leave'])
            ->with('success', 'Leave rejected.');
    }

    public function updateBufferRules(Request $request): RedirectResponse
    {
        $salon = $this->salon();

        $data = $request->validate([
            'buffer_before_minutes'            => ['required', 'integer', 'min:0', 'max:240'],
            'buffer_after_minutes'             => ['required', 'integer', 'min:0', 'max:240'],
            'max_daily_bookings_per_staff'     => ['required', 'integer', 'min:1', 'max:100'],
            'advance_booking_days'             => ['required', 'integer', 'min:1', 'max:730'],
            'last_minute_cutoff_hours'         => ['required', 'integer', 'min:0', 'max:168'],
            'overbooking_percent'              => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $rule = SalonBufferRule::withoutGlobalScopes()->firstOrCreate(['salon_id' => $salon->id], []);
        $rule->update($data);

        return redirect()
            ->route('availability.index', ['tab' => 'buffer'])
            ->with('success', 'Buffer and booking rules saved.');
    }

    /** @return list<string>|null */
    private function parseEquipment(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $parts = array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/', $raw) ?: [])));

        return $parts === [] ? null : $parts;
    }
}
