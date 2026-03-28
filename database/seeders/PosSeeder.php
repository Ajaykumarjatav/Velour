<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Client;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\MarketingCampaign;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Review;
use App\Models\Salon;
use App\Models\SalonNotification;
use App\Models\SalonSetting;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// ════════════════════════════════════════════════════════════════════════════
// PosSeeder — transaction history matching completed appointments
// ════════════════════════════════════════════════════════════════════════════
class PosSeeder extends Seeder
{
    private array $paymentMethods = ['card','card','card','card','cash','split'];

    public function run(): void
    {
        $salon   = Salon::first();
        $count   = 0;

        $completedAppts = Appointment::with(['services','client','staff'])
            ->where('salon_id', $salon->id)
            ->where('status', 'completed')
            ->get();

        foreach ($completedAppts as $appt) {
            $subtotal     = $appt->total_price;
            $tipAmount    = rand(0, 10) < 3 ? round($subtotal * 0.10, 2) : 0; // 30% chance of tip
            $taxRate      = 0.20;
            $taxAmount    = round($subtotal * $taxRate, 2);
            $total        = round($subtotal + $taxAmount + $tipAmount, 2);
            $method       = $this->paymentMethods[array_rand($this->paymentMethods)];

            $tx = PosTransaction::create([
                'salon_id'       => $salon->id,
                'client_id'      => $appt->client_id,
                'staff_id'       => $appt->staff_id,
                'appointment_id' => $appt->id,
                'reference'      => 'TXN-' . strtoupper(Str::random(8)),
                'subtotal'       => $subtotal,
                'discount_amount'=> 0,
                'tax_amount'     => $taxAmount,
                'tip_amount'     => $tipAmount,
                'total'          => $total,
                'amount_tendered'=> $total,
                'payment_method' => $method,
                'status'         => 'completed',
                'completed_at'   => $appt->starts_at,
                'created_at'     => $appt->starts_at,
                'updated_at'     => $appt->starts_at,
            ]);

            foreach ($appt->services as $svc) {
                PosTransactionItem::create([
                    'transaction_id' => $tx->id,
                    'name'           => $svc->service_name,
                    'type'           => 'service',
                    'quantity'       => 1,
                    'unit_price'     => $svc->price,
                    'discount'       => 0,
                    'total'          => $svc->price,
                    'staff_id'       => $appt->staff_id,
                ]);
            }

            $count++;
        }

        $this->command->info("   ✓  {$count} POS transactions created.");
    }
}
