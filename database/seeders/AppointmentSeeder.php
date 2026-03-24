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
// AppointmentSeeder
// ════════════════════════════════════════════════════════════════════════════
class AppointmentSeeder extends Seeder
{
    private array $statuses  = ['completed','completed','completed','completed','completed','no_show','cancelled'];
    private array $sources   = ['online','instagram','google','phone','walk_in','website_embed'];

    public function run(): void
    {
        $salon    = Salon::first();
        $clients  = Client::where('salon_id', $salon->id)->where('status','active')->get();
        $staff    = Staff::where('salon_id', $salon->id)->where('is_active', true)->get();
        $services = Service::where('salon_id', $salon->id)->where('status','active')->get();
        $count    = 0;

        // ── PAST: 90 days of historical appointments ──────────────────────
        for ($daysAgo = 90; $daysAgo >= 1; $daysAgo--) {
            $date = now()->subDays($daysAgo);
            // 2–8 appointments per day
            $dailyCount = rand(2, 8);

            $hour = 9; // start at 9am
            for ($a = 0; $a < $dailyCount && $hour < 17; $a++) {
                $client   = $clients->random();
                $member   = $staff->random();
                $service  = $services->random();
                $status   = $this->statuses[array_rand($this->statuses)];

                $startsAt = $date->copy()->setHour($hour)->setMinute(0)->setSecond(0);
                $duration = $service->duration_minutes + $service->buffer_minutes;
                $endsAt   = $startsAt->copy()->addMinutes($duration);

                $appt = Appointment::create([
                    'salon_id'         => $salon->id,
                    'client_id'        => $client->id,
                    'staff_id'         => $member->id,
                    'reference'        => 'APT-' . strtoupper(Str::random(8)),
                    'starts_at'        => $startsAt,
                    'ends_at'          => $endsAt,
                    'duration_minutes' => $service->duration_minutes,
                    'total_price'      => $service->price,
                    'status'           => $status,
                    'source'           => $this->sources[array_rand($this->sources)],
                    'confirmed_at'     => $startsAt->copy()->subDays(rand(1, 5)),
                ]);

                AppointmentService::create([
                    'appointment_id'   => $appt->id,
                    'service_id'       => $service->id,
                    'service_name'     => $service->name,
                    'duration_minutes' => $service->duration_minutes,
                    'price'            => $service->price,
                    'sort_order'       => 0,
                ]);

                $hour += (int) ceil($duration / 60) + 1;
                $count++;
            }
        }

        // ── FUTURE: next 14 days ──────────────────────────────────────────
        for ($daysAhead = 0; $daysAhead <= 14; $daysAhead++) {
            $date       = now()->addDays($daysAhead);
            $dailyCount = rand(3, 10);
            $hour       = 9;

            for ($a = 0; $a < $dailyCount && $hour < 17; $a++) {
                $client  = $clients->random();
                $member  = $staff->random();
                $service = $services->random();

                $startsAt = $date->copy()->setHour($hour)->setMinute(0)->setSecond(0);
                $duration = $service->duration_minutes + $service->buffer_minutes;
                $endsAt   = $startsAt->copy()->addMinutes($duration);

                $appt = Appointment::create([
                    'salon_id'         => $salon->id,
                    'client_id'        => $client->id,
                    'staff_id'         => $member->id,
                    'reference'        => 'APT-' . strtoupper(Str::random(8)),
                    'starts_at'        => $startsAt,
                    'ends_at'          => $endsAt,
                    'duration_minutes' => $service->duration_minutes,
                    'total_price'      => $service->price,
                    'status'           => 'confirmed',
                    'source'           => $this->sources[array_rand($this->sources)],
                    'confirmed_at'     => now()->subHours(rand(1, 48)),
                ]);

                AppointmentService::create([
                    'appointment_id'   => $appt->id,
                    'service_id'       => $service->id,
                    'service_name'     => $service->name,
                    'duration_minutes' => $service->duration_minutes,
                    'price'            => $service->price,
                    'sort_order'       => 0,
                ]);

                $hour += (int) ceil($duration / 60) + 1;
                $count++;
            }
        }

        $this->command->info("   ✓  ~{$count} appointments created (90 days past + 14 future).");
    }
}
