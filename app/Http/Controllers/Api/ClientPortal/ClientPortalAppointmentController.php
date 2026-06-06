<?php

namespace App\Http\Controllers\Api\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Scopes\TenantScope;
use App\Services\AppointmentInvoiceService;
use App\Services\BookingService;
use App\Services\NotificationService;
use App\Services\Scheduling\AvailabilityRejectedException;
use App\Support\SalonTime;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ClientPortalAppointmentController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private NotificationService $notificationService,
    ) {}

    public function index(Request $request, string $salonSlug): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();
        $salon = $request->attributes->get('salon');
        $tz = SalonTime::timezone($salon);

        $query = Appointment::withoutGlobalScope(TenantScope::class)
            ->with(['staff:id,first_name,last_name,avatar,role', 'services.service:id,name'])
            ->where('salon_id', $salon->id)
            ->where('client_id', $client->id);

        $status = $request->query('status', 'upcoming');
        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', '%'.$search.'%')
                    ->orWhereHas('services', fn ($sq) => $sq->where('service_name', 'like', '%'.$search.'%'));
            });
        }

        match ($status) {
            'completed' => $query->where('status', 'completed'),
            'cancelled' => $query->whereIn('status', ['cancelled', 'no_show']),
            default => $query->whereIn('status', ['confirmed', 'pending'])
                ->where('starts_at', '>=', now()),
        };

        $appointments = $query
            ->orderByDesc('starts_at')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'appointments' => $appointments->through(fn (Appointment $a) => $this->formatSummary($a, $tz)),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page'    => $appointments->lastPage(),
                'total'        => $appointments->total(),
            ],
        ]);
    }

    public function show(Request $request, string $salonSlug, string $ref): JsonResponse
    {
        $appointment = $this->resolveOwnedAppointment($request, $ref);
        $salon = $request->attributes->get('salon');
        $tz = SalonTime::timezone($salon);

        $appointment->load([
            'staff:id,first_name,last_name,avatar,role,bio,phone,email',
            'services.service:id,name,duration_minutes',
            'transaction.items',
            'review:id,appointment_id,rating,comment,created_at',
        ]);

        return response()->json([
            'appointment' => $this->formatDetail($appointment, $tz),
        ]);
    }

    public function cancel(Request $request, string $salonSlug, string $ref): JsonResponse
    {
        $data = $request->validate(['reason' => 'nullable|string|max:500']);
        $appointment = $this->resolveOwnedAppointment($request, $ref);
        $salon = $request->attributes->get('salon');

        if (! in_array($appointment->status, ['confirmed', 'pending'], true)) {
            return response()->json(['message' => 'This appointment cannot be cancelled.'], 422);
        }

        $hoursUntil = now()->diffInHours(Carbon::parse($appointment->starts_at), false);
        if ($hoursUntil < ($salon->cancellation_hours ?? 0)) {
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

    public function reschedule(Request $request, string $salonSlug, string $ref): JsonResponse
    {
        $data = $request->validate([
            'starts_at' => 'required|date|after:now',
            'staff_id'  => 'nullable|integer',
        ]);

        $appointment = $this->resolveOwnedAppointment($request, $ref);
        $salon = $request->attributes->get('salon');

        if (! in_array($appointment->status, ['confirmed', 'pending'], true)) {
            return response()->json(['message' => 'This appointment cannot be rescheduled.'], 422);
        }

        $hoursUntil = now()->diffInHours(Carbon::parse($appointment->starts_at), false);
        if ($hoursUntil < ($salon->cancellation_hours ?? 0)) {
            return response()->json([
                'message' => "Appointments must be rescheduled at least {$salon->cancellation_hours} hours in advance.",
            ], 422);
        }

        $originalStartsAt = $appointment->starts_at->copy();

        try {
            $appointment = $this->bookingService->reschedule($appointment, $data);
        } catch (AvailabilityRejectedException $e) {
            return response()->json([
                'message' => $e->result->firstMessage(),
            ], 422);
        }

        $this->notificationService->notifyTenantReschedule($appointment, $originalStartsAt);
        $this->notificationService->sendClientRescheduleConfirmation($appointment, $originalStartsAt);

        $tz = SalonTime::timezone($salon);

        return response()->json([
            'message'     => 'Appointment rescheduled.',
            'appointment' => $this->formatSummary($appointment->fresh()->load(['staff', 'services']), $tz),
        ]);
    }

    public function invoice(Request $request, string $salonSlug, string $ref): JsonResponse
    {
        $appointment = $this->resolveOwnedAppointment($request, $ref);
        $transaction = AppointmentInvoiceService::ensurePosTransaction($appointment);

        if (! $transaction) {
            return response()->json(['message' => 'Invoice is not available for this appointment yet.'], 422);
        }

        $transaction->load(['client', 'staff', 'items', 'salon']);

        return response()->json([
            'invoice' => [
                'number'          => $transaction->reference,
                'subtotal'        => (float) $transaction->subtotal,
                'tax'             => (float) ($transaction->tax ?? 0),
                'discount'        => (float) ($transaction->discount ?? 0),
                'total'           => (float) $transaction->total,
                'payment_method'  => $transaction->payment_method,
                'payment_status'  => $transaction->status,
                'items'           => $transaction->items->map(fn ($item) => [
                    'name'     => $item->name,
                    'quantity' => $item->quantity,
                    'price'    => (float) $item->price,
                    'total'    => (float) $item->total,
                ]),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    public function invoicePdf(Request $request, string $salonSlug, string $ref)
    {
        $appointment = $this->resolveOwnedAppointment($request, $ref);
        $transaction = AppointmentInvoiceService::ensurePosTransaction($appointment);

        if (! $transaction) {
            return response()->json(['message' => 'Invoice is not available for this appointment yet.'], 422);
        }

        $transaction->loadMissing(['client', 'items', 'salon', 'staff']);

        $pdf = Pdf::loadView('pos.invoice-pdf', ['transaction' => $transaction])
            ->setPaper('a4', 'portrait');

        $safeRef = preg_replace('/[^A-Za-z0-9._-]+/', '-', (string) $transaction->reference) ?: 'invoice';

        return $pdf->download('invoice-'.$safeRef.'.pdf');
    }

    private function resolveOwnedAppointment(Request $request, string $ref): Appointment
    {
        /** @var Client $client */
        $client = $request->user();
        $salon = $request->attributes->get('salon');

        return Appointment::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salon->id)
            ->where('client_id', $client->id)
            ->where('reference', $ref)
            ->firstOrFail();
    }

    private function formatSummary(Appointment $appointment, string $tz): array
    {
        $starts = $appointment->starts_at->timezone($tz);

        return [
            'reference'       => $appointment->reference,
            'status'          => $appointment->status,
            'payment_status'  => $appointment->payment_status,
            'total_price'     => (float) $appointment->total_price,
            'amount_paid'     => (float) $appointment->amount_paid,
            'balance_due'     => (float) $appointment->balance_due,
            'starts_at'       => $starts->toIso8601String(),
            'display'         => [
                'date'      => $starts->format('j M Y'),
                'time'      => $starts->format('H:i'),
                'date_long' => $starts->format('l, j F Y'),
            ],
            'services'        => $appointment->services->map(fn ($s) => [
                'id'                => $s->service_id,
                'name'              => $s->service_name ?? $s->service?->name,
                'duration_minutes'  => $s->duration_minutes,
                'price'             => (float) $s->price,
            ])->values(),
            'staff'           => $appointment->staff ? [
                'id'         => $appointment->staff->id,
                'first_name' => $appointment->staff->first_name,
                'last_name'  => $appointment->staff->last_name,
                'full_name'  => trim($appointment->staff->first_name.' '.$appointment->staff->last_name),
                'role'       => $appointment->staff->role,
                'avatar'     => $appointment->staff->avatar,
            ] : null,
        ];
    }

    private function formatDetail(Appointment $appointment, string $tz): array
    {
        $summary = $this->formatSummary($appointment, $tz);
        $ends = $appointment->ends_at?->timezone($tz);

        $summary['ends_at'] = $ends?->toIso8601String();
        $summary['display']['time_range'] = $ends
            ? $summary['display']['time'].' – '.$ends->format('H:i')
            : $summary['display']['time'];
        $summary['duration_minutes'] = $appointment->duration_minutes;
        $summary['client_notes'] = $appointment->client_notes;
        $summary['source'] = $appointment->source;
        $summary['confirmed_at'] = $appointment->confirmed_at?->toIso8601String();
        $summary['cancelled_at'] = $appointment->cancelled_at?->toIso8601String();
        $summary['cancellation_reason'] = $appointment->cancellation_reason;
        $summary['has_invoice'] = (bool) ($appointment->transaction || $appointment->status === 'completed');
        $summary['review'] = $appointment->review ? [
            'id'         => $appointment->review->id,
            'rating'     => $appointment->review->rating,
            'comment'    => $appointment->review->comment,
            'created_at' => $appointment->review->created_at?->toIso8601String(),
        ] : null;
        $summary['staff'] = $appointment->staff ? array_merge($summary['staff'] ?? [], [
            'bio'   => $appointment->staff->bio,
            'phone' => $appointment->staff->phone,
            'email' => $appointment->staff->email,
        ]) : null;

        return $summary;
    }
}
