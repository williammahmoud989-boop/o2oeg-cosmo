<?php

namespace Database\Factories;

use App\Models\StaffPerformanceMetric;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffPerformanceMetricFactory extends Factory
{
    protected $model = StaffPerformanceMetric::class;

    public function definition(): array
    {
        return [
            'staff_id' => Staff::factory(),
            'month' => $this->faker->numberBetween(1, 12),
            'year' => $this->faker->year(),
            'performance_score' => $this->faker->numberBetween(40, 100),
            'average_rating' => $this->faker->numberBetween(2, 5),
            'completion_rate' => $this->faker->numberBetween(60, 100),
            'attendance_rate' => $this->faker->numberBetween(50, 100),
            'total_bookings' => $this->faker->numberBetween(5, 50),
            'completed_bookings' => $this->faker->numberBetween(3, 50),
            'total_revenue' => $this->faker->numberBetween(500, 5000),
            'total_commission' => $this->faker->numberBetween(50, 500),
            'total_reviews' => $this->faker->numberBetween(2, 30),
            'present_days' => $this->faker->numberBetween(15, 25),
            'absent_days' => $this->faker->numberBetween(0, 5),
            'late_days' => $this->faker->numberBetween(0, 3),
        ];
    }
}
