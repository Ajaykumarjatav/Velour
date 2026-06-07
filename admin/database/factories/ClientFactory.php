<?php
namespace Database\Factories;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'salon_id'          => Salon::factory(),
            'first_name'        => $this->faker->firstName('female'),
            'last_name'         => $this->faker->lastName(),
            'email'             => $this->faker->unique()->safeEmail(),
            'phone'             => $this->faker->phoneNumber(),
            'color'             => '#C4556B',
            'tags'              => [],
            'marketing_consent' => true,
            'sms_consent'       => true,
            'email_consent'     => true,
            'status'            => 'active',
            'is_vip'            => false,
            'total_spent'       => 0,
            'visit_count'       => 0,
            'source'            => 'walk_in',
        ];
    }
}
