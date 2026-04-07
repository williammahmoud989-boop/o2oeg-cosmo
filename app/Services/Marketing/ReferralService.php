<?php

namespace App\Services\Marketing;

use App\Models\Booking;
use App\Models\Influencer;

class ReferralService
{
    /**
     * Track a booking if a referral code is provided.
     * Calculates commission based on booking price and influencer rate.
     * 
     * @param \App\Models\Booking $booking The booking to be tracked
     * @param string|null $referralCode Code from the influencer
     * @return void
     */
    public function trackBooking(Booking $booking, ?string $referralCode): void
    {
        if (empty($referralCode)) {
            return;
        }

        /** @var \App\Models\Influencer|null $influencer */
        $influencer = Influencer::where('referral_code', $referralCode)
            ->where('is_active', true)
            ->first();

        if ($influencer) {
            // Calculate commission: price * (rate/100)
            $commission = ($booking->total_price * $influencer->commission_rate) / 100;
            
            $booking->update([
                'influencer_id' => $influencer->id,
                'influencer_commission' => $commission,
            ]);
        }
    }

    /**
     * Validate an influencer code and return the model if valid.
     * 
     * @param string $code
     * @return \App\Models\Influencer|null
     */
    public function validateCode(string $code): ?Influencer
    {
        return Influencer::query()
            ->where('referral_code', $code)
            ->where('is_active', true)
            ->first();
    }
}
