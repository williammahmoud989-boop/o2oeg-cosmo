<?php

namespace Database\Factories;

use App\Models\StaffReview;
use App\Models\Staff;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffReviewFactory extends Factory
{
    protected $model = StaffReview::class;

    public function definition(): array
    {
        return [
            'staff_id' => Staff::factory(),
            'user_id' => User::factory(),
            'booking_id' => Booking::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->optional(0.7)->sentence(),
        ];
    }
}
