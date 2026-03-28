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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        $salon = Salon::where('slug', $salonSlug)->where('is_active', true)->firstOrFail();
        $staff = Staff::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salon->id)
            ->where('is_active', true)
            ->where('bookable_online', true)
            ->when($request->service_id, fn($q) =>
                $q->whereHas('services', fn($s) => $s->where('service_id', $request->service_id))
            )
            ->orderBy('sort_order')
            ->get(['id','first_name','last_name','role','initials','color','avatar','bio','specialisms']);

        return response()->json(['staff' => $staff]);
    }

    /* ── GET /book/{slug}/availability ─────────────────────────────────── */
    public function availability(Request $request, string $salonSlug): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'date'       => 'required|date_format:Y-m-d|after_or_equal:today',
            'staff_id'   => 'nullable|integer|exists:staff,id',
        ]);

        $salon = Salon::where('slug', $salonSlug)->where('is_active', true)->firstOrFail();

        abort_unless($salon->online_booking_enabled, 503, 'Online booking is unavailable');

        $service = Service::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salon->id)
            ->where('status', 'active')
            ->where('online_bookable', true)
            ->findOrFail($validated['service_id']);

        // ✅ FIX: Better date parsing with proper formatting
        try {
            $date = Carbon::createFromFormat('Y-m-d', $validated['date'])->startOfDay();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 422);
        }

        // Enforce booking advance days limit
        $maxDays = $salon->booking_advance_days ?? 60;
        if ($date->diffInDays(now()->startOfDay(), false) < -$maxDays) {
            return response()->json([
                'error' => "Bookings can only be made up to $maxDays days in advance"
            ], 422);
        }

        $slots = $this->bookingService->getAvailableSlots(
            $salon->id,
            $service,
            $date,
            $validated['staff_id'] ?? null
        );

        return response()->json([
            'date'    => $validated['date'],
            'service' => $service->only(['id', 'name', 'duration_minutes', 'price']),
            'slots'   => $slots,
        ]);
    }

    /* ── POST /book/{slug}/hold ─────────────────────────────────────────── */
    public function hold(Request $request, string $salonSlug): JsonResponse
    {
        $data = $request->validate([
            'service_ids'  => 'required|array',
            'service_ids.*'=> 'integer',
            'staff_id'     => 'nullable|integer',
            'starts_at'    => 'required|date|after:now',
        ]);

        $salon = Salon::where('slug', $salonSlug)->where('is_active', true)->firstOrFail();

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

            return response()->json([
                'message'     => 'Appointment confirmed!',
                'reference'   => $appointment->reference,
                'appointment' => $appointment->load(['services', 'staff:id,first_name,last_name']),
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

        $appointment = $this->bookingService->reschedule($appointment, $data);
        $this->notificationService->appointmentRescheduled($appointment);

        return response()->json([
            'message'    => 'Appointment rescheduled.',
            'starts_at'  => $appointment->starts_at,
        ]);
    }
}
