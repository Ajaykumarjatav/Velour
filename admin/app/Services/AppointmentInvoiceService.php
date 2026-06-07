<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

/**
 * Ensures a completed POS transaction exists so invoices can be generated for any completed appointment.
 */
class AppointmentInvoiceService
{
    public static function ensurePosTransaction(Appointment $appointment): ?PosTransaction
    {
        $appointment->loadMissing(['services', 'transaction', 'client', 'staff', 'salon']);

        if ($appointment->transaction) {
            return $appointment->transaction;
        }

        if ($appointment->status !== 'completed') {
            return null;
        }

        return DB::transaction(function () use ($appointment) {
            $appointment->refresh();
            if ($appointment->transaction) {
                return $appointment->transaction;
            }

            $salonId = (int) $appointment->salon_id;
            $staffId = (int) ($appointment->staff_id ?? 0);
            if ($staffId <= 0) {
                return null;
            }

            $lines = [];
            foreach ($appointment->services as $svc) {
                $lines[] = [
                    'service_id' => $svc->service_id,
                    'name'       => (string) $svc->service_name,
                    'price'      => (float) $svc->price,
                ];
            }

            if ($lines === []) {
                $lines[] = [
                    'service_id' => null,
                    'name'       => 'Appointment services',
                    'price'      => (float) $appointment->total_price,
                ];
            }

            $subtotal = round(collect($lines)->sum('price'), 2);
            $taxRate = 0.20;
            $taxAmount = round($subtotal * $taxRate, 2);
            $total = round($subtotal + $taxAmount, 2);
            $paid = (float) $appointment->amount_paid;
            if ($paid <= 0) {
                $paid = $total;
            }

            $tx = PosTransaction::create([
                'salon_id'        => $salonId,
                'client_id'       => $appointment->client_id,
                'staff_id'        => $staffId,
                'appointment_id'  => $appointment->id,
                'payment_method'  => 'cash',
                'subtotal'        => $subtotal,
                'discount_amount' => 0,
                'tax_amount'      => $taxAmount,
                'total'           => max($total, $paid),
                'amount_tendered' => $paid,
                'change_given'    => max(0, $paid - max($total, $paid)),
                'status'          => 'completed',
                'completed_at'    => $appointment->ends_at ?? now(),
            ]);

            foreach ($lines as $line) {
                $serviceId = $line['service_id'] ? (int) $line['service_id'] : null;
                $itemableType = $serviceId ? Service::class : null;

                PosTransactionItem::create([
                    'transaction_id' => $tx->id,
                    'itemable_id'    => $serviceId,
                    'itemable_type'  => $itemableType,
                    'name'           => $line['name'],
                    'type'           => 'service',
                    'quantity'       => 1,
                    'unit_price'     => $line['price'],
                    'discount'       => 0,
                    'total'          => $line['price'],
                    'staff_id'       => $staffId,
                ]);
            }

            $appointment->update([
                'amount_paid'    => max($paid, (float) $appointment->amount_paid),
                'total_price'    => max((float) $appointment->total_price, $tx->total),
                'payment_status' => $paid >= $tx->total - 0.01
                    ? Appointment::PAYMENT_PAID
                    : ($paid > 0 ? Appointment::PAYMENT_PARTIAL : $appointment->payment_status),
            ]);

            return $tx->fresh(['items']);
        });
    }
}
