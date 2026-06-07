<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Support\Collection;

/**
 * Service lines for appointment UI: booked lines + extras added at POS checkout.
 */
final class AppointmentDisplayLines
{
    /**
     * @return Collection<int, array{
     *     name: string,
     *     duration: int|null,
     *     price: float,
     *     source: 'booked'|'pos',
     *     line_meta?: array<string, mixed>|null
     * }>
     */
    public static function serviceLines(Appointment $appointment): Collection
    {
        $appointment->loadMissing(['services', 'transaction.items']);

        $lines = collect();
        $bookedServiceIds = [];

        foreach ($appointment->services as $svc) {
            if ($svc->service_id) {
                $bookedServiceIds[] = (int) $svc->service_id;
            }
            $lines->push([
                'name'      => (string) $svc->service_name,
                'duration'  => (int) $svc->duration_minutes,
                'price'     => (float) $svc->price,
                'source'    => 'booked',
                'line_meta' => $svc->line_meta,
            ]);
        }

        $transaction = $appointment->transaction;
        if (! $transaction) {
            return $lines;
        }

        foreach ($transaction->items->where('type', 'service') as $item) {
            $serviceId = $item->itemable_type === Service::class && $item->itemable_id
                ? (int) $item->itemable_id
                : null;

            if ($serviceId !== null && in_array($serviceId, $bookedServiceIds, true)) {
                continue;
            }

            $duration = null;
            if ($serviceId !== null) {
                $duration = (int) (Service::withoutGlobalScopes()
                    ->whereKey($serviceId)
                    ->value('duration_minutes') ?? 0);
            }

            $lines->push([
                'name'      => (string) $item->name,
                'duration'  => $duration > 0 ? $duration : null,
                'price'     => (float) $item->total,
                'source'    => 'pos',
                'line_meta' => null,
            ]);
        }

        return $lines;
    }
}
