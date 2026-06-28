<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentService as ApptService;
use App\Models\Client;
use App\Models\PosTransaction;
use App\Models\Salon;
use App\Models\Service;
use Illuminate\Support\Carbon;

/**
 * Creates completed appointment record(s) when a walk-in POS sale includes services.
 */
class PosWalkInAppointmentService
{
    /** Internal placeholder client for anonymous walk-in POS sales. */
    public const WALK_IN_PHONE = '__pos_walk_in__';

    public static function resolveClientId(int $salonId, ?int $clientId): int
    {
        if ($clientId) {
            return $clientId;
        }

        return (int) Client::withoutGlobalScopes()->firstOrCreate(
            [
                'salon_id' => $salonId,
                'phone'    => self::WALK_IN_PHONE,
            ],
            [
                'first_name' => 'Walk-in',
                'last_name'  => 'Guest',
                'source'     => 'walk_in',
                'status'     => 'active',
            ]
        )->id;
    }

    /**
     * @param  list<array{type: string, id: int, name: string, qty: int, price: float, staff_id?: int}>  $resolvedItems
     * @return list<Appointment>
     */
    public static function createAppointmentsForWalkInSale(
        Salon $salon,
        PosTransaction $transaction,
        array $resolvedItems,
        ?int $clientId,
    ): array {
        $serviceItems = array_values(array_filter(
            $resolvedItems,
            fn (array $i) => ($i['type'] ?? '') === 'service'
        ));

        if ($serviceItems === []) {
            return [];
        }

        $resolvedClientId = self::resolveClientId($salon->id, $clientId ?: $transaction->client_id);

        if (! $transaction->client_id) {
            $transaction->update(['client_id' => $resolvedClientId]);
        }

        $grouped = collect($serviceItems)->groupBy(
            fn (array $i) => (int) ($i['staff_id'] ?? 0)
        );

        $appointments = [];

        foreach ($grouped as $staffId => $items) {
            if ((int) $staffId <= 0) {
                continue;
            }

            $appointment = self::createSingleAppointment(
                $salon,
                $transaction,
                $items->values()->all(),
                (int) $staffId,
                $resolvedClientId,
            );

            if ($appointment !== null) {
                $appointments[] = $appointment;
            }
        }

        if ($appointments !== [] && ! $transaction->appointment_id) {
            $transaction->update(['appointment_id' => $appointments[0]->id]);
        }

        $client = Client::withoutGlobalScopes()->find($resolvedClientId);
        if ($client && $client->phone !== self::WALK_IN_PHONE) {
            $completedAt = Carbon::parse($transaction->completed_at ?? now());
            $client->increment('visit_count');
            $client->update(['last_visit_at' => $completedAt]);
        }

        return $appointments;
    }

    /**
     * @param  list<array{type: string, id: int, name: string, qty: int, price: float}>  $serviceItems
     */
    private static function createSingleAppointment(
        Salon $salon,
        PosTransaction $transaction,
        array $serviceItems,
        int $staffId,
        int $clientId,
    ): ?Appointment {
        $services = Service::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->whereIn('id', collect($serviceItems)->pluck('id')->unique()->all())
            ->get()
            ->keyBy('id');

        $totalDuration = 0;
        $totalBuffer   = 0;
        $totalPrice    = 0.0;
        $lines         = [];
        $sort          = 0;

        foreach ($serviceItems as $item) {
            $svc = $services->get($item['id']);
            if ($svc === null) {
                continue;
            }

            $qty       = max(1, (int) $item['qty']);
            $unitPrice = (float) $item['price'];

            for ($q = 0; $q < $qty; $q++) {
                $dur = max(1, (int) ($svc->duration_minutes ?? 30));
                $buf = max(0, (int) ($svc->buffer_minutes ?? 0));
                $totalDuration += $dur;
                $totalBuffer   += $buf;
                $totalPrice    += $unitPrice;

                $lines[] = [
                    'service_id'       => (int) $svc->id,
                    'service_name'     => (string) $item['name'],
                    'duration_minutes' => $dur,
                    'price'            => round($unitPrice, 2),
                    'sort_order'       => $sort,
                    'line_meta'        => null,
                ];
                $sort++;
            }
        }

        if ($lines === []) {
            return null;
        }

        $spanMinutes = max(1, $totalDuration + $totalBuffer);
        $completedAt = Carbon::parse($transaction->completed_at ?? now());
        $endsAt      = $completedAt->copy();
        $startsAt    = $endsAt->copy()->subMinutes($spanMinutes);

        $appointment = Appointment::create([
            'salon_id'         => $salon->id,
            'client_id'        => $clientId,
            'staff_id'         => $staffId,
            'starts_at'        => $startsAt->utc(),
            'ends_at'          => $endsAt->utc(),
            'duration_minutes' => $spanMinutes,
            'total_price'      => round($totalPrice, 2),
            'amount_paid'      => round($totalPrice, 2),
            'payment_status'   => Appointment::PAYMENT_PAID,
            'status'           => 'completed',
            'source'           => 'walk_in',
            'confirmed_at'     => $completedAt,
            'internal_notes'   => 'Created from POS sale '.$transaction->reference,
        ]);

        foreach ($lines as $line) {
            ApptService::create([
                'appointment_id' => $appointment->id,
                ...$line,
            ]);
        }

        return $appointment;
    }

    /**
     * @deprecated Use createAppointmentsForWalkInSale()
     *
     * @param  list<array{type: string, id: int, name: string, qty: int, price: float}>  $resolvedItems
     */
    public static function createFromSale(
        Salon $salon,
        PosTransaction $transaction,
        array $resolvedItems,
        int $staffId,
        ?int $clientId,
    ): ?Appointment {
        $items = array_map(
            fn (array $i) => [...$i, 'staff_id' => $staffId],
            array_values(array_filter($resolvedItems, fn (array $i) => ($i['type'] ?? '') === 'service'))
        );

        $appointments = self::createAppointmentsForWalkInSale($salon, $transaction, $items, $clientId);

        return $appointments[0] ?? null;
    }
}
