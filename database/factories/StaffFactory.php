<?php

namespace Database\Factories;

use App\Models\Staff;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        return [
            'salon_id' => Salon::factory(),
            'name' => $this->faker->firstName(),
            'specialization' => $this->faker->randomElement(['haircut', 'coloring', 'styling', 'manicure', 'pedicure']),
            'base_salary' => $this->faker->numberBetween(3000, 8000),
            'commission_rate' => $this->faker->numberBetween(5, 20),
            'is_active' => true,
            'attendance_reminder_enabled' => false,
            'attendance_time' => null,
            'whatsapp_number' => null,
            'privacy_consent' => false,
            'consent_given_at' => null,
        ];
    }
}
