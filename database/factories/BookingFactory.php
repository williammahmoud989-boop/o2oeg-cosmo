<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Staff;
use App\Models\User;
use App\Models\Service;
use App\Models\Salon;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeInInterval('-1 month', '+1 month');
        $startTime = $this->faker->time('H:i');
        
        return [
            'salon_id' => Salon::factory(),
            'service_id' => \App\Models\Service::factory(),
            'staff_id' => Staff::factory(),
            'user_id' => User::factory(),
            'booking_code' => 'BK' . uniqid(),
            'booking_date' => $startDate,
            'start_time' => $startTime,
            'end_time' => date('H:i', strtotime($startTime) + 3600),
            'total_price' => $this->faker->numberBetween(50, 300),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'completed', 'cancelled']),
            'payment_status' => 'pending',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
