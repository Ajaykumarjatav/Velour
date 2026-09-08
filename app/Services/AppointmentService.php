<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentService as ApptService;
use App\Models\Salon;
use App\Models\Service;
use App\Models\ServicePackage;
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
     *   package_ids?: array<int>,
     *   starts_at: string,
     *   source?: string,
     *   payment_status?: string,
     *   client_notes?: ?string,
     *   internal_notes?: ?string,
     *   service_options?: array<int, array{variant?: ?string, addons?: list<string>}>
     * }  $data
     */
    public function create(int $salonId, array $data, array $options = []): Appointment
    {
        return DB::transaction(function () use ($salonId, $data, $options) {
            $this->acquireStaffBookingLocks($salonId, [(int) $data['staff_id']]);

            $snapshot = Service::summarizeForAppointment(
                $salonId,
                $data['service_ids'],
                $data['service_options'] ?? []
            );
            $snapshot = $this->applyPackagesToSnapshot(
                $salonId,
                $snapshot,
                array_values(array_map('intval', $data['package_ids'] ?? []))
            );
            if ($options['enforce_staff_services'] ?? true) {
                $this->assertStaffCanPerformServices($salonId, (int) $data['staff_id'], $data['service_ids']);
            }

            $salon    = Salon::findOrFail($salonId);
            $startsAt = SalonTime::parseAppointmentStartsAt($salon, $data['starts_at']);
            $endsAt   = $startsAt->copy()->addMinutes($snapshot['total_span_minutes']);

            // Advance window + last-minute cut-off apply to tenant/staff/admin/online (not skipped with relaxed overlap).
            if ($options['enforce_booking_rules'] ?? true) {
                \App\Support\SalonBookingRules::forSalon($salon)->assertStartsAtAllowed($startsAt);
            }

            if ($options['enforce_availability'] ?? true) {
                $this->assertWindowAllowed($salonId, (int) $data['staff_id'], $startsAt, $endsAt, null, false);
            }

            $status = $data['status'] ?? 'confirmed';
            if (! in_array($status, ['pending', 'confirmed'], true)) {
                $status = 'confirmed';
            }

            $paymentStatus = $data['payment_status'] ?? Appointment::PAYMENT_UNPAID;
            if (! in_array($paymentStatus, Appointment::paymentStatusKeys(), true)) {
                $paymentStatus = Appointment::PAYMENT_UNPAID;
            }
            $amountPaid = 0.0;
            if ($paymentStatus === Appointment::PAYMENT_PAID) {
                $amountPaid = (float) $snapshot['total_price'];
            } elseif ($paymentStatus === Appointment::PAYMENT_PARTIAL) {
                // Partial without an explicit amount is treated as unpaid until POS collects.
                $paymentStatus = Appointment::PAYMENT_UNPAID;
            }

            $appointment = Appointment::create([
                'salon_id'          => $salonId,
                'client_id'         => $data['client_id'],
                'staff_id'          => $data['staff_id'],
                'starts_at'         => $startsAt->copy()->utc(),
                'ends_at'           => $endsAt->copy()->utc(),
                'duration_minutes'  => $snapshot['total_span_minutes'],
                'total_price'       => $snapshot['total_price'],
                'amount_paid'       => $amountPaid,
                'status'            => $status,
                'source'            => $data['source'] ?? 'manual',
                'payment_status'    => $paymentStatus,
                'client_notes'      => $data['client_notes'] ?? null,
                'internal_notes'    => $data['internal_notes'] ?? null,
                'confirmed_at'      => $status === 'confirmed' ? now() : null,
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
            $nextStaffId = (int) ($data['staff_id'] ?? $appointment->staff_id);
            $this->acquireStaffBookingLocks($appointment->salon_id, [(int) $appointment->staff_id, $nextStaffId]);

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
                $this->assertStaffCanPerformServices($appointment->salon_id, (int) $staffId, $data['service_ids']);
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
            } elseif (isset($data['starts_at']) || isset($data['staff_id'])) {
                $salonForParse = Salon::findOrFail($appointment->salon_id);
                $staffId       = (int) ($data['staff_id'] ?? $appointment->staff_id);

                $startsAt = isset($data['starts_at'])
                    ? SalonTime::parseAppointmentStartsAt($salonForParse, $data['starts_at'])
                    : Carbon::parse($appointment->starts_at);
                $endsAt = $startsAt->copy()->addMinutes((int) $appointment->duration_minutes);

                $this->assertWindowAllowed($appointment->salon_id, $staffId, $startsAt, $endsAt, $appointment->id, false);

                if (isset($data['starts_at'])) {
                    $data['starts_at'] = $startsAt->copy()->utc();
                    $data['ends_at']   = $endsAt->copy()->utc();
                }
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
        return DB::transaction(function () use ($appointment, $data) {
            $salon    = Salon::findOrFail($appointment->salon_id);
            $startsAt = SalonTime::parseAppointmentStartsAt($salon, $data['starts_at']);
            $endsAt   = $startsAt->copy()->addMinutes($appointment->duration_minutes);
            $staffId  = (int) ($data['staff_id'] ?? $appointment->staff_id);

            $this->acquireStaffBookingLocks($appointment->salon_id, [(int) $appointment->staff_id, $staffId]);
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
        });
    }

    /**
     * Online booking / public API: same rules plus staff must be bookable online.
     *
     * @param  array{starts_at: string, staff_id?: int|null}  $data
     */
    public function rescheduleForOnlineBooking(Appointment $appointment, array $data): Appointment
    {
        return DB::transaction(function () use ($appointment, $data) {
            $salon    = Salon::findOrFail($appointment->salon_id);
            $startsAt = SalonTime::parseAppointmentStartsAt($salon, $data['starts_at']);
            $endsAt   = $startsAt->copy()->addMinutes($appointment->duration_minutes);
            $staffId  = (int) ($data['staff_id'] ?? $appointment->staff_id);

            $this->acquireStaffBookingLocks($appointment->salon_id, [(int) $appointment->staff_id, $staffId]);
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
        });
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

    /**
     * Prevents concurrent requests from double-booking the same staff: lock their row(s) for the
     * duration of the transaction so overlap checks and insert/update are serialized per staff.
     *
     * @param  list<int>  $staffIds
     */
    public function acquireStaffBookingLocks(int $salonId, array $staffIds): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $staffIds), fn (int $id) => $id > 0)));
        sort($ids, SORT_NUMERIC);

        foreach ($ids as $id) {
            Staff::where('salon_id', $salonId)->whereKey($id)->lockForUpdate()->firstOrFail();
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

    /** @param  array<int, mixed>  $serviceIds */
    /**
     * Previously enforced service↔staff pivot / allowed_roles.
     * Online and in-panel booking now allow any active staff for any service;
     * keep this hook so callers can still opt in with enforce_staff_services.
     */
    private function assertStaffCanPerformServices(int $salonId, int $staffId, array $serviceIds): void
    {
        $ids = array_values(array_unique(array_map('intval', $serviceIds)));
        if ($ids === []) {
            return;
        }

        Staff::withoutGlobalScopes()->where('salon_id', $salonId)->findOrFail($staffId);
        // Intentionally no service assignment / role checks.
    }

    /**
     * Stamp package provenance on component lines and apply package catalogue prices.
     *
     * @param  array{
     *   total_price: float,
     *   lines: list<array{service_id: int, service_name: string, duration_minutes: int, price: float, line_meta: array, sort_order: int}>
     * }  $snapshot
     * @param  list<int>  $packageIds
     * @return array{
     *   total_price: float,
     *   lines: list<array{service_id: int, service_name: string, duration_minutes: int, price: float, line_meta: array, sort_order: int}>
     * }
     */
    private function applyPackagesToSnapshot(int $salonId, array $snapshot, array $packageIds): array
    {
        $packageIds = array_values(array_unique(array_filter(array_map('intval', $packageIds), fn (int $id) => $id > 0)));
        if ($packageIds === []) {
            return $snapshot;
        }

        $packages = ServicePackage::withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->where('status', 'active')
            ->whereIn('id', $packageIds)
            ->with(['services' => fn ($q) => $q->orderByPivot('sort_order')])
            ->get()
            ->keyBy('id');

        $assignedServiceIds = [];

        foreach ($packageIds as $packageId) {
            $pkg = $packages->get($packageId);
            if ($pkg === null || $pkg->services->isEmpty()) {
                continue;
            }

            $componentIds = $pkg->orderedServiceIds();
            $componentSet = array_fill_keys($componentIds, true);
            $first = true;
            $packagePrice = (float) $pkg->price;

            foreach ($snapshot['lines'] as $idx => $line) {
                $serviceId = (int) ($line['service_id'] ?? 0);
                if ($serviceId <= 0 || ! isset($componentSet[$serviceId])) {
                    continue;
                }
                if (isset($assignedServiceIds[$serviceId])) {
                    continue;
                }

                $meta = is_array($line['line_meta'] ?? null) ? $line['line_meta'] : [];
                $meta['package_id'] = (int) $pkg->id;
                $meta['package_name'] = (string) $pkg->name;

                $snapshot['lines'][$idx]['line_meta'] = $meta;
                $snapshot['lines'][$idx]['price'] = $first ? round($packagePrice, 2) : 0.0;
                $assignedServiceIds[$serviceId] = true;
                $first = false;
            }
        }

        $snapshot['total_price'] = round(
            array_sum(array_map(fn (array $line) => (float) ($line['price'] ?? 0), $snapshot['lines'])),
            2
        );

        return $snapshot;
    }
}
