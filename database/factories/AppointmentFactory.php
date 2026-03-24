<?php
namespace Database\Factories;
use App\Models\Salon;
use App\Models\Staff;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('now', '+30 days');
        return [
            'salon_id'         => Salon::factory(),
            'client_id'        => Client::factory(),
            'staff_id'         => Staff::factory(),
            'reference'        => 'APT-' . strtoupper(Str::random(8)),
            'starts_at'        => $startsAt,
            'ends_at'          => (clone $startsAt)->modify('+60 minutes'),
            'duration_minutes' => 60,
            'total_price'      => 85.00,
            'status'           => 'confirmed',
            'source'           => 'manual',
        ];
    }
}
