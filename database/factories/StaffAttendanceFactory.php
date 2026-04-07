<?php

namespace Database\Factories;

use App\Models\StaffAttendance;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffAttendanceFactory extends Factory
{
    protected $model = StaffAttendance::class;

    public function definition(): array
    {
        $checkInTime = $this->faker->time('H:i');
        $checkOutTime = date('H:i', strtotime($checkInTime) + 28800); // 8 hours later
        
        return [
            'staff_id' => Staff::factory(),
            'date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'check_in_time' => $checkInTime,
            'check_out_time' => $checkOutTime,
            'status' => $this->faker->randomElement(['present', 'absent', 'late', 'half_day', 'on_leave']),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }
}
