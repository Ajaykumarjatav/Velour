<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\PosTransaction;
use Illuminate\Support\Collection;

/**
 * Unified client timeline: appointments and POS sales (full amounts, not spend deltas).
 */
final class ClientHistory
{
    /**
     * @return Collection<int, array{
     *   kind: string,
     *   at: \Carbon\Carbon|null,
     *   label: string,
     *   detail: string,
     *   amount: float,
     *   status: string,
     *   url: string|null
     * }>
     */
    public static function forClient(Client $client, int $limit = 15): Collection
    {
        $currency = $client->salon?->currency ?? 'GBP';

        $appointments = Appointment::withoutGlobalScopes()
            ->where('client_id', $client->id)
            ->with(['staff:id,first_name,last_name', 'services:id,appointment_id,service_id,service_name'])
            ->orderByDesc('starts_at')
            ->limit($limit)
            ->get();

        $sales = PosTransaction::withoutGlobalScopes()
            ->where('client_id', $client->id)
            ->where('status', 'completed')
            ->with(['items', 'staff:id,first_name,last_name'])
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $rows = collect();

        $saleAppointmentIds = $sales->pluck('appointment_id')->filter()->map(fn ($id) => (int) $id)->flip();

        foreach ($appointments as $apt) {
            if ($saleAppointmentIds->has((int) $apt->id)) {
                continue;
            }

            $paid = (float) ($apt->amount_paid ?? 0);
            $quoted = (float) ($apt->total_price ?? 0);
            $amount = ($apt->payment_status === 'paid' && $paid > 0) ? $paid : $quoted;

            $rows->push([
                'kind'   => 'appointment',
                'at'     => $apt->starts_at,
                'label'  => 'Appointment',
                'detail' => trim(($apt->services->pluck('service_name')->filter()->join(', ') ?: 'Services')
                    . ($apt->staff?->name ? ' · '.$apt->staff->name : '')),
                'amount' => $amount,
                'status' => (string) $apt->status,
                'display_status' => self::displayStatusForAppointment((string) $apt->status),
                'action_label' => 'Details',
                'url'    => route('appointments.show', $apt->id),
            ]);
        }

        foreach ($sales as $tx) {
            $at = $tx->completed_at ?? $tx->created_at;
            $lineSummary = $tx->items->map(function ($line) {
                $qty = (int) $line->quantity;
                $name = (string) $line->name;

                return $qty > 1 ? "{$name} × {$qty}" : $name;
            })->filter()->take(4)->join(', ');

            $paymentLabel = $tx->payment_method
                ? ucfirst(str_replace('_', ' ', (string) $tx->payment_method))
                : null;
            $detailWithPay = $lineSummary !== '' ? $lineSummary : ($tx->reference ?? 'Walk-in sale');
            if ($paymentLabel) {
                $detailWithPay .= ' · '.$paymentLabel;
            }

            $rows->push([
                'kind'   => 'sale',
                'at'     => $at,
                'label'  => 'POS sale',
                'detail' => $detailWithPay,
                'amount' => (float) $tx->total,
                'status' => 'completed',
                'display_status' => 'Done',
                'action_label' => 'Receipt',
                'url'    => route('pos.show', $tx->id),
            ]);
        }

        return $rows
            ->sortByDesc(fn (array $row) => $row['at']?->timestamp ?? 0)
            ->take($limit)
            ->values();
    }

    public static function formatAmount(float $amount, string $currency): string
    {
        return \App\Helpers\CurrencyHelper::format($amount, $currency);
    }

    /**
     * @return array<int, Collection<int, array<string, mixed>>>
     */
    public static function forClientIds(int $salonId, array $clientIds, int $perClient = 8): array
    {
        if ($clientIds === []) {
            return [];
        }

        $appointments = Appointment::withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->whereIn('client_id', $clientIds)
            ->with(['staff:id,first_name,last_name', 'services:id,appointment_id,service_id,service_name'])
            ->orderByDesc('starts_at')
            ->get()
            ->groupBy('client_id');

        $sales = PosTransaction::withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->whereIn('client_id', $clientIds)
            ->where('status', 'completed')
            ->with(['items', 'staff:id,first_name,last_name'])
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('client_id');

        $out = [];
        foreach ($clientIds as $clientId) {
            $clientId = (int) $clientId;
            $rows = collect();
            $clientSales = $sales->get($clientId, collect());
            $saleAppointmentIds = $clientSales->pluck('appointment_id')->filter()->map(fn ($id) => (int) $id)->flip();

            foreach ($appointments->get($clientId, collect()) as $apt) {
                if ($saleAppointmentIds->has((int) $apt->id)) {
                    continue;
                }

                $paid = (float) ($apt->amount_paid ?? 0);
                $quoted = (float) ($apt->total_price ?? 0);
                $amount = ($apt->payment_status === 'paid' && $paid > 0) ? $paid : $quoted;

                $rows->push([
                    'kind'   => 'appointment',
                    'at'     => $apt->starts_at?->toIso8601String(),
                    'label'  => 'Appointment',
                    'detail' => trim(($apt->services->pluck('service_name')->filter()->join(', ') ?: 'Services')
                        . ($apt->staff?->name ? ' · '.$apt->staff->name : '')),
                    'amount' => $amount,
                    'status' => (string) $apt->status,
                    'display_status' => self::displayStatusForAppointment((string) $apt->status),
                    'action_label' => 'Details',
                    'url'    => route('appointments.show', $apt->id),
                ]);
            }

            foreach ($clientSales as $tx) {
                $at = $tx->completed_at ?? $tx->created_at;
                $lineSummary = $tx->items->map(function ($line) {
                    $qty = (int) $line->quantity;

                    return ((int) $line->quantity) > 1
                        ? $line->name.' × '.$qty
                        : (string) $line->name;
                })->filter()->take(4)->join(', ');

                $paymentLabel = $tx->payment_method
                    ? ucfirst(str_replace('_', ' ', (string) $tx->payment_method))
                    : null;
                $detailWithPay = $lineSummary !== '' ? $lineSummary : ($tx->reference ?? 'Sale');
                if ($paymentLabel) {
                    $detailWithPay .= ' · '.$paymentLabel;
                }

                $rows->push([
                    'kind'   => 'sale',
                    'at'     => $at?->toIso8601String(),
                    'label'  => 'POS sale',
                    'detail' => $detailWithPay,
                    'amount' => (float) $tx->total,
                    'status' => 'completed',
                    'display_status' => 'Done',
                    'action_label' => 'Receipt',
                    'url'    => route('pos.show', $tx->id),
                ]);
            }

            $out[$clientId] = $rows
                ->sortByDesc('at')
                ->take($perClient)
                ->values()
                ->all();
        }

        return $out;
    }

    private static function displayStatusForAppointment(string $status): string
    {
        return $status === 'completed' ? 'Done' : ucfirst(str_replace('_', ' ', $status));
    }
}
