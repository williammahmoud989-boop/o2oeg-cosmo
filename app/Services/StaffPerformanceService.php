<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\StaffPerformanceMetric;
use App\Models\StaffReview;
use Carbon\Carbon;

class StaffPerformanceService
{
    /**
     * Calculate monthly performance metrics for a staff member
     */
    public function calculateMonthlyMetrics(Staff $staff, $month = null, $year = null)
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get bookings for the month
        $bookings = Booking::where('staff_id', $staff->id)
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->get();

        $totalBookings = $bookings->count();
        $completedBookings = $bookings->where('status', 'completed')->count();

        // Calculate revenue
        $totalRevenue = $bookings->where('status', 'completed')->sum('total_price');
        $commissionRate = $staff->commission_rate ?? 10;
        $commissionEarned = $totalRevenue * ($commissionRate / 100);

        // Get reviews
        $reviews = StaffReview::where('staff_id', $staff->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $averageRating = $reviews->avg('rating') ?? 0;
        $totalReviews = $reviews->count();

        // Get attendance stats
        $attendanceStats = StaffAttendance::getMonthlyStats($staff->id, $month, $year);

        // Store or update metrics
        $metric = StaffPerformanceMetric::updateOrCreate(
            [
                'staff_id' => $staff->id,
                'month' => $month,
                'year' => $year,
            ],
            [
                'total_bookings' => $totalBookings,
                'completed_bookings' => $completedBookings,
                'total_revenue' => $totalRevenue,
                'commission_earned' => $commissionEarned,
                'average_rating' => $averageRating,
                'total_reviews' => $totalReviews,
                'attendance_days' => $attendanceStats?->present ?? 0,
                'absent_days' => $attendanceStats?->absent ?? 0,
                'late_days' => $attendanceStats?->late ?? 0,
                'half_days' => $attendanceStats?->half_day ?? 0,
            ]
        );

        return $metric;
    }

    /**
     * Get staff performance summary
     */
    public function getPerformanceSummary(Staff $staff, $lastMonths = 3)
    {
        $metrics = StaffPerformanceMetric::where('staff_id', $staff->id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit($lastMonths)
            ->get();

        return [
            'staff' => $staff,
            'current_rating' => StaffReview::getAverageRating($staff->id),
            'total_reviews' => StaffReview::getTotalReviews($staff->id),
            'recent_metrics' => $metrics,
            'total_revenue_3_months' => $metrics->sum('total_revenue'),
            'total_commission_3_months' => $metrics->sum('commission_earned'),
            'average_performance_score' => $metrics->avg('performance_score'),
        ];
    }

    /**
     * Get top performing staff
     */
    public function getTopPerformers($salonId, $month = null, $year = null, $limit = 10)
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        return StaffPerformanceMetric::whereHas('staff', function ($query) use ($salonId) {
            $query->where('salon_id', $salonId);
        })
            ->where('month', $month)
            ->where('year', $year)
            ->orderByDesc('performance_score')
            ->limit($limit)
            ->with('staff')
            ->get();
    }

    /**
     * Get attendance report
     */
    public function getAttendanceReport(Staff $staff, $startDate, $endDate)
    {
        $attendances = StaffAttendance::where('staff_id', $staff->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $stats = [
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'half_day' => $attendances->where('status', 'half_day')->count(),
            'on_leave' => $attendances->where('status', 'on_leave')->count(),
            'attendance_rate' => $this->calculateAttendanceRate($attendances),
        ];

        return [
            'staff' => $staff,
            'period' => ['start' => $startDate, 'end' => $endDate],
            'attendances' => $attendances,
            'stats' => $stats,
        ];
    }

    /**
     * Calculate attendance rate
     */
    private function calculateAttendanceRate($attendances)
    {
        $total = $attendances->count();

        if ($total == 0) {
            return 0;
        }

        $presentCount = $attendances->where('status', 'present')->count();
        $lateCount = $attendances->where('status', 'late')->count() * 0.5;
        $halfDayCount = $attendances->where('status', 'half_day')->count() * 0.5;

        return (($presentCount + $lateCount + $halfDayCount) / $total) * 100;
    }

    /**
     * Get performance insights for a salon
     */
    public function getSalonPerformanceInsights($salon, $month = null, $year = null)
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        $metrics = StaffPerformanceMetric::whereHas('staff', function ($query) use ($salon) {
            $query->where('salon_id', $salon->id);
        })
            ->where('month', $month)
            ->where('year', $year)
            ->with('staff')
            ->get();

        return [
            'salon' => $salon,
            'total_staff' => $metrics->count(),
            'total_revenue' => $metrics->sum('total_revenue'),
            'total_commission' => $metrics->sum('commission_earned'),
            'total_bookings' => $metrics->sum('total_bookings'),
            'completed_bookings' => $metrics->sum('completed_bookings'),
            'average_rating' => $metrics->avg('average_rating'),
            'top_performer' => $metrics->sortByDesc('performance_score')->first(),
            'metrics' => $metrics,
        ];
    }
}
