<?php
namespace Database\Factories;
use App\Models\Salon;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'salon_id'         => Salon::factory(),
            'category_id'      => ServiceCategory::factory(),
            'name'             => $this->faker->randomElement(['Balayage','Cut & Blowdry','Gel Manicure','Signature Facial','Swedish Massage']),
            'duration_minutes' => $this->faker->randomElement([30, 45, 60, 90, 120]),
            'buffer_minutes'   => 10,
            'price'            => $this->faker->randomFloat(2, 25, 250),
            'deposit_type'     => 'none',
            'deposit_value'    => 0,
            'online_bookable'  => true,
            'show_in_menu'     => true,
            'status'           => 'active',
            'sort_order'       => 0,
        ];
    }
}
