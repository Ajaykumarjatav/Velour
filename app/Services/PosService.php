<?php

namespace App\Services;

use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\InventoryItem;
use App\Models\Voucher;
use App\Models\Client;
use App\Models\Staff;
use App\Models\Appointment;
use App\Models\SalonNotification;
use App\Models\MarketingCampaign;
use App\Models\LinkVisit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class PosService
{
    /**
     * Process a full POS checkout.
     */
    public function process(int $salonId, array $data): PosTransaction
    {
        return DB::transaction(function () use ($salonId, $data) {
            // Calculate totals
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $subtotal += $lineTotal;
            }

            // Apply voucher
            $discountAmount = 0;
            if (!empty($data['discount_code'])) {
                $voucher = Voucher::where('salon_id', $salonId)
                    ->where('code', strtoupper($data['discount_code']))
                    ->valid()
                    ->first();

                if ($voucher) {
                    $discountAmount = match ($voucher->type) {
                        'percentage' => round($subtotal * ($voucher->value / 100), 2),
                        'fixed'      => min($voucher->value, $subtotal),
                        'gift_card'  => min($voucher->remaining_balance, $subtotal),
                        default      => 0,
                    };
                    // Increment usage
                    $voucher->increment('usage_count');
                    if ($voucher->type === 'gift_card') {
                        $voucher->decrement('remaining_balance', $discountAmount);
                    }
                }
            }

            $taxRate      = 0.20; // VAT 20%
            $netAfterDisc = $subtotal - $discountAmount;
            $taxAmount    = round($netAfterDisc * $taxRate, 2);
            $tipAmount    = $data['tip_amount'] ?? 0;
            $total        = $netAfterDisc + $taxAmount + $tipAmount;
            $changeDue    = isset($data['amount_tendered'])
                ? max(0, $data['amount_tendered'] - $total)
                : 0;

            $tx = PosTransaction::create([
                'salon_id'                 => $salonId,
                'client_id'               => $data['client_id'] ?? null,
                'staff_id'                => $data['staff_id'],
                'appointment_id'          => $data['appointment_id'] ?? null,
                'reference'               => 'TXN-' . strtoupper(uniqid()),
                'subtotal'                => round($subtotal, 2),
                'discount_amount'         => $discountAmount,
                'discount_code'           => $data['discount_code'] ?? null,
                'tax_amount'              => $taxAmount,
                'tip_amount'              => $tipAmount,
                'total'                   => round($total, 2),
                'amount_tendered'         => $data['amount_tendered'] ?? $total,
                'change_given'            => $changeDue,
                'payment_method'          => $data['payment_method'],
                'status'                  => 'completed',
                'stripe_payment_intent_id'=> $data['stripe_payment_intent_id'] ?? null,
                'notes'                   => $data['notes'] ?? null,
                'completed_at'            => now(),
            ]);

            foreach ($data['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);

                PosTransactionItem::create([
                    'transaction_id' => $tx->id,
                    'name'           => $item['name'],
                    'type'           => $item['type'],
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $item['unit_price'],
                    'discount'       => $item['discount'] ?? 0,
                    'total'          => $lineTotal,
                    'staff_id'       => $item['staff_id'] ?? $data['staff_id'],
                ]);

                // Reduce inventory stock if it's a product sale
                if ($item['type'] === 'product' && !empty($item['inventory_item_id'])) {
                    $invItem = InventoryItem::find($item['inventory_item_id']);
                    if ($invItem) {
                        $invItem->decrement('stock_quantity', $item['quantity']);
                    }
                }
            }

            // Update client total spent
            if ($tx->client_id) {
                Client::find($tx->client_id)?->increment('total_spent', $tx->total);
            }

            return $tx;
        });
    }

    /**
     * Issue a refund.
     */
    public function refund(PosTransaction $tx, float $amount, ?string $reason): PosTransaction
    {
        // Stripe refund in production
        if ($tx->stripe_payment_intent_id) {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            \Stripe\Refund::create([
                'payment_intent' => $tx->stripe_payment_intent_id,
                'amount'         => (int) round($amount * 100),
                'reason'         => 'requested_by_customer',
            ]);
        }

        $tx->update([
            'status' => $amount >= $tx->total ? 'refunded' : 'partial_refund',
            'notes'  => trim(($tx->notes ?? '') . "\nRefund: $reason"),
        ]);

        return $tx;
    }

    public function handlePaymentSuccess(\Stripe\PaymentIntent $intent): void
    {
        PosTransaction::where('stripe_payment_intent_id', $intent->id)
            ->update(['status' => 'completed', 'completed_at' => now()]);
    }

    public function handlePaymentFailed(\Stripe\PaymentIntent $intent): void
    {
        PosTransaction::where('stripe_payment_intent_id', $intent->id)
            ->update(['status' => 'voided']);
    }
}
