<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsMetric extends Model
{
    protected $fillable = [
        'salon_id',
        'date',
        'total_bookings',
        'completed_bookings',
        'cancelled_bookings',
        'total_revenue',
        'commission_charged',
        'net_revenue',
        'unique_customers',
        'returning_customers',
        'average_booking_value',
        'occupancy_rate',
    ];

    protected $casts = [
        'date' => 'date',
        'total_revenue' => 'decimal:2',
        'commission_charged' => 'decimal:2',
        'net_revenue' => 'decimal:2',
        'average_booking_value' => 'decimal:2',
        'occupancy_rate' => 'decimal:2',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    /**
     * Get metrics for a date range
     */
    public static function getMetricsForRange($salonId, $startDate, $endDate)
    {
        return self::where('salon_id', $salonId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }

    /**
     * Get aggregated data for a period
     */
    public static function getAggregatedMetrics($salonId, $startDate, $endDate)
    {
        return self::where('salon_id', $salonId)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                SUM(total_bookings) as total_bookings,
                SUM(completed_bookings) as completed_bookings,
                SUM(cancelled_bookings) as cancelled_bookings,
                SUM(total_revenue) as total_revenue,
                SUM(commission_charged) as commission_charged,
                SUM(net_revenue) as net_revenue,
                SUM(unique_customers) as unique_customers,
                SUM(returning_customers) as returning_customers,
                AVG(average_booking_value) as average_booking_value,
                AVG(occupancy_rate) as occupancy_rate
            ')
            ->first();
    }
}
