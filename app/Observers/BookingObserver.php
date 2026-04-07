<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\Loyalty\LoyaltyService;
use Illuminate\Support\Facades\Log;

class BookingObserver
{
    protected $loyalty;

    public function __construct(LoyaltyService $loyalty)
    {
        $this->loyalty = $loyalty;
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        // Check if the status has changed to 'completed'
        if ($booking->isDirty('status') && $booking->status === 'completed') {
            try {
                // Award points only if not already earned for this booking
                if (!$booking->points_earned) {
                    $this->loyalty->awardPoints($booking->user, $booking);
                    Log::info("Loyalty points awarded for booking: {$booking->booking_code}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to award loyalty points: " . $e->getMessage());
            }
        }
    }
}
