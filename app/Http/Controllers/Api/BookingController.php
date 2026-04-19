<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Appointment;
use App\Models\Client;
use App\Scopes\TenantScope;
use App\Services\BookingService;
use App\Services\NotificationService;
use App\Services\Scheduling\AvailabilityRejectedException;
use App\Support\SalonTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function __construct(
        private BookingService      $bookingService,
        private NotificationService $notificationService,
    ) {}

    /* ── GET /book/{slug} ───────────────────────────────────────────────── */
    public function info(Request $request, string $salonSlug): JsonResponse
    {
        $salon = Salon::where('slug', $salonSlug)
            ->where('is_active', true)
            ->firstOrFail();

        if (! $salon->online_booking_enabled) {
            return response()->json(['message' => 'Online booking is currently unavailable.'], 503);
        }

        return response()->json([
            'salon' => $salon->only([
                'id', 'name', 'description', 'phone', 'email', 'address_line1',
                'city', 'postcode', 'logo', 'cover_image', 'currency',
                'deposit_required', 'deposit_percentage', 'instant_confirmation',
                'cancellation_hours', 'booking_advance_days', 'opening_hours',
            ]),
        ]);
    }

    /* ── GET /book/{slug}/services ──────────────────────────────────────── */
    public function services(Request $request, string $salonSlug): JsonResponse
    {
        $salon    = Salon::where('slug', $salonSlug)->where('is_active', true)->firstOrFail();
        $services = Service::withoutGlobalScope(TenantScope::class)
            ->with(['category', 'staff:id,first_name,last_name,initials,color,avatar'])
            ->where('salon_id', $salon->id)
            ->where('status', 'active')
            ->where('online_bookable', true)
            ->where('show_in_menu', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category_id');

        return response()->json(['services' => $services]);
    }

    /* ── GET /book/{slug}/staff ─────────────────────────────────────────── */
    public function staff(Request $request, string $salonSlug): JsonResponse
    {
        $validated = $request->validate([
            'service_id'    => ['nullable', 'integer'],
            'service_ids'   => ['nullable', 'array'],
            'service_ids.*' => ['integer'],
        ]);

        $salon = Salon::where('slug', $salonSlug)->where('is_active', true)->firstOrFail();

        $ids = array_values(array_unique(array_merge(
            $validated['service_ids'] ?? [],
            isset($validated['service_id']) ? [(int) $validated['service_id']] : []
        )));

        $query = Staff::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salon->id)
            ->where('is_active', true)
            ->where('bookable_online', true);

        foreach ($ids as $serviceId) {
            $query->whereHas('services', fn ($s) => $s->where('services.id', $serviceId));
        }

        $staff = $query
            ->orderBy('sort_order')
            ->get(['id', 'first_name', 'last_name', 'role', 'initials', 'color', 'avatar', 'bio', 'specialisms']);

        return response()->json(['staff' => $staff]);
    }

    /* ── GET /book/{slug}/availability ─────────────────────────────────── */
    public function availability(Request $request, string $salonSlug): JsonResponse
    {
        $validated = $request->validate([
            'service_id'    => ['nullable', 'integer'],
            'service_ids'   => ['nullable', 'array'],
            'service_ids.*' => ['integer'],
            'date'          => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'staff_id'      => ['nullable', 'integer', 'exists:staff,id'],
        ]);

        $ids = array_values(array_unique(array_merge(
            $validated['service_ids'] ?? [],
            isset($validated['service_id']) ? [(int) $validated['service_id']] : []
        )));

        if ($ids === []) {
            return response()->json(['message' => 'Provide service_id or service_ids.'], 422);
        }

        $salon = Salon::where('slug', $salonSlug)->where('is_active', true)->firstOrFail();

        abort_unless($salon->online_booking_enabled, 503, 'Online booking is unavailable');

        try {
            $services = $this->orderedOnlineServices($salon, $ids);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $validated['date'])->startOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 422);
        }

        $maxDays = $salon->booking_advance_days ?? 60;
        if ($date->diffInDays(now()->startOfDay(), false) < -$maxDays) {
            return response()->json([
                'error' => "Bookings can only be made up to $maxDays days in advance",
            ], 422);
        }

        $slots = $this->bookingService->getAvailableSlots(
            $salon->id,
            $services,
            $date,
            $validated['staff_id'] ?? null
        );

        $first = $services->first();

        return response()->json([
            'date'     => $validated['date'],
            'service'  => $first->only(['id', 'name', 'duration_minutes', 'price']),
            'services' => $services->map(fn (Service $s) => $s->only(['id', 'name', 'duration_minutes', 'price']))->values()->all(),
            'combined' => [
                'duration_minutes'      => (int) $services->sum('duration_minutes'),
                'appointment_minutes'   => BookingService::combinedDurationMinutes($services, $salon->id),
                'price'                 => round((float) $services->sum('price'), 2),
            ],
            'slots'    => $slots,
        ]);
    }

    /* ── POST /book/{slug}/hold ─────────────────────────────────────────── */
    public function hold(Request $request, string $salonSlug): JsonResponse
    {
        $data = $request->validate([
            'service_ids'       => 'required|array|min:1',
            'service_ids.*'     => 'integer',
            'service_options'   => 'nullable|array',
            'service_options.*' => 'array',
            'staff_id'          => 'nullable|integer',
            'starts_at'         => 'required|date|after:now',
        ]);

        $salon = Salon::where('slug', $salonSlug)->where('is_active', true)->firstOrFail();

        abort_unless($salon->online_booking_enabled, 503, 'Online booking is unavailable');

        $data['service_ids'] = array_values(array_unique(array_map('intval', $data['service_ids'])));

        $data['staff_id'] = isset($data['staff_id']) ? (int) $data['staff_id'] : null;
        if ($data['staff_id'] === 0) {
            $data['staff_id'] = null;
        }

        try {
            $this->orderedOnlineServices($salon, $data['service_ids']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($data['staff_id'] !== null) {
            $stylist = Staff::withoutGlobalScope(TenantScope::class)
                ->where('salon_id', $salon->id)
                ->where('id', $data['staff_id'])
                ->where('is_active', true)
                ->where('bookable_online', true)
                ->first();
            if (! $stylist) {
                return response()->json(['message' => 'That stylist is not available for online booking.'], 422);
            }
            foreach ($data['service_ids'] as $sid) {
                if (! $stylist->services()->where('services.id', (int) $sid)->exists()) {
                    return response()->json(['message' => 'That stylist cannot perform one of the selected services.'], 422);
                }
            }
        }

        try {
            $holdToken = $this->bookingService->holdSlot($salon->id, $data);
            return response()->json([
                'hold_token' => $holdToken,
                'expires_at' => now()->addMinutes(10)->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    /* ── POST /book/{slug}/confirm ──────────────────────────────────────── */
    public function confirm(Request $request, string $salonSlug): JsonResponse
    {
        $data = $request->validate([
            'hold_token'      => 'required|string',
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'required|string|max:100',
            'email'           => 'nullable|email',
            'phone'           => 'required|string|max:30',
            'notes'           => 'nullable|string|max:500',
            'marketing_consent' => 'nullable|boolean',
            'stripe_payment_intent_id' => 'nullable|string',
        ]);

        $salon = Salon::where('slug', $salonSlug)->where('is_active', true)->firstOrFail();

        if (!$salon->new_client_booking_enabled) {
            // Check if client exists
            $exists = Client::withoutGlobalScope(TenantScope::class)
                ->where('salon_id', $salon->id)
                ->where(fn($q) => $q->where('email', $data['email'] ?? '')
                                    ->orWhere('phone', $data['phone']))
                ->exists();
            if (!$exists) {
                return response()->json(['message' => 'New client online booking is not currently available.'], 403);
            }
        }

        try {
            $appointment = $this->bookingService->confirmFromHold($salon, $data);
            $this->notificationService->appointmentConfirmation($appointment);

            $tz     = SalonTime::timezone($salon);
            $starts = $appointment->starts_at->timezone($tz);

            return response()->json([
                'message'     => 'Appointment confirmed!',
                'reference'   => $appointment->reference,
                'appointment' => $appointment->load(['services', 'staff:id,first_name,last_name']),
                'display'     => [
                    'time'       => $starts->format('H:i'),
                    'date_long'  => $starts->format('l, j F Y'),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /* ── GET /book/{slug}/appointment/{ref} ─────────────────────────────── */
    public function getByRef(Request $request, string $salonSlug, string $ref): JsonResponse
    {
        $salon = Salon::where('slug', $salonSlug)->firstOrFail();
        $appointment = Appointment::withoutGlobalScope(TenantScope::class)
            ->with(['client', 'staff:id,first_name,last_name,avatar', 'services'])
            ->where('salon_id', $salon->id)
            ->where('reference', $ref)
            ->firstOrFail();

        return response()->json($appointment);
    }

    /* ── POST /book/{slug}/cancel/{ref} ─────────────────────────────────── */
    public function cancel(Request $request, string $salonSlug, string $ref): JsonResponse
    {
        $data  = $request->validate(['reason' => 'nullable|string|max:500']);
        $salon = Salon::where('slug', $salonSlug)->firstOrFail();

        $appointment = Appointment::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salon->id)
            ->where('reference', $ref)
            ->whereIn('status', ['confirmed', 'pending'])
            ->firstOrFail();

        // Enforce cancellation window
        $hoursUntil = now()->diffInHours(Carbon::parse($appointment->starts_at), false);
        if ($hoursUntil < $salon->cancellation_hours) {
            return response()->json([
                'message' => "Appointments must be cancelled at least {$salon->cancellation_hours} hours in advance.",
            ], 422);
        }

        $appointment->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => $data['reason'] ?? 'Cancelled by client',
        ]);

        $this->notificationService->appointmentCancellation($appointment);

        return response()->json(['message' => 'Appointment cancelled.']);
    }

    /* ── POST /book/{slug}/reschedule/{ref} ─────────────────────────────── */
    public function reschedule(Request $request, string $salonSlug, string $ref): JsonResponse
    {
        $data  = $request->validate([
            'starts_at' => 'required|date|after:now',
            'staff_id'  => 'nullable|integer',
        ]);

        $salon = Salon::where('slug', $salonSlug)->firstOrFail();

        $appointment = Appointment::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salon->id)
            ->where('reference', $ref)
            ->whereIn('status', ['confirmed', 'pending'])
            ->firstOrFail();

        // Capture original time before reschedule mutates the model
        $originalStartsAt = $appointment->starts_at->copy();

        try {
            $appointment = $this->bookingService->reschedule($appointment, $data);
        } catch (AvailabilityRejectedException $e) {
            return response()->json([
                'message' => $e->result->firstMessage(),
                'reasons' => $e->result->reasons,
            ], 422);
        }

        $this->notificationService->notifyTenantReschedule($appointment, $originalStartsAt);

        return response()->json([
            'message'    => 'Appointment rescheduled.',
            'starts_at'  => $appointment->starts_at,
        ]);
    }

    /**
     * Active, online-bookable services for this salon, in the order given by $ids.
     *
     * @param  list<int>  $ids
     * @return Collection<int, Service>
     */
    private function orderedOnlineServices(Salon $salon, array $ids): Collection
    {
        $ordered = array_values(array_unique(array_map('intval', $ids)));
        if ($ordered === []) {
            throw new \InvalidArgumentException('Select at least one service.');
        }

        $found = Service::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salon->id)
            ->where('status', 'active')
            ->where('online_bookable', true)
            ->whereIn('id', $ordered)
            ->get()
            ->keyBy('id');

        $out = collect();
        foreach ($ordered as $id) {
            if (! $found->has($id)) {
                throw new \InvalidArgumentException('One or more services are invalid or not available for online booking.');
            }
            $out->push($found->get($id));
        }

        return $out;
    }
}
