<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Staff;
use App\Models\Service;
use App\Models\StaffLeaveRequest;
use App\Services\AppointmentService as AppointmentBookingService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index(Request $request)
    {
        $salon   = $this->salon();
        $search  = $request->get('search');
        $status  = $request->get('status');
        $date    = $request->get('date');
        $staffId = $request->get('staff_id');

        $query = Appointment::where('salon_id', $salon->id)
            ->with(['client', 'staff', 'services.service'])
            ->latest('starts_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('client', fn($c) =>
                    $c->where('first_name', 'like', "%$search%")
                      ->orWhere('last_name',  'like', "%$search%")
                      ->orWhere('email',      'like', "%$search%")
                      ->orWhere('phone',      'like', "%$search%")
                )->orWhere('reference', 'like', "%$search%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($date) {
            $query->whereDate('starts_at', $date);
        }

        if ($staffId) {
            $query->where('staff_id', $staffId);
        }

        $appointments = $query->paginate(20)->withQueryString();
        $staff        = Staff::where('salon_id', $salon->id)
            ->where('is_active', true)
            ->withName()
            ->get();

        return view('appointments.index', compact('salon', 'appointments', 'staff', 'search', 'status', 'date', 'staffId'));
    }

    public function create()
    {
        $salon    = $this->salon();
        $clients  = Client::where('salon_id', $salon->id)->orderBy('first_name')->get(['id','first_name','last_name','phone']);
        $staff    = Staff::where('salon_id', $salon->id)->where('is_active', true)->withName()->get();
        $services = Service::where('salon_id', $salon->id)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('appointments.create', compact('salon', 'clients', 'staff', 'services'));
    }

    /**
     * JSON: HH:MM slot starts that overlap an existing booking for this staff on this date
     * (uses max single-service duration+buffer in the salon as a conservative window).
     */
    public function occupiedSlots(Request $request)
    {
        $data = $request->validate([
            'date'                     => ['required', 'date_format:Y-m-d'],
            'staff_id'                 => ['required', 'integer', 'exists:staff,id'],
            'exclude_appointment_id'   => ['nullable', 'integer', 'exists:appointments,id'],
        ]);

        $salon   = $this->salon();
        $staffId = (int) $data['staff_id'];

        abort_unless(
            Staff::where('salon_id', $salon->id)->where('id', $staffId)->exists(),
            404
        );

        $excludeId = isset($data['exclude_appointment_id']) ? (int) $data['exclude_appointment_id'] : null;
        if ($excludeId) {
            abort_unless(
                Appointment::where('salon_id', $salon->id)->where('id', $excludeId)->exists(),
                404
            );
        }

        $maxMinutes = Service::where('salon_id', $salon->id)
            ->active()
            ->get(['duration_minutes', 'buffer_minutes'])
            ->map(fn (Service $s) => (int) $s->duration_minutes + (int) ($s->buffer_minutes ?? 0))
            ->max();

        $maxMinutes = max(30, (int) $maxMinutes);

        $slotTimes = [
            '09:00', '09:30', '10:00', '10:30',
            '11:00', '11:30', '12:00', '12:30',
            '13:00', '14:00', '14:30', '15:00',
            '15:30', '16:00', '16:30', '17:00',
            '17:30', '18:00', '18:30', '19:00',
        ];

        $dateStr = $data['date'];

        if (StaffLeaveRequest::approvedBlockingLeaveExists($salon->id, $staffId, $dateStr)) {
            return response()->json([
                'blocked'                  => $slotTimes,
                'assumed_duration_minutes' => $maxMinutes,
            ]);
        }

        $blocked = [];

        foreach ($slotTimes as $time) {
            $start = Carbon::parse("{$dateStr} {$time}:00");
            $end   = $start->copy()->addMinutes($maxMinutes);

            $overlap = Appointment::where('salon_id', $salon->id)
                ->where('staff_id', $staffId)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->where('starts_at', '<', $end)
                ->where('ends_at', '>', $start)
                ->exists();

            if ($overlap) {
                $blocked[] = $time;
            }
        }

        return response()->json([
            'blocked'                    => $blocked,
            'assumed_duration_minutes'   => $maxMinutes,
        ]);
    }

    public function store(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'client_id'            => ['required', 'exists:clients,id'],
            'staff_id'             => ['required', 'exists:staff,id'],
            'starts_at'            => ['required', 'date'],
            'services'             => ['required', 'array', 'min:1'],
            'services.*'           => ['exists:services,id'],
            'service_variant'      => ['nullable', 'array'],
            'service_variant.*'    => ['nullable', 'string', 'max:100'],
            'service_addons'       => ['nullable', 'array'],
            'service_addons.*'     => ['nullable', 'array'],
            'service_addons.*.*'   => ['nullable', 'string', 'max:100'],
            'internal_notes'       => ['nullable', 'string', 'max:1000'],
            'client_notes'         => ['nullable', 'string', 'max:1000'],
        ]);

        $orderedIds = array_map('intval', $data['services']);
        $options    = [];
        foreach ($orderedIds as $sid) {
            $options[$sid] = [
                'variant' => $data['service_variant'][$sid] ?? null,
                'addons'  => array_values(array_filter($data['service_addons'][$sid] ?? [])),
            ];
        }

        try {
            $snapshot = Service::summarizeForAppointment($salon->id, $orderedIds, $options);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['services' => $e->getMessage()])->withInput();
        }

        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt   = $startsAt->copy()->addMinutes($snapshot['total_span_minutes']);

        try {
            app(AppointmentBookingService::class)->assertStaffNotOnBlockingLeave(
                $salon->id,
                (int) $data['staff_id'],
                $startsAt,
                $endsAt
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['starts_at' => $e->getMessage()])->withInput();
        }

        $conflict = Appointment::where('salon_id', $salon->id)
            ->where('staff_id', $data['staff_id'])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($conflict) {
            return back()->withErrors(['starts_at' => 'That time slot is already booked for this staff member. Please choose another time.'])->withInput();
        }

        DB::transaction(function () use ($data, $salon, $snapshot, $startsAt, $endsAt) {
            $appointment = Appointment::create([
                'salon_id'         => $salon->id,
                'client_id'        => $data['client_id'],
                'staff_id'         => $data['staff_id'],
                'starts_at'        => $startsAt,
                'ends_at'          => $endsAt,
                'duration_minutes' => $snapshot['total_span_minutes'],
                'total_price'      => $snapshot['total_price'],
                'status'           => 'confirmed',
                'source'           => 'walk_in',
                'internal_notes'   => $data['internal_notes'] ?? null,
                'client_notes'     => $data['client_notes'] ?? null,
            ]);

            foreach ($snapshot['lines'] as $line) {
                $appointment->services()->create([
                    'service_id'       => $line['service_id'],
                    'service_name'     => $line['service_name'],
                    'price'            => $line['price'],
                    'duration_minutes' => $line['duration_minutes'],
                    'line_meta'        => $line['line_meta'],
                    'sort_order'       => $line['sort_order'],
                ]);
            }
        });

        return redirect()->route('appointments.index')->with('success', 'Appointment booked successfully.');
    }

    public function show(Appointment $appointment)
    {
        $this->authorise($appointment);
        $appointment->load(['client', 'staff', 'services.service', 'transaction', 'review']);
        $salon = $this->salon();
        $staff = Staff::where('salon_id', $salon->id)->where('is_active', true)->withName()->get();

        return view('appointments.show', compact('appointment', 'staff'));
    }

    public function edit(Appointment $appointment)
    {
        $this->authorise($appointment);
        $salon    = $this->salon();
        $clients  = Client::where('salon_id', $salon->id)->orderBy('first_name')->get(['id','first_name','last_name']);
        $staff    = Staff::where('salon_id', $salon->id)->where('is_active', true)->withName()->get();
        $services = Service::where('salon_id', $salon->id)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('appointments.edit', compact('appointment', 'clients', 'staff', 'services'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->authorise($appointment);

        $data = $request->validate([
            'client_id'      => ['required', 'exists:clients,id'],
            'staff_id'       => ['required', 'exists:staff,id'],
            'starts_at'      => ['required', 'date'],
            'internal_notes' => ['nullable', 'string', 'max:1000'],
            'client_notes'   => ['nullable', 'string', 'max:1000'],
        ]);

        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt   = $startsAt->copy()->addMinutes($appointment->duration_minutes);
        $staffId  = (int) $data['staff_id'];

        try {
            app(AppointmentBookingService::class)->assertStaffNotOnBlockingLeave(
                $appointment->salon_id,
                $staffId,
                $startsAt,
                $endsAt
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['starts_at' => $e->getMessage()])->withInput();
        }

        $conflict = Appointment::where('salon_id', $appointment->salon_id)
            ->where('staff_id', $staffId)
            ->where('id', '!=', $appointment->id)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($conflict) {
            return back()->withErrors(['starts_at' => 'That time slot is already booked for this staff member. Please choose another time.'])->withInput();
        }

        $appointment->update([
            'client_id'      => $data['client_id'],
            'staff_id'       => $staffId,
            'starts_at'      => $startsAt,
            'ends_at'        => $endsAt,
            'internal_notes' => $data['internal_notes'] ?? null,
            'client_notes'   => $data['client_notes'] ?? null,
        ]);

        return redirect()->route('appointments.show', $appointment)->with('success', 'Appointment updated.');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $this->authorise($appointment);
        $data = $request->validate(['status' => ['required', 'in:confirmed,completed,cancelled,no_show']]);
        $appointment->update(['status' => $data['status']]);

        return back()->with('success', 'Status updated.');
    }

    /* ── Dedicated action methods ─────────────────────────────────────────── */

    public function confirm(Appointment $appointment): RedirectResponse
    {
        $this->authorise($appointment);

        if ($appointment->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending appointments can be confirmed.']);
        }

        $appointment->update([
            'status'       => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $this->notificationService->notifyTenantNewBooking($appointment->fresh(['client', 'staff', 'services.service', 'salon']));

        return back()->with('success', 'Appointment confirmed and client notified.');
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorise($appointment);

        if (in_array($appointment->status, ['completed', 'cancelled', 'no_show'])) {
            return back()->withErrors(['status' => 'This appointment cannot be cancelled.']);
        }

        $data = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $appointment->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => $data['cancellation_reason'] ?? null,
        ]);

        $this->notificationService->notifyTenantCancellation($appointment->fresh(['client', 'staff', 'services.service', 'salon']));

        return back()->with('success', 'Appointment cancelled.');
    }

    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorise($appointment);

        if (in_array($appointment->status, ['completed', 'cancelled', 'no_show'])) {
            return back()->withErrors(['status' => 'This appointment cannot be rescheduled.']);
        }

        $data = $request->validate([
            'starts_at' => ['required', 'date', 'after:now'],
            'staff_id'  => ['nullable', 'exists:staff,id'],
        ]);

        $originalStartsAt = $appointment->starts_at->copy();
        $newStartsAt      = Carbon::parse($data['starts_at']);
        $newEndsAt        = $newStartsAt->copy()->addMinutes($appointment->duration_minutes);
        $staffId          = $data['staff_id'] ?? $appointment->staff_id;

        // Conflict check: overlapping non-cancelled appointments for same staff
        $conflict = Appointment::where('salon_id', $appointment->salon_id)
            ->where('staff_id', $staffId)
            ->where('id', '!=', $appointment->id)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where('starts_at', '<', $newEndsAt)
            ->where('ends_at',   '>', $newStartsAt)
            ->exists();

        if ($conflict) {
            return back()->withErrors(['starts_at' => 'That time slot is not available. Please choose another time.']);
        }

        $appointment->update([
            'starts_at' => $newStartsAt,
            'ends_at'   => $newEndsAt,
            'staff_id'  => $staffId,
        ]);

        $this->notificationService->notifyTenantReschedule(
            $appointment->fresh(['client', 'staff', 'services.service', 'salon']),
            $originalStartsAt
        );

        return back()->with('success', 'Appointment rescheduled.');
    }

    public function complete(Appointment $appointment): RedirectResponse
    {
        $this->authorise($appointment);

        if (! in_array($appointment->status, ['confirmed', 'checked_in', 'in_progress'])) {
            return back()->withErrors(['status' => 'Only confirmed or in-progress appointments can be marked as completed.']);
        }

        $appointment->update(['status' => 'completed']);

        // Update client visit stats
        $client = $appointment->client;
        $client->increment('visit_count');
        $client->update(['last_visit_at' => $appointment->starts_at]);

        return back()->with('success', 'Appointment marked as completed.');
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorise($appointment);
        $appointment->delete();

        return redirect()->route('appointments.index')->with('success', 'Appointment deleted.');
    }

    private function authorise(Appointment $appointment): void
    {
        abort_unless($appointment->salon_id === $this->salon()->id, 403);
    }
}
