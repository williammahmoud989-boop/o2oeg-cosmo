<?php

namespace App\Services;

use App\Models\AnalyticsMetric;
use App\Models\Booking;
use App\Models\Salon;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Calculate and store daily metrics for a salon
     */
    public function calculateDailyMetrics(Salon $salon, $date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        $dateStr = $date->toDateString();

        // Get bookings for the date
        $bookings = Booking::where('salon_id', $salon->id)
            ->whereDate('booking_date', $dateStr)
            ->get();

        $completedBookings = $bookings->where('status', 'completed')->count();
        $cancelledBookings = $bookings->where('status', 'cancelled')->count();
        $totalBookings = $bookings->count();

        // Calculate revenue
        $completedRevenue = $bookings->where('status', 'completed')
            ->sum('total_price');
        
        $commission = $completedRevenue * ($salon->commission_rate ?? 10) / 100;
        $netRevenue = $completedRevenue - $commission;

        // Customer metrics
        $uniqueCustomers = $bookings->pluck('user_id')->unique()->count();
        $returningCustomers = $this->getReturningCustomersCount($salon->id, $dateStr, $uniqueCustomers);

        $avgBookingValue = $completedBookings > 0 
            ? $completedRevenue / $completedBookings 
            : 0;

        // Occupancy rate calculation
        $occupancyRate = $this->calculateOccupancyRate($salon, $dateStr);

        // Store or update metrics
        $metric = AnalyticsMetric::updateOrCreate(
            [
                'salon_id' => $salon->id,
                'date' => $dateStr,
            ],
            [
                'total_bookings' => $totalBookings,
                'completed_bookings' => $completedBookings,
                'cancelled_bookings' => $cancelledBookings,
                'total_revenue' => $completedRevenue,
                'commission_charged' => $commission,
                'net_revenue' => $netRevenue,
                'unique_customers' => $uniqueCustomers,
                'returning_customers' => $returningCustomers,
                'average_booking_value' => $avgBookingValue,
                'occupancy_rate' => $occupancyRate,
            ]
        );

        return $metric;
    }

    /**
     * Calculate how many customers are returning
     */
    private function getReturningCustomersCount($salonId, $date, $totalCustomers)
    {
        $currentDate = Carbon::parse($date);
        $thirtyDaysAgo = $currentDate->copy()->subDays(30);

        $returningIds = Booking::where('salon_id', $salonId)
            ->whereDate('booking_date', '<', $date)
            ->whereDate('booking_date', '>=', $thirtyDaysAgo)
            ->pluck('user_id')
            ->unique();

        $currentIds = Booking::where('salon_id', $salonId)
            ->whereDate('booking_date', $date)
            ->pluck('user_id')
            ->unique();

        return $currentIds->intersect($returningIds)->count();
    }

    /**
     * Calculate occupancy rate (booked time / available time)
     */
    private function calculateOccupancyRate(Salon $salon, $date)
    {
        $bookings = Booking::where('salon_id', $salon->id)
            ->whereDate('booking_date', $date)
            ->where('status', '!=', 'cancelled')
            ->get();

        $totalServiceMinutes = $bookings->sum(function ($booking) {
            return $booking->service->duration_minutes ?? 0;
        });

        // Assume 8 working hours per day per staff
        $staffCount = $salon->staff()->where('is_active', true)->count() ?? 1;
        $availableMinutes = (8 * 60) * $staffCount;

        if ($availableMinutes == 0) {
            return 0;
        }

        return min(100, ($totalServiceMinutes / $availableMinutes) * 100);
    }

    /**
     * Get dashboard summary for a period
     */
    public function getDashboardSummary(Salon $salon, $days = 30)
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays($days);

        $metrics = AnalyticsMetric::getAggregatedMetrics(
            $salon->id,
            $startDate,
            $endDate
        );

        // Calculate growth compared to previous period
        $previousStartDate = $startDate->copy()->subDays($days);
        $previousMetrics = AnalyticsMetric::getAggregatedMetrics(
            $salon->id,
            $previousStartDate,
            $startDate->copy()->subDay()
        );

        return [
            'current_period' => $metrics,
            'previous_period' => $previousMetrics,
            'revenue_growth' => $this->calculateGrowth(
                $previousMetrics?->total_revenue ?? 0,
                $metrics?->total_revenue ?? 0
            ),
            'booking_growth' => $this->calculateGrowth(
                $previousMetrics?->total_bookings ?? 0,
                $metrics?->total_bookings ?? 0
            ),
            'customer_growth' => $this->calculateGrowth(
                $previousMetrics?->unique_customers ?? 0,
                $metrics?->unique_customers ?? 0
            ),
        ];
    }

    /**
     * Calculate percentage growth
     */
    private function calculateGrowth($previousValue, $currentValue)
    {
        if ($previousValue == 0) {
            return $currentValue > 0 ? 100 : 0;
        }

        return (($currentValue - $previousValue) / $previousValue) * 100;
    }

    /**
     * Get top services by revenue
     */
    public function getTopServicesByRevenue(Salon $salon, $days = 30)
    {
        $startDate = Carbon::now()->subDays($days);

        return Booking::where('salon_id', $salon->id)
            ->where('status', 'completed')
            ->whereDate('booking_date', '>=', $startDate)
            ->selectRaw('service_id, COUNT(*) as count, SUM(total_price) as revenue')
            ->groupBy('service_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->with('service')
            ->get();
    }

    /**
     * Get customer retention rate
     */
    public function getRetentionRate(Salon $salon, $days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();

        $totalCurrentCustomers = Booking::where('salon_id', $salon->id)
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->pluck('user_id')
            ->unique()
            ->count();

        if ($totalCurrentCustomers == 0) {
            return 0;
        }

        $returningCustomers = Booking::where('salon_id', $salon->id)
            ->whereDate('booking_date', '<', $startDate)
            ->pluck('user_id')
            ->unique()
            ->intersect(
                Booking::where('salon_id', $salon->id)
                    ->whereBetween('booking_date', [$startDate, $endDate])
                    ->pluck('user_id')
                    ->unique()
            )
            ->count();

        return ($returningCustomers / $totalCurrentCustomers) * 100;
    }
}
