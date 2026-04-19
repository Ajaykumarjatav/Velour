<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentService as ApptService;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Services\Scheduling\AvailabilityRejectedException;
use App\Support\SalonTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    public function __construct(
        private AvailabilityService $availability,
    ) {}

    /**
     * Create a new appointment with unified availability checking.
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

            $salon    = Salon::findOrFail($salonId);
            $startsAt = SalonTime::parseAppointmentStartsAt($salon, $data['starts_at']);
            $endsAt   = $startsAt->copy()->addMinutes($snapshot['total_span_minutes']);

            $this->assertWindowAllowed($salonId, (int) $data['staff_id'], $startsAt, $endsAt, null, false);

            $appointment = Appointment::create([
                'salon_id'          => $salonId,
                'client_id'         => $data['client_id'],
                'staff_id'          => $data['staff_id'],
                'starts_at'         => $startsAt->copy()->utc(),
                'ends_at'           => $endsAt->copy()->utc(),
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

                $salonForParse = Salon::findOrFail($appointment->salon_id);
                $startsAt = isset($data['starts_at'])
                    ? SalonTime::parseAppointmentStartsAt($salonForParse, $data['starts_at'])
                    : Carbon::parse($appointment->starts_at);
                $endsAt   = $startsAt->copy()->addMinutes($snapshot['total_span_minutes']);

                $staffId = $data['staff_id'] ?? $appointment->staff_id;
                $this->assertWindowAllowed($appointment->salon_id, (int) $staffId, $startsAt, $endsAt, $appointment->id, false);

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
                $data['ends_at']          = $endsAt->copy()->utc();
                $data['total_price']      = $snapshot['total_price'];
                $data['starts_at']        = $startsAt->copy()->utc();
            }

            $appointment->update($data);

            return $appointment->fresh();
        });
    }

    /**
     * Reschedule to a new time, optionally with a different staff member.
     *
     * @param  array{starts_at: string, staff_id?: int|null}  $data
     */
    public function reschedule(Appointment $appointment, array $data): Appointment
    {
        $salon    = Salon::findOrFail($appointment->salon_id);
        $startsAt = SalonTime::parseAppointmentStartsAt($salon, $data['starts_at']);
        $endsAt   = $startsAt->copy()->addMinutes($appointment->duration_minutes);
        $staffId  = (int) ($data['staff_id'] ?? $appointment->staff_id);

        $this->assertWindowAllowed($appointment->salon_id, $staffId, $startsAt, $endsAt, $appointment->id, false);

        $appointment->update([
            'staff_id'               => $staffId,
            'starts_at'              => $startsAt->copy()->utc(),
            'ends_at'                => $endsAt->copy()->utc(),
            'status'                 => 'confirmed',
            'reminder_sent'          => false,
            'reminder_sent_at'       => null,
            'reminder_dispatch_keys' => null,
        ]);

        return $appointment->fresh();
    }

    /**
     * Online booking / public API: same rules plus staff must be bookable online.
     *
     * @param  array{starts_at: string, staff_id?: int|null}  $data
     */
    public function rescheduleForOnlineBooking(Appointment $appointment, array $data): Appointment
    {
        $salon    = Salon::findOrFail($appointment->salon_id);
        $startsAt = SalonTime::parseAppointmentStartsAt($salon, $data['starts_at']);
        $endsAt   = $startsAt->copy()->addMinutes($appointment->duration_minutes);
        $staffId  = (int) ($data['staff_id'] ?? $appointment->staff_id);

        $this->assertWindowAllowed($appointment->salon_id, $staffId, $startsAt, $endsAt, $appointment->id, true);

        $appointment->update([
            'staff_id'               => $staffId,
            'starts_at'              => $startsAt->copy()->utc(),
            'ends_at'                => $endsAt->copy()->utc(),
            'status'                   => 'confirmed',
            'reminder_sent'            => false,
            'reminder_sent_at'         => null,
            'reminder_dispatch_keys'   => null,
        ]);

        return $appointment->fresh();
    }

    /**
     * Return true if the staff member is free for the given window (all checks).
     */
    public function isAvailable(int $salonId, int $staffId, Carbon $startsAt, Carbon $endsAt, ?int $excludeId = null, bool $requireBookableOnline = false): bool
    {
        try {
            $this->assertWindowAllowed($salonId, $staffId, $startsAt, $endsAt, $excludeId, $requireBookableOnline);

            return true;
        } catch (AvailabilityRejectedException) {
            return false;
        }
    }

    /**
     * @deprecated Prefer AvailabilityService::validateProposedWindow
     */
    public function assertStaffNotOnBlockingLeave(int $salonId, int $staffId, Carbon $startsAt, Carbon $endsAt): void
    {
        try {
            $this->assertWindowAllowed($salonId, $staffId, $startsAt, $endsAt, null, false);
        } catch (AvailabilityRejectedException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * @deprecated Prefer AvailabilityService::validateProposedWindow
     */
    public function assertNoConflict(
        int    $staffId,
        Carbon $startsAt,
        Carbon $endsAt,
        ?int   $excludeId = null
    ): void {
        $staff = Staff::findOrFail($staffId);
        try {
            $this->assertWindowAllowed($staff->salon_id, $staffId, $startsAt, $endsAt, $excludeId, false);
        } catch (AvailabilityRejectedException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    private function assertWindowAllowed(
        int $salonId,
        int $staffId,
        Carbon $startsAt,
        Carbon $endsAt,
        ?int $excludeAppointmentId,
        bool $requireBookableOnline,
    ): void {
        $salon = Salon::findOrFail($salonId);
        $staff = Staff::where('salon_id', $salonId)->findOrFail($staffId);

        $result = $this->availability->validateProposedWindow(
            $salon,
            $staff,
            $startsAt,
            $endsAt,
            $excludeAppointmentId,
            $requireBookableOnline,
        );

        if (! $result->ok) {
            throw new AvailabilityRejectedException($result);
        }
    }
}
