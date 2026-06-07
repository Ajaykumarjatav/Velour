<?php
namespace Database\Factories;
use App\Models\BusinessType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SalonFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->company();
        return [
            'owner_id'               => User::factory(),
            'business_type_id'       => static function () {
                $existing = BusinessType::query()->where('slug', 'unisex')->first();
                if ($existing) {
                    return $existing->id;
                }

                return BusinessType::query()->create([
                    'name'       => 'Unisex',
                    'slug'       => 'unisex',
                    'sort_order' => 0,
                ])->id;
            },
            'name'                   => $name,
            'slug'                   => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'phone'                  => $this->faker->phoneNumber(),
            'email'                  => $this->faker->companyEmail(),
            'address_line1'          => $this->faker->streetAddress(),
            'city'                   => $this->faker->city(),
            'postcode'               => $this->faker->postcode(),
            'country'                => 'GB',
            'timezone'               => 'Europe/London',
            'currency'               => 'GBP',
            'online_booking_enabled' => true,
            'new_client_booking_enabled' => true,
            'deposit_required'       => false,
            'is_active'              => true,
            'opening_hours'          => [
                'Monday'=>['open'=>true,'start'=>'09:00','end'=>'18:00'],
                'Tuesday'=>['open'=>true,'start'=>'09:00','end'=>'18:00'],
                'Wednesday'=>['open'=>true,'start'=>'09:00','end'=>'18:00'],
                'Thursday'=>['open'=>true,'start'=>'09:00','end'=>'18:00'],
                'Friday'=>['open'=>true,'start'=>'09:00','end'=>'18:00'],
                'Saturday'=>['open'=>true,'start'=>'09:00','end'=>'16:00'],
                'Sunday'=>['open'=>false,'start'=>null,'end'=>null],
            ],
        ];
    }
}
