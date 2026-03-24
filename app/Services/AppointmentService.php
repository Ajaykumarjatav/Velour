<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentService as ApptService;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppointmentService
{
    /**
     * Create a new appointment with conflict checking.
     */
    public function create(int $salonId, array $data): Appointment
    {
        return DB::transaction(function () use ($salonId, $data) {
            $services = Service::whereIn('id', $data['service_ids'])
                ->where('salon_id', $salonId)
                ->get();

            if ($services->count() !== count($data['service_ids'])) {
                throw new \InvalidArgumentException('One or more services not found.');
            }

            $totalDuration = $services->sum('duration_minutes')
                + $services->sum('buffer_minutes');

            $startsAt = Carbon::parse($data['starts_at']);
            $endsAt   = $startsAt->copy()->addMinutes($totalDuration);
            $totalPrice = $services->sum('price');

            // Check for conflicts
            $this->assertNoConflict($data['staff_id'], $startsAt, $endsAt);

            $appointment = Appointment::create([
                'salon_id'       => $salonId,
                'client_id'      => $data['client_id'],
                'staff_id'       => $data['staff_id'],
                'reference'      => 'APT-' . strtoupper(Str::random(8)),
                'starts_at'      => $startsAt,
                'ends_at'        => $endsAt,
                'duration_minutes'=> $totalDuration,
                'total_price'    => $totalPrice,
                'status'         => 'confirmed',
                'source'         => $data['source'] ?? 'manual',
                'client_notes'   => $data['client_notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'confirmed_at'   => now(),
            ]);

            foreach ($services as $i => $service) {
                ApptService::create([
                    'appointment_id' => $appointment->id,
                    'service_id'     => $service->id,
                    'service_name'   => $service->name,
                    'duration_minutes'=> $service->duration_minutes,
                    'price'          => $service->price,
                    'sort_order'     => $i,
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
                $services = Service::whereIn('id', $data['service_ids'])
                    ->where('salon_id', $appointment->salon_id)
                    ->get();

                $totalDuration = $services->sum('duration_minutes') + $services->sum('buffer_minutes');
                $startsAt = isset($data['starts_at']) ? Carbon::parse($data['starts_at']) : Carbon::parse($appointment->starts_at);
                $endsAt   = $startsAt->copy()->addMinutes($totalDuration);

                $staffId = $data['staff_id'] ?? $appointment->staff_id;
                $this->assertNoConflict($staffId, $startsAt, $endsAt, $appointment->id);

                $appointment->services()->delete();
                foreach ($services as $i => $service) {
                    ApptService::create([
                        'appointment_id' => $appointment->id,
                        'service_id'     => $service->id,
                        'service_name'   => $service->name,
                        'duration_minutes'=> $service->duration_minutes,
                        'price'          => $service->price,
                        'sort_order'     => $i,
                    ]);
                }

                $data['duration_minutes'] = $totalDuration;
                $data['ends_at']          = $endsAt;
                $data['total_price']      = $services->sum('price');
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

        $this->assertNoConflict($staffId, $startsAt, $endsAt, $appointment->id);

        $appointment->update([
            'staff_id'  => $staffId,
            'starts_at' => $startsAt,
            'ends_at'   => $endsAt,
            'status'    => 'confirmed',
        ]);

        return $appointment->fresh();
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
            ->where(fn($q) => $q
                ->whereBetween('starts_at', [$startsAt, $endsAt->copy()->subSecond()])
                ->orWhereBetween('ends_at',  [$startsAt->copy()->addSecond(), $endsAt])
                ->orWhere(fn($sq) => $sq->where('starts_at', '<=', $startsAt)->where('ends_at', '>=', $endsAt))
            );

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
    public function isAvailable(int $staffId, Carbon $startsAt, Carbon $endsAt, ?int $excludeId = null): bool
    {
        try {
            $this->assertNoConflict($staffId, $startsAt, $endsAt, $excludeId);
            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
}
