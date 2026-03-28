<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Staff;
use App\Services\AppointmentService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function __construct(
        private AppointmentService  $appointmentService,
        private NotificationService $notificationService,
    ) {}

    /* ── GET /appointments ──────────────────────────────────────────────── */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date'     => 'nullable|date',
            'from'     => 'nullable|date',
            'to'       => 'nullable|date',
            'staff_id' => 'nullable|integer',
            'status'   => 'nullable|string',
            'search'   => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:200',
        ]);

        $salonId = $request->attributes->get('salon_id');

        $q = Appointment::with(['client', 'staff', 'services'])
            ->where('salon_id', $salonId);

        if ($request->date) {
            $q->whereDate('starts_at', $request->date);
        } elseif ($request->from && $request->to) {
            $q->whereBetween('starts_at', [$request->from, $request->to . ' 23:59:59']);
        }

        if ($request->staff_id) {
            $q->where('staff_id', $request->staff_id);
        }

        if ($request->status) {
            $q->where('status', $request->status);
        }

        if ($request->search) {
            $term = $request->search;
            $q->whereHas('client', function ($cq) use ($term) {
                $cq->whereRaw("(first_name || ' ' || last_name) ILIKE ?", ["%{$term}%"])
                   ->orWhere('email', 'ilike', "%{$term}%")
                   ->orWhere('phone', 'ilike', "%{$term}%");
            });
        }

        $appointments = $q->orderBy('starts_at')
                          ->paginate($request->per_page ?? 50);

        return response()->json($appointments);
    }

    /* ── POST /appointments ─────────────────────────────────────────────── */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'      => 'required|integer',
            'staff_id'       => 'required|integer',
            'service_ids'    => 'required|array|min:1',
            'service_ids.*'  => 'integer',
            'starts_at'      => 'required|date|after:now',
            'source'         => 'nullable|string',
            'client_notes'   => 'nullable|string|max:1000',
            'internal_notes' => 'nullable|string|max:1000',
            'send_confirmation' => 'nullable|boolean',
        ]);

        $salonId = $request->attributes->get('salon_id');

        try {
            $appointment = $this->appointmentService->create($salonId, $data);

            if ($data['send_confirmation'] ?? true) {
                $this->notificationService->appointmentConfirmation($appointment);
            }

            return response()->json([
                'message'     => 'Appointment booked.',
                'appointment' => $appointment->load(['client', 'staff', 'services']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /* ── GET /appointments/{id} ─────────────────────────────────────────── */
    public function show(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::with(['client', 'staff', 'services', 'transaction'])
            ->where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);

        return response()->json($appointment);
    }

    /* ── PUT /appointments/{id} ─────────────────────────────────────────── */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'staff_id'       => 'sometimes|integer',
            'service_ids'    => 'sometimes|array',
            'service_ids.*'  => 'integer',
            'starts_at'      => 'sometimes|date',
            'client_notes'   => 'nullable|string|max:1000',
            'internal_notes' => 'nullable|string|max:1000',
        ]);

        $appointment = Appointment::where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);

        $appointment = $this->appointmentService->update($appointment, $data);

        return response()->json([
            'message'     => 'Appointment updated.',
            'appointment' => $appointment->load(['client', 'staff', 'services']),
        ]);
    }

    /* ── DELETE /appointments/{id} ──────────────────────────────────────── */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);

        $appointment->delete();
        return response()->json(['message' => 'Appointment deleted.']);
    }

    /* ── PUT /appointments/{id}/status ─────────────────────────────────── */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,in_progress,completed,cancelled,no_show',
        ]);

        $appointment = Appointment::where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);

        $appointment->update(['status' => $data['status']]);
        return response()->json(['message' => 'Status updated.', 'status' => $data['status']]);
    }

    /* ── PUT /appointments/{id}/checkin ─────────────────────────────────── */
    public function checkIn(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);

        $appointment->update(['status' => 'checked_in']);
        $this->notificationService->staffAlert($appointment->salon_id, "Client {$appointment->client->first_name} checked in", 'checkin');

        return response()->json(['message' => 'Client checked in.']);
    }

    /* ── PUT /appointments/{id}/complete ────────────────────────────────── */
    public function complete(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);

        $appointment->update(['status' => 'completed']);

        // Update client stats
        $appointment->client->increment('visit_count');
        $appointment->client->update(['last_visit_at' => now()]);

        return response()->json(['message' => 'Appointment completed.']);
    }

    /* ── PUT /appointments/{id}/cancel ─────────────────────────────────── */
    public function cancelAppointment(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $appointment = Appointment::where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);

        $appointment->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_reason' => $data['reason'] ?? null,
        ]);

        $this->notificationService->appointmentCancellation($appointment);

        return response()->json(['message' => 'Appointment cancelled.']);
    }

    /* ── PUT /appointments/{id}/noshow ─────────────────────────────────── */
    public function markNoShow(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);

        $appointment->update(['status' => 'no_show']);
        return response()->json(['message' => 'Marked as no-show.']);
    }

    /* ── POST /appointments/{id}/reschedule ─────────────────────────────── */
    public function reschedule(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'starts_at' => 'required|date|after:now',
            'staff_id'  => 'nullable|integer',
        ]);

        $appointment = Appointment::where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);

        $appointment = $this->appointmentService->reschedule($appointment, $data);
        $this->notificationService->appointmentRescheduled($appointment);

        return response()->json([
            'message'     => 'Appointment rescheduled.',
            'appointment' => $appointment->load(['client', 'staff', 'services']),
        ]);
    }

    /* ── POST /appointments/{id}/reminder ───────────────────────────────── */
    public function sendReminder(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);

        $this->notificationService->appointmentReminder($appointment);
        $appointment->update(['reminder_sent' => true]);

        return response()->json(['message' => 'Reminder sent.']);
    }

    /* ── GET /appointments/export ───────────────────────────────────────── */
    public function export(Request $request): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');

        $appointments = Appointment::with(['client', 'staff', 'services'])
            ->where('salon_id', $salonId)
            ->orderBy('starts_at', 'desc')
            ->get();

        return response()->json([
            'count' => $appointments->count(),
            'data'  => $appointments,
        ]);
    }
}
