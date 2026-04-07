<?php

namespace App\Listeners;

use App\Events\BookingSaved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AwardLoyaltyPoints
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected \App\Services\Loyalty\LoyaltyService $loyaltyService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(BookingSaved $event): void
    {
        $booking = $event->booking;

        // Points are only awarded when the service is fully completed
        if ($booking->status === 'completed' && !$booking->points_earned) {
            $this->loyaltyService->awardPoints($booking->user, $booking);
        }
    }
}
