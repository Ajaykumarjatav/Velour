<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentService as ApptService;
use App\Models\Service;
use App\Models\StaffLeaveRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    /**
     * Create a new appointment with conflict checking.
     *
     * @param  array{
     *   client_id: int,
     *   staff_id: int,
     *   service_ids: array<int>,
     *   starts_at: string,
     *   source?: string,
     *   client_notes?: ?string,
     *   internal_notes?: ?string,
     *   service_options?: array<int, array{variant?: ?string, addons?: list<string>}>
     * }  $data
     */
    public function create(int $salonId, array $data): Appointment
    {
        return DB::transaction(function () use ($salonId, $data) {
            $snapshot = Service::summarizeForAppointment(
                $salonId,
                $data['service_ids'],
                $data['service_options'] ?? []
            );

            $startsAt = Carbon::parse($data['starts_at']);
            $endsAt    = $startsAt->copy()->addMinutes($snapshot['total_span_minutes']);

            $this->assertStaffNotOnBlockingLeave($salonId, $data['staff_id'], $startsAt, $endsAt);
            $this->assertNoConflict($data['staff_id'], $startsAt, $endsAt);

            $appointment = Appointment::create([
                'salon_id'          => $salonId,
                'client_id'         => $data['client_id'],
                'staff_id'          => $data['staff_id'],
                'starts_at'         => $startsAt,
                'ends_at'           => $endsAt,
                'duration_minutes'  => $snapshot['total_span_minutes'],
                'total_price'       => $snapshot['total_price'],
                'status'            => 'confirmed',
                'source'            => $data['source'] ?? 'manual',
                'client_notes'      => $data['client_notes'] ?? null,
                'internal_notes'    => $data['internal_notes'] ?? null,
                'confirmed_at'      => now(),
            ]);

            foreach ($snapshot['lines'] as $line) {
                ApptService::create([
                    'appointment_id'   => $appointment->id,
                    'service_id'       => $line['service_id'],
                    'service_name'     => $line['service_name'],
                    'duration_minutes' => $line['duration_minutes'],
                    'price'            => $line['price'],
                    'sort_order'       => $line['sort_order'],
                    'line_meta'        => $line['line_meta'],
                ]);
            }

            return $appointment;
        });
    }

    /**
     * Update appointment details.
     */
    public function update(Appointment $appointment, array $data): Appointment
    {
        return DB::transaction(function () use ($appointment, $data) {
            if (isset($data['service_ids'])) {
                $snapshot = Service::summarizeForAppointment(
                    $appointment->salon_id,
                    $data['service_ids'],
                    $data['service_options'] ?? []
                );

                $startsAt = isset($data['starts_at']) ? Carbon::parse($data['starts_at']) : Carbon::parse($appointment->starts_at);
                $endsAt   = $startsAt->copy()->addMinutes($snapshot['total_span_minutes']);

                $staffId = $data['staff_id'] ?? $appointment->staff_id;
                $this->assertStaffNotOnBlockingLeave($appointment->salon_id, $staffId, $startsAt, $endsAt);
                $this->assertNoConflict($staffId, $startsAt, $endsAt, $appointment->id);

                $appointment->services()->delete();
                foreach ($snapshot['lines'] as $line) {
                    ApptService::create([
                        'appointment_id'   => $appointment->id,
                        'service_id'       => $line['service_id'],
                        'service_name'     => $line['service_name'],
                        'duration_minutes' => $line['duration_minutes'],
                        'price'            => $line['price'],
                        'sort_order'       => $line['sort_order'],
                        'line_meta'        => $line['line_meta'],
                    ]);
                }

                $data['duration_minutes'] = $snapshot['total_span_minutes'];
                $data['ends_at']          = $endsAt;
                $data['total_price']      = $snapshot['total_price'];
                $data['starts_at']        = $startsAt;
            }

            $appointment->update($data);

            return $appointment->fresh();
        });
    }

    /**
     * Reschedule to a new time, optionally with a different staff member.
     */
    public function reschedule(Appointment $appointment, array $data): Appointment
    {
        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt   = $startsAt->copy()->addMinutes($appointment->duration_minutes);
        $staffId  = $data['staff_id'] ?? $appointment->staff_id;

        $this->assertStaffNotOnBlockingLeave($appointment->salon_id, $staffId, $startsAt, $endsAt);
        $this->assertNoConflict($staffId, $startsAt, $endsAt, $appointment->id);

        $appointment->update([
            'staff_id'               => $staffId,
            'starts_at'              => $startsAt,
            'ends_at'                => $endsAt,
            'status'                 => 'confirmed',
            'reminder_sent'          => false,
            'reminder_sent_at'       => null,
            'reminder_dispatch_keys' => null,
        ]);

        return $appointment->fresh();
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function assertStaffNotOnBlockingLeave(int $salonId, int $staffId, Carbon $startsAt, Carbon $endsAt): void
    {
        $cursor = $startsAt->copy()->startOfDay();
        $endDay = $endsAt->copy()->startOfDay();
        if ($endDay->lt($cursor)) {
            $endDay = $cursor;
        }

        while ($cursor->lte($endDay)) {
            if (StaffLeaveRequest::approvedBlockingLeaveExists($salonId, $staffId, $cursor->toDateString())) {
                throw new \InvalidArgumentException('This staff member is on approved leave on that date. Choose another day or staff member.');
            }
            $cursor->addDay();
        }
    }

    /**
     * Check whether a staff member is free for the given window.
     */
    public function assertNoConflict(
        int    $staffId,
        Carbon $startsAt,
        Carbon $endsAt,
        ?int   $excludeId = null
    ): void {
        $query = Appointment::where('staff_id', $staffId)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where(fn ($q) => $q
                ->whereBetween('starts_at', [$startsAt, $endsAt->copy()->subSecond()])
                ->orWhereBetween('ends_at', [$startsAt->copy()->addSecond(), $endsAt])
                ->orWhere(fn ($sq) => $sq->where('starts_at', '<=', $startsAt)->where('ends_at', '>=', $endsAt)));

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new \InvalidArgumentException('The selected time slot is no longer available.');
        }
    }

    /**
     * Return true if the staff member is free at the given window.
     */
    public function isAvailable(int $salonId, int $staffId, Carbon $startsAt, Carbon $endsAt, ?int $excludeId = null): bool
    {
        try {
            $this->assertStaffNotOnBlockingLeave($salonId, $staffId, $startsAt, $endsAt);
            $this->assertNoConflict($staffId, $startsAt, $endsAt, $excludeId);

            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
}
