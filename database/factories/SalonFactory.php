<?php

namespace Database\Factories;

use App\Models\Salon;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalonFactory extends Factory
{
    protected $model = Salon::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->company() . ' Salon',
            'subdomain' => strtolower(str_replace(' ', '-', uniqid())),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'status' => 'active',
        ];
    }
}
