<?php

namespace App\Services\Payment;

use App\Models\Salon;
use App\Models\Service;
use App\Models\PricingRule;
use App\Models\Booking;
use Carbon\Carbon;

class PricingService
{
    /**
     * Calculate final price based on base price and active pricing rules (Happy Hour, etc.).
     *
     * @param Salon $salon
     * @param Service $service
     * @param string $date (Y-m-d)
     * @param string $time (H:i)
     * @return array ['final_price' => float, 'discount_applied' => float, 'rule_name' => ?string]
     */
    public function calculatePrice(Salon $salon, Service $service, string $date, string $time): array
    {
        $basePrice = (float) ($service->discount_price ?? $service->price);
        $bookingDate = Carbon::parse($date);
        $dayName = strtolower($bookingDate->format('l'));
        
        // 1. Check for Manual Pricing Rules (Happy Hour, etc.)
        $rule = PricingRule::where('salon_id', $salon->id)
            ->where('is_active', true)
            ->where(function ($query) use ($dayName) {
                $query->where('day_of_week', $dayName)
                      ->orWhere('day_of_week', 'all');
            })
            ->whereTime('start_time', '<=', $time)
            ->whereTime('end_time', '>=', $time)
            ->first();

        if ($rule) {
            $ruleDiscount = ($basePrice * $rule->percentage) / 100;
            $finalPrice = max(0, $basePrice - $ruleDiscount);

            return [
                'final_price' => $finalPrice,
                'discount_applied' => $ruleDiscount,
                'rule_name' => "Manual Rule: " . $rule->name,
                'type' => 'discount'
            ];
        }

        // 2. AI Dynamic Pricing (Occupancy Based)
        $bookingCount = Booking::where('salon_id', $salon->id)
            ->where('booking_date', $date)
            ->where('status', '!=', 'cancelled')
            ->count();
        
        $capacity = $salon->daily_capacity ?: 20;
        $threshold = $salon->occupancy_threshold_high ?: 70;
        $occupancyRate = ($bookingCount / $capacity) * 100;

        if ($occupancyRate >= $threshold) {
            $surchargePercent = $salon->peak_surcharge_percentage ?: 10;
            $surchargeAmount = ($basePrice * $surchargePercent) / 100;
            $finalPrice = $basePrice + $surchargeAmount;

            return [
                'final_price' => $finalPrice,
                'discount_applied' => -$surchargeAmount, // Negative discount = surcharge
                'rule_name' => "AI Peak Pricing (Occupancy: " . round($occupancyRate) . "%)",
                'type' => 'peak'
            ];
        }

        return [
            'final_price' => $basePrice,
            'discount_applied' => 0,
            'rule_name' => null,
            'type' => 'base'
        ];
    }
}
