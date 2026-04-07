<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'salon_id' => Salon::factory(),
            'name' => $this->faker->word(),
            'name_ar' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement(['haircut', 'coloring', 'styling', 'manicure', 'pedicure']),
            'price' => $this->faker->numberBetween(50, 300),
            'duration_minutes' => $this->faker->numberBetween(30, 120),
            'is_active' => true,
        ];
    }
}
