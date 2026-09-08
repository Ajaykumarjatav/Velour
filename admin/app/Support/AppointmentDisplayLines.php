<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\ServicePackage;
use Illuminate\Support\Collection;

/**
 * Service lines for appointment UI: booked lines + extras added at POS checkout.
 * Package POS sales are shown as the package (not expanded component services).
 */
final class AppointmentDisplayLines
{
    /**
     * @param  Collection<int, ServicePackage>|null  $packages  Preloaded salon packages to avoid N+1 on boards.
     * @return Collection<int, array{
     *     name: string,
     *     duration: int|null,
     *     price: float,
     *     source: 'booked'|'pos'|'package',
     *     line_meta?: array<string, mixed>|null
     * }>
     */
    public static function serviceLines(Appointment $appointment, ?Collection $packages = null): Collection
    {
        $appointment->loadMissing(['services', 'transaction.items']);

        $bookedRows = [];
        $bookedServiceIds = [];

        foreach ($appointment->services as $svc) {
            if ($svc->service_id) {
                $bookedServiceIds[] = (int) $svc->service_id;
            }
            $meta = is_array($svc->line_meta) ? $svc->line_meta : null;
            $bookedRows[] = [
                'name'      => (string) $svc->service_name,
                'duration'  => (int) $svc->duration_minutes,
                'price'     => (float) $svc->price,
                'source'    => 'booked',
                'line_meta' => $meta,
                'package_id' => isset($meta['package_id']) ? (int) $meta['package_id'] : null,
                'package_name' => isset($meta['package_name']) ? (string) $meta['package_name'] : null,
            ];
        }

        $lines = self::collapsePackageGroups($bookedRows);

        // Existing package sales without line_meta: prefer POS package item names.
        if (! $lines->contains(fn (array $l) => ($l['source'] ?? '') === 'package')) {
            $fallback = self::packageLinesFromTransaction($appointment, $bookedRows);
            if ($fallback->isNotEmpty()) {
                $lines = $fallback;
            }
        }

        // Online package bookings (no POS txn / no line_meta yet): infer exact package match.
        if (! $lines->contains(fn (array $l) => ($l['source'] ?? '') === 'package')) {
            $inferred = self::inferPackageFromBookedServices($appointment, $bookedRows, $packages);
            if ($inferred->isNotEmpty()) {
                $lines = $inferred;
            }
        }

        $transaction = $appointment->transaction;
        if (! $transaction) {
            return $lines->values();
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

        return $lines->values();
    }

    /**
     * @param  list<array{name: string, duration: int, price: float, source: string, line_meta: ?array, package_id: ?int, package_name: ?string}>  $bookedRows
     * @return Collection<int, array<string, mixed>>
     */
    private static function collapsePackageGroups(array $bookedRows): Collection
    {
        $lines = collect();
        /** @var array<int, array{name: string, duration: int, price: float, components: list<array{name: string, duration: int|null}>, package_id: int}> $openGroups */
        $openGroups = [];

        $flushGroup = function (int $pkgId) use (&$openGroups, $lines): void {
            if (! isset($openGroups[$pkgId])) {
                return;
            }
            $g = $openGroups[$pkgId];
            unset($openGroups[$pkgId]);
            $lines->push([
                'name'      => $g['name'],
                'duration'  => $g['duration'] > 0 ? $g['duration'] : null,
                'price'     => round($g['price'], 2),
                'source'    => 'package',
                'line_meta' => [
                    'is_package' => true,
                    'package_id' => $g['package_id'],
                    'package_name' => $g['name'],
                    'components' => $g['components'],
                ],
            ]);
        };

        foreach ($bookedRows as $row) {
            $pkgId = $row['package_id'];
            if ($pkgId === null || $pkgId <= 0) {
                foreach (array_keys($openGroups) as $openId) {
                    $flushGroup((int) $openId);
                }
                $lines->push([
                    'name'      => $row['name'],
                    'duration'  => $row['duration'] > 0 ? $row['duration'] : null,
                    'price'     => $row['price'],
                    'source'    => 'booked',
                    'line_meta' => $row['line_meta'],
                ]);
                continue;
            }

            if (! isset($openGroups[$pkgId])) {
                foreach (array_keys($openGroups) as $openId) {
                    if ((int) $openId !== $pkgId) {
                        $flushGroup((int) $openId);
                    }
                }
                $openGroups[$pkgId] = [
                    'name' => $row['package_name'] ?: 'Package',
                    'duration' => 0,
                    'price' => 0.0,
                    'components' => [],
                    'package_id' => $pkgId,
                ];
            }

            $openGroups[$pkgId]['duration'] += max(0, (int) $row['duration']);
            $openGroups[$pkgId]['price'] += (float) $row['price'];
            if ($row['package_name']) {
                $openGroups[$pkgId]['name'] = $row['package_name'];
            }
            $openGroups[$pkgId]['components'][] = [
                'name' => $row['name'],
                'duration' => $row['duration'] > 0 ? $row['duration'] : null,
            ];
        }

        foreach (array_keys($openGroups) as $openId) {
            $flushGroup((int) $openId);
        }

        return $lines;
    }

    /**
     * @param  list<array{name: string, duration: int, price: float}>  $bookedRows
     * @return Collection<int, array<string, mixed>>
     */
    private static function packageLinesFromTransaction(Appointment $appointment, array $bookedRows): Collection
    {
        $transaction = $appointment->transaction;
        if (! $transaction) {
            return collect();
        }

        $packageItems = $transaction->items->where('type', 'package')->values();
        if ($packageItems->isEmpty()) {
            return collect();
        }

        // Only collapse when the sale has package lines and no separate POS service lines
        // (components already live on the appointment). Mixed package+extra-service keeps expansion.
        if ($transaction->items->where('type', 'service')->isNotEmpty()) {
            return collect();
        }

        $components = array_map(fn (array $row) => [
            'name' => $row['name'],
            'duration' => $row['duration'] > 0 ? $row['duration'] : null,
        ], $bookedRows);

        $totalDuration = array_sum(array_map(fn (array $c) => (int) ($c['duration'] ?? 0), $components));

        // One package on the sale: attach all booked components under it.
        if ($packageItems->count() === 1) {
            $item = $packageItems->first();

            return collect([[
                'name'      => (string) $item->name,
                'duration'  => $totalDuration > 0 ? $totalDuration : null,
                'price'     => (float) $item->total,
                'source'    => 'package',
                'line_meta' => [
                    'is_package' => true,
                    'package_id' => (int) $item->itemable_id,
                    'package_name' => (string) $item->name,
                    'components' => $components,
                ],
            ]]);
        }

        // Multiple packages without line_meta: show package names/prices only (no safe component split).
        return $packageItems->map(fn ($item) => [
            'name'      => (string) $item->name,
            'duration'  => null,
            'price'     => (float) $item->total,
            'source'    => 'package',
            'line_meta' => [
                'is_package' => true,
                'package_id' => (int) $item->itemable_id,
                'package_name' => (string) $item->name,
                'components' => [],
            ],
        ]);
    }

    /**
     * When booked service IDs exactly match an active package, show that package name.
     *
     * @param  list<array{name: string, duration: int, price: float}>  $bookedRows
     * @param  Collection<int, ServicePackage>|null  $packages
     * @return Collection<int, array<string, mixed>>
     */
    private static function inferPackageFromBookedServices(Appointment $appointment, array $bookedRows, ?Collection $packages = null): Collection
    {
        $bookedIds = $appointment->services
            ->pluck('service_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (count($bookedIds) < 2) {
            return collect();
        }

        $sortedBooked = $bookedIds;
        sort($sortedBooked, SORT_NUMERIC);

        if ($packages === null) {
            $packages = ServicePackage::withoutGlobalScopes()
                ->where('salon_id', $appointment->salon_id)
                ->where('status', 'active')
                ->with(['services' => fn ($q) => $q->withoutGlobalScopes()->orderByPivot('sort_order')])
                ->get();
        }

        foreach ($packages as $pkg) {
            $componentIds = $pkg->relationLoaded('services')
                ? $pkg->services->pluck('id')->map(fn ($id) => (int) $id)->all()
                : $pkg->orderedServiceIds();
            if (count($componentIds) !== count($sortedBooked)) {
                continue;
            }
            $sortedComponents = $componentIds;
            sort($sortedComponents, SORT_NUMERIC);
            if ($sortedComponents !== $sortedBooked) {
                continue;
            }

            $components = array_map(fn (array $row) => [
                'name' => $row['name'],
                'duration' => $row['duration'] > 0 ? $row['duration'] : null,
            ], $bookedRows);
            $totalDuration = array_sum(array_map(fn (array $c) => (int) ($c['duration'] ?? 0), $components));

            return collect([[
                'name'      => (string) $pkg->name,
                'duration'  => $totalDuration > 0 ? $totalDuration : null,
                'price'     => (float) $pkg->price,
                'source'    => 'package',
                'line_meta' => [
                    'is_package' => true,
                    'package_id' => (int) $pkg->id,
                    'package_name' => (string) $pkg->name,
                    'components' => $components,
                ],
            ]]);
        }

        return collect();
    }
}
