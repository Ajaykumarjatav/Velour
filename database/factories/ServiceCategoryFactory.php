<?php
namespace Database\Factories;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceCategoryFactory extends Factory
{
    protected $model = \App\Models\ServiceCategory::class;
    public function definition(): array
    {
        $name = $this->faker->randomElement(['Hair Colour','Cuts & Styling','Nails','Skin & Facials','Massage']);
        return [
            'salon_id'   => Salon::factory(),
            'name'       => $name,
            'slug'       => Str::slug($name),
            'sort_order' => 0,
        ];
    }
}
