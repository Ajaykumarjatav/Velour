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
use App\Services\AppointmentInvoiceService;
use App\Services\AppointmentService as AppointmentBookingService;
use App\Services\AvailabilityService;
use App\Services\NotificationService;
use App\Models\PosTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\Scheduling\AvailabilityRejectedException;
use App\Services\Scheduling\ScheduleValidationResult;
use App\Helpers\CurrencyHelper;
use App\Support\AppointmentDisplayLines;
use App\Support\DisplayFormatter;
use App\Support\SalonTime;
use App\Support\AppointmentLifecycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    use ResolvesActiveSalon;

    public function __construct(private NotificationService $notificationService) {}

    private function salon()
    {
        return $this->activeSalon();
    }

    public function index(Request $request): View|JsonResponse
    {
        $salon   = $this->salon();
        $search  = $request->get('search');
        $status  = $request->get('status');
        $staffId = $request->get('staff_id');
        $legacyDate = $request->get('date');
        $salonToday = SalonTime::todayDateString($salon);
        $allTimeFrom = SalonTime::earliestReportDateString($salon);
        $allTimeTo = SalonTime::now($salon)->copy()->addYears(2)->toDateString();
        if ($request->filled('from') || $request->filled('to')) {
            $from = SalonTime::clampReportFrom($salon, (string) $request->get('from', $salonToday));
            $to = (string) $request->get('to', $from);
        } elseif (is_string($legacyDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $legacyDate)) {
            $from = SalonTime::clampReportFrom($salon, $legacyDate);
            $to = $from;
        } else {
            $from = $allTimeFrom;
            $to = $allTimeTo;
        }
        if ($to < $from) {
            $to = $from;
        }
        $scopedStaffId = Auth::user()->dashboardScopedStaffId();
        if ($scopedStaffId !== null) {
            $staffId = $scopedStaffId;
        }

        $query = Appointment::withoutGlobalScopes()->where('salon_id', $salon->id)
            ->with(['client', 'staff', 'services', 'transaction.items'])
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

        if ($status === AppointmentLifecycle::DISPLAY_MISSED) {
            AppointmentLifecycle::scopeMissedUnresolved($query, $salon);
        } elseif ($status) {
            $query->where('status', $status);
        }

        [$rangeStartUtc, $rangeEndUtc] = SalonTime::ymdRangeUtcInclusive($salon, $from, $to);
        $query->whereBetween('starts_at', [$rangeStartUtc, $rangeEndUtc]);

        if ($staffId) {
            $query->where('staff_id', $staffId);
        }

        $appointments = $query->paginate(20)->withQueryString();
        $isScopedStaffPanel = $scopedStaffId !== null;
        $appointmentRows = $this->buildAppointmentIndexRows(
            $appointments->getCollection(),
            $salon,
            $isScopedStaffPanel
        );

        $selectedQuery = (int) $request->input('selected', 0);
        $firstApptId = $appointments->first()?->id;
        $initialSelectedAppointmentId = null;
        if ($firstApptId) {
            $initialSelectedAppointmentId = ($selectedQuery > 0 && $appointments->contains(fn (Appointment $a) => (int) $a->id === $selectedQuery))
                ? $selectedQuery
                : (int) $firstApptId;
        }

        $paginationHtml = $appointments->hasPages()
            ? (string) $appointments->links()
            : '';

        if ($request->boolean('ajax') || $request->wantsJson()) {
            return response()->json([
                'appointments' => $appointmentRows,
                'selected_id' => $initialSelectedAppointmentId,
                'pagination_html' => $paginationHtml,
            ]);
        }

        $staffQuery = Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->where('is_active', true);
        if ($scopedStaffId !== null) {
            $staffQuery->whereKey($scopedStaffId);
        }
        $staff = $staffQuery->withName()->get();

        return view('appointments.index', compact(
            'salon',
            'appointments',
            'appointmentRows',
            'paginationHtml',
            'staff',
            'search',
            'status',
            'from',
            'to',
            'allTimeFrom',
            'allTimeTo',
            'salonToday',
            'staffId',
            'initialSelectedAppointmentId'
        ));
    }

    /**
     * @param  Collection<int, Appointment>  $appointments
     * @return list<array<string, mixed>>
     */
    private function buildAppointmentIndexRows(Collection $appointments, $salon, bool $isScopedStaffPanel): array
    {
        $currency = $salon->currency ?? \App\Helpers\CurrencyHelper::defaultCode();

        return $appointments->map(function (Appointment $apt) use ($salon, $currency, $isScopedStaffPanel) {
            $st = $apt->status;
            $isMissed = AppointmentLifecycle::isPastUnresolved($apt, $salon);
            $displayStatus = AppointmentLifecycle::displayStatusKey($apt, $salon);
            $pay = $apt->derivePaymentStatusFromAmounts();
            $serviceLines = AppointmentDisplayLines::serviceLines($apt);
            $balanceDue = max(0, round((float) $apt->total_price - (float) $apt->amount_paid, 2));
            $isCompleted = $st === 'completed';

            return [
                'id' => (int) $apt->id,
                'client_name' => trim(($apt->client?->first_name ?? '') . ' ' . ($apt->client?->last_name ?? '')),
                'reference' => (string) $apt->reference,
                'service_summary' => $serviceLines->first()['name'] ?? ($apt->services->first()?->service_name ?? '—'),
                'service_extra' => max(0, $serviceLines->count() - 1),
                'staff_name' => $apt->staff?->name ?? '—',
                'starts_clock' => DisplayFormatter::businessClock($salon, $apt->starts_at),
                'starts_date' => DisplayFormatter::businessDate($salon, $apt->starts_at),
                'time_range' => DisplayFormatter::businessTimeRange($salon, $apt->starts_at, $apt->ends_at),
                'amount' => CurrencyHelper::format((float) $apt->total_price, $currency),
                'amount_paid' => CurrencyHelper::format((float) $apt->amount_paid, $currency),
                'balance_due' => CurrencyHelper::format($balanceDue, $currency),
                'has_balance' => $balanceDue > 0,
                'is_partial_payment' => $pay === Appointment::PAYMENT_PARTIAL || ($balanceDue > 0 && (float) $apt->amount_paid > 0),
                'deposit_paid' => (float) $apt->deposit_paid > 0
                    ? CurrencyHelper::format((float) $apt->deposit_paid, $currency)
                    : null,
                'invoice_pdf_url' => $isCompleted ? route('appointments.invoice.pdf', $apt) : null,
                'invoice_page_url' => $isCompleted ? route('appointments.invoice.show', $apt) : null,
                'status' => $st,
                'display_status' => $displayStatus,
                'is_missed' => $isMissed,
                'status_label' => AppointmentLifecycle::displayStatusLabel($apt, $salon),
                'status_url' => route('appointments.status', $apt->id),
                'source' => (string) ($apt->source ?? 'manual'),
                'source_label' => Appointment::sourceLabel($apt->source),
                'payment_status' => $pay,
                'payment_label' => Appointment::paymentStatusLabel($pay),
                'booked_at' => DisplayFormatter::businessDateTime($salon, $apt->created_at),
                'duration_minutes' => (int) $apt->duration_minutes,
                'client_notes' => $apt->client_notes,
                'internal_notes' => $isScopedStaffPanel ? null : $apt->internal_notes,
                'show_url' => route('appointments.show', $apt->id),
                'pos_url' => route('pos.create', ['appointment' => $apt->id]),
                'rebook_url' => route('appointments.create', ['client_id' => $apt->client_id, 'from' => $apt->id]),
                'rebook_same_url' => route('appointments.create', [
                    'client_id' => $apt->client_id,
                    'services' => $apt->services->pluck('service_id')->filter()->join(','),
                    'staff_id' => $apt->staff_id,
                    'from' => $apt->id,
                ]),
                'can_rebook' => in_array($st, ['completed', 'cancelled', 'no_show'], true) && ! $isMissed,
                'services' => $serviceLines->map(fn ($line) => [
                    'name' => $line['name'],
                    'price' => CurrencyHelper::format((float) $line['price'], $currency),
                    'duration' => $line['duration'],
                    'source' => $line['source'],
                ])->values()->all(),
            ];
        })->values()->all();
    }

    public function create(Request $request)
    {
        $salon    = $this->salon();
        $scopedStaffId = Auth::user()->dashboardScopedStaffId();
        if ($scopedStaffId !== null) {
            abort(403, 'Staff users cannot create appointments from this screen.');
        }

        if ($request->filled('from')) {
            $sourceAppt = Appointment::withoutGlobalScopes()
                ->where('salon_id', $salon->id)
                ->find((int) $request->query('from'));
            if ($sourceAppt && \App\Support\AppointmentLifecycle::isPastUnresolved($sourceAppt, $salon)) {
                return redirect()
                    ->route('appointments.show', $sourceAppt)
                    ->with('error', 'This appointment was missed. Mark it as no-show, complete, or cancel it before rebooking.');
            }
        }

        $prefillClientId = old('client_id', $request->query('client_id'));
        $prefillStaffId  = old('staff_id', $request->query('staff_id'));
        $prefillServices = old('services', []);
        if ($prefillServices === [] && $request->filled('services')) {
            $prefillServices = array_values(array_filter(array_map(
                'intval',
                explode(',', (string) $request->query('services'))
            )));
        }

        $clients  = Client::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(30)
            ->get(['id', 'first_name', 'last_name', 'phone']);
        if ($prefillClientId && ! $clients->firstWhere('id', (int) $prefillClientId)) {
            $extra = Client::withoutGlobalScopes()
                ->where('salon_id', $salon->id)
                ->find((int) $prefillClientId, ['id', 'first_name', 'last_name', 'phone']);
            if ($extra) {
                $clients->prepend($extra);
            }
        }
        $staffQuery = Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->where('is_active', true);
        if ($scopedStaffId !== null) {
            $staffQuery->whereKey($scopedStaffId);
        }
        $staff    = $staffQuery->withName()->get();
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

        $defaultStaffId = (string) ($prefillStaffId ?: '');
        if ($scopedStaffId !== null) {
            $defaultStaffId = (string) $scopedStaffId;
        }
        $todayYmd = SalonTime::todayDateString($salon);
        $isRebookPrefill = $request->hasAny(['client_id', 'services', 'staff_id']);

        return view('appointments.create', compact(
            'salon',
            'clients',
            'staff',
            'services',
            'clientQuickCreateLoyaltyTiers',
            'scopedStaffId',
            'defaultStaffId',
            'todayYmd',
            'prefillClientId',
            'prefillServices',
            'isRebookPrefill'
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

        $slotTimes = \App\Support\AppointmentSlotGrid::allTimes();

        $dateStr = $data['date'];
        $isTodayInSalon = $dateStr === SalonTime::todayDateString($salon);
        $nowInSalon = SalonTime::now($salon);

        $staffMember = Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->whereKey($staffId)->firstOrFail();

        $tz = SalonTime::timezone($salon);

        $dayBlock = app(\App\Services\StaffAttendanceService::class)->daySchedulingBlockReason($salon, $staffMember, $dateStr);
        if ($dayBlock !== null) {
            $blockedDetails = collect($slotTimes)->mapWithKeys(fn ($t) => [$t => $dayBlock])->all();

            return response()->json([
                'blocked'                    => $slotTimes,
                'blocked_details'            => $blockedDetails,
                'assumed_duration_minutes'   => $maxMinutes,
                'day_blocked'                => true,
            ]);
        }
        $availability = app(AvailabilityService::class);

        $blocked = [];
        $blockedDetails = [];

        foreach ($slotTimes as $time) {
            $start = Carbon::createFromFormat('Y-m-d H:i', $dateStr . ' ' . $time, $tz);
            $end   = $start->copy()->addMinutes($maxMinutes);

            if ($isTodayInSalon && $start->lte($nowInSalon)) {
                $blocked[] = $time;
                $blockedDetails[$time] = 'That time has already passed. Please choose a later time.';
                continue;
            }

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
        if ($scopedStaffId !== null) {
            return back()->withErrors(['status' => 'Staff users cannot create appointments from this screen.']);
        }

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
            'source'               => ['required', Rule::in(Appointment::bookingSourceKeys())],
            'payment_status'       => ['required', Rule::in(Appointment::paymentStatusKeys())],
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

        $startsAtLocal = SalonTime::parseAppointmentStartsAt($salon, $data['starts_at']);
        if ($startsAtLocal->lte(SalonTime::now($salon))) {
            return back()->withErrors(['starts_at' => 'Please choose a future time slot.'])->withInput();
        }

        $relaxedBookingDay = SalonTime::isTodayOrTomorrow($salon, $startsAtLocal->toDateString());

        try {
            $appointment = app(AppointmentBookingService::class)->create($salon->id, [
                'client_id'         => (int) $data['client_id'],
                'staff_id'          => (int) $data['staff_id'],
                'starts_at'         => $data['starts_at'],
                'service_ids'       => $orderedIds,
                'service_options'   => $options,
                'source'            => $data['source'],
                'payment_status'    => $data['payment_status'],
                'internal_notes'    => $data['internal_notes'] ?? null,
                'client_notes'      => $data['client_notes'] ?? null,
            ], [
                'enforce_staff_services' => false,
                'enforce_availability'   => ! $relaxedBookingDay,
            ]);
        } catch (AvailabilityRejectedException $e) {
            return back()->withErrors(['starts_at' => $e->result->firstMessage()])->withInput();
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['services' => $e->getMessage()])->withInput();
        }

        $this->notificationService->appointmentConfirmation(
            $appointment->fresh(['client', 'staff', 'services.service', 'salon'])
        );

        return redirect()->route('appointments.index')->with('success', 'Appointment booked successfully.');
    }

    public function show(Appointment $appointment)
    {
        $this->authorise($appointment);
        $appointment->load(['client', 'staff', 'services', 'transaction.items', 'review']);

        // Fix stale rows where payment_status says paid but amount_paid is still 0.
        if ($appointment->reconcilePaymentStatus()) {
            $appointment->saveQuietly();
        }

        $displayServiceLines = AppointmentDisplayLines::serviceLines($appointment);
        $salon = $this->salon();
        $staff = Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->where('is_active', true)->withName()->get();

        return view('appointments.show', compact('appointment', 'staff', 'salon', 'displayServiceLines'));
    }

    public function invoicePdf(Appointment $appointment)
    {
        $this->authorise($appointment);

        $transaction = AppointmentInvoiceService::ensurePosTransaction($appointment);
        if (! $transaction) {
            abort(422, __('Invoice is only available for completed appointments with an assigned staff member.'));
        }

        $this->authorize('view', $transaction);

        $transaction->loadMissing(['client', 'items', 'salon', 'staff']);

        $pdf = Pdf::loadView('pos.invoice-pdf', ['transaction' => $transaction])
            ->setPaper('a4', 'portrait');

        $safeRef = preg_replace('/[^A-Za-z0-9._-]+/', '-', (string) $transaction->reference) ?: 'invoice';

        return $pdf->download('invoice-'.$safeRef.'.pdf');
    }

    public function invoiceShow(Appointment $appointment): RedirectResponse
    {
        $this->authorise($appointment);

        $transaction = AppointmentInvoiceService::ensurePosTransaction($appointment);
        if (! $transaction) {
            abort(422, __('Invoice is only available for completed appointments with an assigned staff member.'));
        }

        $this->authorize('view', $transaction);

        return redirect()->route('pos.show', $transaction);
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

        $todayYmd = SalonTime::todayDateString($salon);

        return view('appointments.edit', compact('appointment', 'clients', 'staff', 'services', 'clientQuickCreateLoyaltyTiers', 'todayYmd'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->authorise($appointment);

        $data = $request->validate([
            'client_id'        => ['required', 'exists:clients,id'],
            'staff_id'         => ['required', 'exists:staff,id'],
            'starts_at'        => ['required', 'date'],
            'source'           => ['required', Rule::in(Appointment::bookingSourceKeys())],
            'payment_status'   => ['required', Rule::in(Appointment::paymentStatusKeys())],
            'internal_notes'   => ['nullable', 'string', 'max:1000'],
            'client_notes'     => ['nullable', 'string', 'max:1000'],
        ]);

        $salon   = $this->salon();
        $staffId = (int) $data['staff_id'];
        $startsAtLocal = SalonTime::parseAppointmentStartsAt($salon, $data['starts_at']);
        if ($startsAtLocal->lte(SalonTime::now($salon))) {
            return back()->withErrors(['starts_at' => 'Please choose a future time slot.'])->withInput();
        }

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

                $paymentStatus = $data['payment_status'];
                $amountPaid    = (float) $appointment->amount_paid;
                if ($paymentStatus === Appointment::PAYMENT_PAID && $amountPaid <= 0.009) {
                    $amountPaid = (float) $appointment->total_price;
                } elseif ($paymentStatus === Appointment::PAYMENT_UNPAID) {
                    // Keep recorded payments; status will be reconciled from amounts on save.
                } elseif ($paymentStatus === Appointment::PAYMENT_PARTIAL && $amountPaid <= 0.009) {
                    $paymentStatus = Appointment::PAYMENT_UNPAID;
                }

                $appointment->update([
                    'client_id'        => $data['client_id'],
                    'staff_id'         => $staffId,
                    'starts_at'        => $startsAt->copy()->utc(),
                    'ends_at'          => $endsAt->copy()->utc(),
                    'source'           => $data['source'],
                    'payment_status'   => $paymentStatus,
                    'amount_paid'      => $amountPaid,
                    'internal_notes'   => $data['internal_notes'] ?? null,
                    'client_notes'     => $data['client_notes'] ?? null,
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
        if (Auth::user()->dashboardScopedStaffId() !== null && $data['status'] === 'cancelled') {
            return back()->withErrors(['status' => 'Staff users cannot cancel bookings.']);
        }

        $previous = $appointment->status;
        $redirectTarget = $request->input('redirect');

        if ($data['status'] === 'confirmed' && $previous !== 'confirmed') {
            try {
                DB::transaction(function () use ($appointment, $previous) {
                    $locked = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();

                    if ((int) $locked->staff_id > 0) {
                        app(AppointmentBookingService::class)->acquireStaffBookingLocks(
                            (int) $locked->salon_id,
                            [(int) $locked->staff_id]
                        );
                    }

                    $result = $this->validateConfirmSlot($locked);
                    if (! $result->ok) {
                        throw new AvailabilityRejectedException($result);
                    }

                    $this->finalizeConfirmation($locked, $previous);
                });
            } catch (AvailabilityRejectedException $e) {
                $appointment->loadMissing(['staff']);

                return $this->redirectConfirmBlocked($appointment, $e->result, $redirectTarget);
            }

            $success = 'Booking confirmed successfully. The client has been notified.';

            if ($redirectTarget === 'tasks') {
                return redirect()->route('tasks.index')->with('success', $success);
            }

            return back()->with('success', $success);
        }

        $appointment->update(['status' => $data['status']]);
        $fresh = $appointment->fresh(['client', 'staff', 'services.service', 'salon']);

        if (in_array($data['status'], ['cancelled', 'no_show'], true) && $previous !== $data['status']) {
            $this->notificationService->appointmentCancellation($fresh, $previous === 'pending');
        }

        if ($redirectTarget === 'tasks' && in_array($data['status'], ['cancelled', 'no_show', 'completed'], true)) {
            return redirect()->route('tasks.index')->with('success', 'Status updated.');
        }

        return back()->with('success', 'Status updated.');
    }

    /* ── Dedicated action methods ─────────────────────────────────────────── */

    public function confirm(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorise($appointment);

        if ($appointment->status !== 'pending') {
            return back()->withErrors(['status' => 'Only pending appointments can be confirmed.']);
        }

        $redirectTarget = $request->input('redirect');

        try {
            DB::transaction(function () use ($appointment) {
                $locked = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();

                if ($locked->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'status' => 'Only pending appointments can be confirmed.',
                    ]);
                }

                if ((int) $locked->staff_id > 0) {
                    app(AppointmentBookingService::class)->acquireStaffBookingLocks(
                        (int) $locked->salon_id,
                        [(int) $locked->staff_id]
                    );
                }

                $result = $this->validateConfirmSlot($locked);
                if (! $result->ok) {
                    throw new AvailabilityRejectedException($result);
                }

                $this->finalizeConfirmation($locked, 'pending');
            });
        } catch (AvailabilityRejectedException $e) {
            $appointment->loadMissing(['staff']);

            return $this->redirectConfirmBlocked($appointment, $e->result, $redirectTarget);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $success = 'Booking confirmed successfully. The client has been notified.';

        if ($redirectTarget === 'tasks') {
            return redirect()->route('tasks.index')->with('success', $success);
        }

        return back()->with('success', $success);
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorise($appointment);
        if (Auth::user()->dashboardScopedStaffId() !== null) {
            return back()->withErrors(['status' => 'Staff users cannot cancel bookings.']);
        }

        if (in_array($appointment->status, ['completed', 'cancelled', 'no_show'])) {
            return back()->withErrors(['status' => 'This appointment cannot be cancelled.']);
        }

        $data = $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $wasPending = $appointment->status === 'pending';

        $appointment->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => $data['cancellation_reason'] ?? null,
        ]);

        $this->notificationService->appointmentCancellation(
            $appointment->fresh(['client', 'staff', 'services.service', 'salon']),
            $wasPending
        );

        if ($request->input('redirect') === 'tasks') {
            return redirect()->route('tasks.index')->with('success', 'Appointment cancelled.');
        }

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

        $this->notificationService->appointmentRescheduled(
            $appointment->fresh(['client', 'staff', 'services.service', 'salon']),
            $originalStartsAt
        );

        if ($request->input('redirect') === 'tasks') {
            return redirect()->route('tasks.index')->with('success', 'Appointment rescheduled.');
        }

        return back()->with('success', 'Appointment rescheduled.');
    }

    public function complete(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorise($appointment);

        if (! in_array($appointment->status, ['confirmed', 'checked_in', 'in_progress'])) {
            return back()->withErrors(['status' => 'Only confirmed or in-progress appointments can be marked as completed.']);
        }

        // Payment must be collected before completing — redirect to POS
        if (! $appointment->isFullyPaid()) {
            return redirect()
                ->route('pos.create', ['appointment' => $appointment->id])
                ->with('info', __('Please collect payment to complete this appointment.'));
        }

        $appointment->update(['status' => 'completed']);

        $client = $appointment->client;
        $client->increment('visit_count');
        $client->update(['last_visit_at' => $appointment->starts_at]);

        $success = __('Appointment completed successfully.');
        if ($request->input('redirect') === 'tasks') {
            return redirect()->route('tasks.index')->with('success', $success);
        }

        return redirect()
            ->route('appointments.show', $appointment->id)
            ->with('success', $success);
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

    private function validateConfirmSlot(Appointment $appointment): ScheduleValidationResult
    {
        $appointment->loadMissing(['salon', 'staff']);
        $staff = $appointment->staff;

        if (! $staff) {
            return ScheduleValidationResult::failure([
                ['code' => 'no_staff', 'message' => 'Assign a staff member before confirming this booking.'],
            ]);
        }

        return app(AvailabilityService::class)->validateProposedWindow(
            $appointment->salon,
            $staff,
            $appointment->starts_at->copy(),
            AppointmentLifecycle::slotEndsAt($appointment),
            (int) $appointment->id,
            false,
        );
    }

    private function redirectConfirmBlocked(
        Appointment $appointment,
        ScheduleValidationResult $result,
        ?string $redirectTarget,
    ): RedirectResponse {
        $staffName = $appointment->staff?->name ?? 'The assigned staff member';
        $detail = $result->firstMessage();
        $message = "Booking not confirmed — {$staffName} is not available at this time. {$detail} Please reschedule to a different slot.";

        $payload = [
            'error' => $message,
            'booking_confirm_conflict_id' => $appointment->id,
            'booking_confirm_conflict_message' => $detail,
        ];

        if ($redirectTarget === 'tasks') {
            return redirect()->route('tasks.index')->with($payload);
        }

        return back()->with($payload);
    }

    private function finalizeConfirmation(Appointment $appointment, string $previousStatus): void
    {
        $appointment->update([
            'status'       => 'confirmed',
            'confirmed_at' => $appointment->confirmed_at ?? now(),
        ]);

        if (in_array($previousStatus, ['pending', 'hold'], true)) {
            $this->notificationService->notifyClientBookingConfirmed(
                $appointment->fresh(['client', 'staff', 'services.service', 'salon']),
                true
            );
        }
    }
}
