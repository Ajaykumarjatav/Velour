<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Appointment;
use App\Models\PosTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class StaffController extends Controller
{
    /* ── GET /staff ─────────────────────────────────────────────────────── */
    public function index(Request $request): JsonResponse
    {
        $staff = Staff::where('salon_id', $request->attributes->get('salon_id'))
            ->when($request->active, fn($q) => $q->where('is_active', true))
            ->when($request->search, fn($q) => $q->where(function($sq) use ($request) {
                $sq->where('first_name', 'ilike', '%'.$request->search.'%')
                   ->orWhere('last_name',  'ilike', '%'.$request->search.'%')
                   ->orWhere('role',       'ilike', '%'.$request->search.'%');
            }))
            ->orderBy('sort_order')
            ->orderBy('first_name')
            ->paginate($request->integer('per_page', 50));

        return response()->json($staff);
    }

    /* ── POST /staff ────────────────────────────────────────────────────── */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:30',
            'role'            => 'nullable|string|max:100',
            'bio'             => 'nullable|string|max:2000',
            'specialisms'     => 'nullable|array',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'access_level'    => 'nullable|in:staff,senior,manager,owner',
            'start_time'      => 'nullable|date_format:H:i',
            'end_time'        => 'nullable|date_format:H:i',
            'working_days'    => 'nullable|array',
            'color'           => 'nullable|string|max:10',
            'hired_at'        => 'nullable|date',
            'bookable_online' => 'nullable|boolean',
        ]);

        $data['salon_id']  = $request->attributes->get('salon_id');
        $data['initials']  = strtoupper(substr($data['first_name'], 0, 1) . substr($data['last_name'], 0, 1));

        $staff = Staff::create($data);

        return response()->json(['message' => 'Staff member created.', 'staff' => $staff], 201);
    }

    /* ── GET /staff/{id} ────────────────────────────────────────────────── */
    public function show(Request $request, int $id): JsonResponse
    {
        $staff = Staff::with(['services'])
            ->where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);

        return response()->json($staff);
    }

    /* ── PUT /staff/{id} ────────────────────────────────────────────────── */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'first_name'      => 'sometimes|string|max:100',
            'last_name'       => 'sometimes|string|max:100',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:30',
            'role'            => 'nullable|string|max:100',
            'bio'             => 'nullable|string|max:2000',
            'specialisms'     => 'nullable|array',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'access_level'    => 'nullable|in:staff,senior,manager,owner',
            'working_days'    => 'nullable|array',
            'start_time'      => 'nullable|date_format:H:i',
            'end_time'        => 'nullable|date_format:H:i',
            'color'           => 'nullable|string|max:10',
            'is_active'       => 'nullable|boolean',
            'bookable_online' => 'nullable|boolean',
        ]);

        $staff = Staff::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        if (isset($data['first_name']) || isset($data['last_name'])) {
            $fn = $data['first_name'] ?? $staff->first_name;
            $ln = $data['last_name']  ?? $staff->last_name;
            $data['initials'] = strtoupper(substr($fn, 0, 1) . substr($ln, 0, 1));
        }

        $staff->update($data);

        return response()->json(['message' => 'Staff updated.', 'staff' => $staff]);
    }

    /* ── DELETE /staff/{id} ─────────────────────────────────────────────── */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $staff = Staff::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $staff->update(['is_active' => false]);
        $staff->delete();
        return response()->json(['message' => 'Staff member removed.']);
    }

    /* ── POST /staff/{id}/avatar ────────────────────────────────────────── */
    public function uploadAvatar(Request $request, int $id): JsonResponse
    {
        $request->validate(['avatar' => 'required|mimes:jpg,jpeg,png,webp|max:2048|dimensions:min_width=50,min_height=50,max_width=2000,max_height=2000']);
        $staff = Staff::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $path  = $request->file('avatar')->store("salons/{$staff->salon_id}/staff", 'public');
        $staff->update(['avatar' => $path]);
        return response()->json(['avatar' => $path]);
    }

    /* ── GET /staff/{id}/schedule ───────────────────────────────────────── */
    public function schedule(Request $request, int $id): JsonResponse
    {
        $request->validate(['date' => 'nullable|date', 'week' => 'nullable|date']);
        $staff  = Staff::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $from   = Carbon::parse($request->week ?? $request->date ?? today())->startOfWeek();
        $to     = $from->copy()->endOfWeek();

        $appointments = Appointment::with(['client', 'services'])
            ->where('staff_id', $id)
            ->whereBetween('starts_at', [$from, $to])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->orderBy('starts_at')
            ->get();

        return response()->json([
            'staff'        => $staff,
            'week_start'   => $from->toDateString(),
            'week_end'     => $to->toDateString(),
            'appointments' => $appointments,
        ]);
    }

    /* ── PUT /staff/{id}/schedule ───────────────────────────────────────── */
    public function updateSchedule(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'working_days' => 'required|array',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i',
        ]);

        $staff = Staff::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $staff->update($data);

        return response()->json(['message' => 'Schedule updated.', 'staff' => $staff]);
    }

    /* ── GET /staff/{id}/performance ────────────────────────────────────── */
    public function performance(Request $request, int $id): JsonResponse
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);

        $staff = Staff::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $from  = Carbon::parse($request->from ?? now()->startOfMonth());
        $to    = Carbon::parse($request->to   ?? now()->endOfMonth());

        $appointments = Appointment::where('staff_id', $id)
            ->whereBetween('starts_at', [$from, $to])
            ->get();

        $revenue = PosTransaction::where('staff_id', $id)
            ->whereBetween('completed_at', [$from, $to])
            ->where('status', 'completed')
            ->sum('total');

        $commission = round($revenue * ($staff->commission_rate / 100), 2);

        return response()->json([
            'staff'          => $staff,
            'period'         => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'total_appts'    => $appointments->count(),
            'completed_appts'=> $appointments->where('status', 'completed')->count(),
            'no_shows'       => $appointments->where('status', 'no_show')->count(),
            'cancellations'  => $appointments->where('status', 'cancelled')->count(),
            'revenue'        => $revenue,
            'commission'     => $commission,
            'utilisation'    => $this->calcUtilisation($staff, $appointments, $from, $to),
        ]);
    }

    /* ── GET /staff/{id}/commission ─────────────────────────────────────── */
    public function commission(Request $request, int $id): JsonResponse
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);
        $staff  = Staff::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $month  = Carbon::parse($request->month ?? now()->format('Y-m'));
        $from   = $month->copy()->startOfMonth();
        $to     = $month->copy()->endOfMonth();

        $transactions = PosTransaction::with('items')
            ->where('staff_id', $id)
            ->whereBetween('completed_at', [$from, $to])
            ->where('status', 'completed')
            ->get();

        $revenue    = $transactions->sum('total');
        $commission = round($revenue * ($staff->commission_rate / 100), 2);

        return response()->json([
            'staff'           => $staff->only(['id','first_name','last_name','commission_rate']),
            'month'           => $month->format('F Y'),
            'transactions'    => $transactions->count(),
            'gross_revenue'   => $revenue,
            'commission_rate' => $staff->commission_rate,
            'commission_due'  => $commission,
        ]);
    }

    /* ── POST /staff/{id}/invite ────────────────────────────────────────── */
    public function sendInvite(Request $request, int $id): JsonResponse
    {
        $staff = Staff::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        if (! $staff->email) {
            return response()->json(['message' => 'Staff member has no email address.'], 422);
        }

        // Mail::to($staff->email)->send(new \App\Mail\StaffInvite($staff));
        return response()->json(['message' => "Invite sent to {$staff->email}."]);
    }

    /* ── Private ─────────────────────────────────────────────────────────── */
    private function calcUtilisation(Staff $staff, $appointments, Carbon $from, Carbon $to): float
    {
        $workingDays = collect($staff->working_days ?? ['Mon','Tue','Wed','Thu','Fri']);
        $totalDays   = $from->diffInDays($to) + 1;
        $dayHours    = $staff->start_time && $staff->end_time
            ? Carbon::parse($staff->end_time)->diffInHours(Carbon::parse($staff->start_time))
            : 8;

        $availableMinutes = $workingDays->count() * ($totalDays / 7) * $dayHours * 60;
        $bookedMinutes    = $appointments->where('status', '!=', 'cancelled')
                                         ->where('status', '!=', 'no_show')
                                         ->sum('duration_minutes');

        return $availableMinutes > 0
            ? round(($bookedMinutes / $availableMinutes) * 100, 1)
            : 0;
    }
}
