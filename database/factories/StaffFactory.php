<?php
namespace Database\Factories;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    public function definition(): array
    {
        $first = $this->faker->firstName('female');
        $last  = $this->faker->lastName();
        return [
            'salon_id'        => Salon::factory(),
            'first_name'      => $first,
            'last_name'       => $last,
            'email'           => $this->faker->unique()->safeEmail(),
            'phone'           => $this->faker->phoneNumber(),
            'role'            => $this->faker->randomElement(['Colourist','Hair Stylist','Nail Technician','Therapist']),
            'initials'        => substr($first, 0, 1) . substr($last, 0, 1),
            'color'           => '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT),
            'commission_rate' => $this->faker->randomFloat(2, 30, 50),
            'access_level'    => 'staff',
            'is_active'       => true,
            'bookable_online' => true,
            'working_days'    => ['Mon','Tue','Wed','Thu','Fri'],
            'start_time'      => '09:00:00',
            'end_time'        => '18:00:00',
            'sort_order'      => 0,
        ];
    }
}
