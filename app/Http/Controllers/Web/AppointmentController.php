<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\LoyaltyTier;
use App\Models\Staff;
use App\Models\Service;
use App\Models\StaffLeaveRequest;
use App\Services\AppointmentService as AppointmentBookingService;
use App\Services\AvailabilityService;
use App\Services\NotificationService;
use App\Services\Scheduling\AvailabilityRejectedException;
use App\Support\SalonTime;
use App\Support\StaffServiceEligibility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    use ResolvesActiveSalon;

    public function __construct(private NotificationService $notificationService) {}

    private function salon()
    {
        return $this->activeSalon();
    }

    public function index(Request $request)
    {
        $salon   = $this->salon();
        $search  = $request->get('search');
        $status  = $request->get('status');
        $date    = $request->get('date');
        $staffId = $request->get('staff_id');
        $scopedStaffId = Auth::user()->dashboardScopedStaffId();
        if ($scopedStaffId !== null) {
            $staffId = $scopedStaffId;
        }

        $query = Appointment::withoutGlobalScopes()->where('salon_id', $salon->id)
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
        $staffQuery   = Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->where('is_active', true);
        if ($scopedStaffId !== null) {
            $staffQuery->whereKey($scopedStaffId);
        }
        $staff = $staffQuery->withName()->get();

        return view('appointments.index', compact('salon', 'appointments', 'staff', 'search', 'status', 'date', 'staffId'));
    }

    public function create()
    {
        $salon    = $this->salon();
        $scopedStaffId = Auth::user()->dashboardScopedStaffId();
        $clients  = Client::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(30)
            ->get(['id', 'first_name', 'last_name', 'phone']);
        $oldClientId = old('client_id');
        if ($oldClientId && ! $clients->firstWhere('id', (int) $oldClientId)) {
            $extra = Client::withoutGlobalScopes()
                ->where('salon_id', $salon->id)
                ->find((int) $oldClientId, ['id', 'first_name', 'last_name', 'phone']);
            if ($extra) {
                $clients->prepend($extra);
            }
        }
        $staffQuery = Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->where('is_active', true);
        if ($scopedStaffId !== null) {
            $staffQuery->whereKey($scopedStaffId);
        }
        $staff    = $staffQuery->withName()
            ->with([
                'services' => fn ($q) => $q->withoutTenantScope()->where('services.salon_id', $salon->id),
            ])
            ->get();
        $staffServiceIdsByStaffId = $staff->mapWithKeys(fn (Staff $s) => [
            $s->id => $s->services->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
        ])->all();
        $services = Service::withoutTenantScope()
            ->where('salon_id', $salon->id)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $clientQuickCreateLoyaltyTiers = LoyaltyTier::where('salon_id', $salon->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        $staffQuickCreateServicesByRole = StaffServiceEligibility::servicesByRoleForSalon($salon->id);
        $defaultStaffId = old('staff_id', $scopedStaffId !== null ? (string) $scopedStaffId : '');
        if ($scopedStaffId !== null) {
            $defaultStaffId = (string) $scopedStaffId;
        }

        return view('appointments.create', compact(
            'salon',
            'clients',
            'staff',
            'services',
            'staffServiceIdsByStaffId',
            'clientQuickCreateLoyaltyTiers',
            'staffQuickCreateServicesByRole',
            'scopedStaffId',
            'defaultStaffId'
        ));
    }

    /**
     * POST JSON: validate a proposed window using the same rules as booking / reschedule.
     */
    public function validateWindow(Request $request)
    {
        $data = $request->validate([
            'staff_id'               => ['required', 'integer', 'exists:staff,id'],
            'starts_at'              => ['required', 'date'],
            'ends_at'                => ['nullable', 'date', 'required_without:duration_minutes', 'after:starts_at'],
            'duration_minutes'       => ['nullable', 'integer', 'min:5', 'max:960', 'required_without:ends_at'],
            'exclude_appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
        ]);

        $salon = $this->salon();
        abort_unless(
            Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->where('id', $data['staff_id'])->exists(),
            404
        );

        $staff = Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->findOrFail($data['staff_id']);
        $starts = SalonTime::parseAppointmentStartsAt($salon, $data['starts_at']);
        $ends = isset($data['ends_at'])
            ? SalonTime::parseAppointmentStartsAt($salon, $data['ends_at'])
            : $starts->copy()->addMinutes((int) $data['duration_minutes']);

        $exclude = isset($data['exclude_appointment_id']) ? (int) $data['exclude_appointment_id'] : null;
        if ($exclude) {
            abort_unless(
                Appointment::withoutGlobalScopes()->where('salon_id', $salon->id)->whereKey($exclude)->exists(),
                404
            );
        }

        $result = app(AvailabilityService::class)->validateProposedWindow($salon, $staff, $starts, $ends, $exclude, false);

        return response()->json([
            'ok'       => $result->ok,
            'reasons'  => $result->reasons,
            'message'  => $result->ok ? null : $result->firstMessage(),
        ]);
    }

    /**
     * JSON: HH:MM slot starts that are not viable for this staff on this date
     * (uses max single-service duration+buffer in the salon as a conservative window).
     * Blocked reasons use the unified availability engine (salon hours, staff shift, leave, overlap).
     */
    public function occupiedSlots(Request $request)
    {
        $data = $request->validate([
            'date'                     => ['required', 'date_format:Y-m-d'],
            'staff_id'                 => ['required', 'integer', 'exists:staff,id'],
            'exclude_appointment_id'   => ['nullable', 'integer', 'exists:appointments,id'],
            'service_ids'              => ['nullable', 'array'],
            'service_ids.*'            => ['integer', 'exists:services,id'],
        ]);

        $salon   = $this->salon();
        $staffId = (int) $data['staff_id'];

        abort_unless(
            Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->where('id', $staffId)->exists(),
            404
        );

        $excludeId = isset($data['exclude_appointment_id']) ? (int) $data['exclude_appointment_id'] : null;
        if ($excludeId) {
            abort_unless(
                Appointment::withoutGlobalScopes()->where('salon_id', $salon->id)->where('id', $excludeId)->exists(),
                404
            );
        }

        $serviceIds = array_values(array_unique(array_map('intval', $data['service_ids'] ?? [])));

        if ($serviceIds !== []) {
            $svcRows = Service::withoutTenantScope()
                ->where('salon_id', $salon->id)
                ->active()
                ->whereIn('id', $serviceIds)
                ->get();
            if ($svcRows->count() !== count(array_unique($serviceIds))) {
                abort(422, 'Invalid services selection.');
            }
            $maxMinutes = max(30, BookingService::combinedDurationMinutes($svcRows, $salon->id));
        } else {
            $maxMinutes = Service::withoutTenantScope()
                ->where('salon_id', $salon->id)
                ->active()
                ->get(['duration_minutes', 'buffer_minutes'])
                ->map(fn (Service $s) => (int) $s->duration_minutes + (int) ($s->buffer_minutes ?? 0))
                ->max();

            $maxMinutes = max(30, (int) $maxMinutes);
            // Without a service selection, the catalog "longest" offering (e.g. day packages) must
            // not drive the grid: it makes probes cross midnight and breaks salon-hours checks.
            $maxMinutes = min($maxMinutes, 180);
        }

        $slotTimes = [
            '09:00', '09:30', '10:00', '10:30',
            '11:00', '11:30', '12:00', '12:30',
            '13:00', '14:00', '14:30', '15:00',
            '15:30', '16:00', '16:30', '17:00',
            '17:30', '18:00', '18:30', '19:00',
        ];

        $dateStr = $data['date'];

        if (StaffLeaveRequest::approvedBlockingLeaveExists($salon->id, $staffId, $dateStr)) {
            $blockedDetails = collect($slotTimes)->mapWithKeys(fn ($t) => [
                $t => 'Approved leave or time off — adjust under Availability or Staff.',
            ])->all();

            return response()->json([
                'blocked'                  => $slotTimes,
                'blocked_details'          => $blockedDetails,
                'assumed_duration_minutes' => $maxMinutes,
            ]);
        }

        $staffMember = Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->whereKey($staffId)->firstOrFail();
        $tz          = SalonTime::timezone($salon);
        $availability = app(AvailabilityService::class);

        $blocked = [];
        $blockedDetails = [];

        foreach ($slotTimes as $time) {
            $start = Carbon::createFromFormat('Y-m-d H:i', $dateStr . ' ' . $time, $tz);
            $end   = $start->copy()->addMinutes($maxMinutes);

            // Do not enforce "end before next midnight" here: assumed duration can be the salon's
            // longest service while no services are checked, which pushes the probe past midnight
            // for late slots. Real bookings still use full enforcement on store/update.
            $result = $availability->validateProposedWindow($salon, $staffMember, $start, $end, $excludeId, false, false);
            if (! $result->ok) {
                $blocked[] = $time;
                $blockedDetails[$time] = $result->firstMessage();
            }
        }

        return response()->json([
            'blocked'                    => $blocked,
            'blocked_details'            => $blockedDetails,
            'assumed_duration_minutes'   => $maxMinutes,
        ]);
    }

    public function store(Request $request)
    {
        $salon = $this->salon();
        $scopedStaffId = Auth::user()->dashboardScopedStaffId();

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

        if ($scopedStaffId !== null) {
            $data['staff_id'] = (string) $scopedStaffId;
        }

        $orderedIds = array_map('intval', $data['services']);
        $options    = [];
        foreach ($orderedIds as $sid) {
            $options[$sid] = [
                'variant' => $data['service_variant'][$sid] ?? null,
                'addons'  => array_values(array_filter($data['service_addons'][$sid] ?? [])),
            ];
        }

        try {
            app(AppointmentBookingService::class)->create($salon->id, [
                'client_id'         => (int) $data['client_id'],
                'staff_id'          => (int) $data['staff_id'],
                'starts_at'         => $data['starts_at'],
                'service_ids'       => $orderedIds,
                'service_options'   => $options,
                'source'            => 'walk_in',
                'internal_notes'    => $data['internal_notes'] ?? null,
                'client_notes'      => $data['client_notes'] ?? null,
            ]);
        } catch (AvailabilityRejectedException $e) {
            return back()->withErrors(['starts_at' => $e->result->firstMessage()])->withInput();
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['services' => $e->getMessage()])->withInput();
        }

        return redirect()->route('appointments.index')->with('success', 'Appointment booked successfully.');
    }

    public function show(Appointment $appointment)
    {
        $this->authorise($appointment);
        $appointment->load(['client', 'staff', 'services.service', 'transaction', 'review']);
        $salon = $this->salon();
        $staff = Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->where('is_active', true)->withName()->get();
        $staffQuickCreateServicesByRole = StaffServiceEligibility::servicesByRoleForSalon($salon->id);

        return view('appointments.show', compact('appointment', 'staff', 'salon', 'staffQuickCreateServicesByRole'));
    }

    public function edit(Appointment $appointment)
    {
        $this->authorise($appointment);
        $salon    = $this->salon();
        $clients  = Client::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(30)
            ->get(['id', 'first_name', 'last_name', 'phone']);
        if (! $clients->contains('id', $appointment->client_id)) {
            $selectedClient = Client::withoutGlobalScopes()
                ->where('salon_id', $salon->id)
                ->find($appointment->client_id, ['id', 'first_name', 'last_name', 'phone']);
            if ($selectedClient) {
                $clients->prepend($selectedClient);
            }
        }
        $staff    = Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->where('is_active', true)->withName()->get();
        $services = Service::withoutTenantScope()
            ->where('salon_id', $salon->id)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $clientQuickCreateLoyaltyTiers = LoyaltyTier::where('salon_id', $salon->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        $staffQuickCreateServicesByRole = StaffServiceEligibility::servicesByRoleForSalon($salon->id);

        return view('appointments.edit', compact('appointment', 'clients', 'staff', 'services', 'clientQuickCreateLoyaltyTiers', 'staffQuickCreateServicesByRole'));
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

        $salon   = $this->salon();
        $staffId = (int) $data['staff_id'];

        try {
            DB::transaction(function () use ($salon, $appointment, $data, $staffId) {
                app(AppointmentBookingService::class)->acquireStaffBookingLocks($salon->id, [
                    (int) $appointment->staff_id,
                    $staffId,
                ]);

                $startsAt = SalonTime::parseAppointmentStartsAt($salon, $data['starts_at']);
                $endsAt   = $startsAt->copy()->addMinutes($appointment->duration_minutes);
                $staff    = Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->findOrFail($staffId);

                $result = app(AvailabilityService::class)->validateProposedWindow(
                    $salon,
                    $staff,
                    $startsAt,
                    $endsAt,
                    $appointment->id,
                    false,
                );

                if (! $result->ok) {
                    throw ValidationException::withMessages(['starts_at' => $result->firstMessage()]);
                }

                $appointment->update([
                    'client_id'      => $data['client_id'],
                    'staff_id'       => $staffId,
                    'starts_at'      => $startsAt->copy()->utc(),
                    'ends_at'        => $endsAt->copy()->utc(),
                    'internal_notes' => $data['internal_notes'] ?? null,
                    'client_notes'   => $data['client_notes'] ?? null,
                ]);
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

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

        try {
            $appointment = app(AppointmentBookingService::class)->reschedule($appointment, [
                'starts_at' => $data['starts_at'],
                'staff_id'  => $data['staff_id'] ?? null,
            ]);
        } catch (AvailabilityRejectedException $e) {
            return back()->withErrors(['starts_at' => $e->result->firstMessage()])->withInput();
        }

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
