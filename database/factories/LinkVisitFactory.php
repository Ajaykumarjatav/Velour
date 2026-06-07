<?php

namespace Database\Factories;

use App\Models\LinkVisit;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LinkVisitFactory extends Factory
{
    protected $model = LinkVisit::class;

    public function definition(): array
    {
        return [
            'salon_id'     => Salon::factory(),
            'source'       => $this->faker->randomElement(['instagram','whatsapp','facebook','google','direct','qr','embed','email']),
            'page'         => 'booking',
            'ip_address'   => $this->faker->ipv4(),
            'country'      => $this->faker->countryCode(),
            'device'       => $this->faker->randomElement(['mobile','desktop']),
            'converted'    => $this->faker->boolean(25),
            'utm_source'   => null,
            'utm_medium'   => null,
            'utm_campaign' => null,
            'referrer'     => null,
        ];
    }

    public function converted(): static
    {
        return $this->state(['converted' => true]);
    }

    public function mobile(): static
    {
        return $this->state(['device' => 'mobile']);
    }

    public function fromInstagram(): static
    {
        return $this->state(['source' => 'instagram']);
    }
}
