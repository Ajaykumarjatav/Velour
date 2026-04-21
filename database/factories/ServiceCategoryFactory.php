<?php
namespace Database\Factories;
use App\Models\BusinessType;
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
            'salon_id'         => Salon::factory(),
            'business_type_id' => function (array $attributes) {
                $salon = Salon::query()->find($attributes['salon_id']);

                return $salon ? (int) $salon->business_type_id : BusinessType::defaultId();
            },
            'name'             => $name,
            'slug'             => Str::slug($name).'-'.$this->faker->unique()->numerify('###'),
            'sort_order'       => 0,
        ];
    }
}
