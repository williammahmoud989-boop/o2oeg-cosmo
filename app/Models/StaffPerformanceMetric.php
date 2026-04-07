<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPerformanceMetric extends Model
{
    use HasFactory;
    protected $fillable = [
        'staff_id',
        'month',
        'year',
        'performance_score',
        'average_rating',
        'completion_rate',
        'attendance_rate',
        'total_bookings',
        'completed_bookings',
        'total_revenue',
        'total_commission',
        'total_reviews',
        'present_days',
        'absent_days',
        'late_days',
    ];

    protected $casts = [
        'total_revenue' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'performance_score' => 'decimal:2',
        'completion_rate' => 'decimal:2',
        'attendance_rate' => 'decimal:2',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get completion rate
     */
    public function getCompletionRateAttribute()
    {
        if ($this->total_bookings == 0) {
            return 0;
        }
        return ($this->completed_bookings / $this->total_bookings) * 100;
    }

    /**
     * Get attendance rate
     */
    public function getAttendanceRateAttribute()
    {
        $totalWorkingDays = $this->attendance_days + $this->absent_days + $this->late_days + $this->half_days;
        
        if ($totalWorkingDays == 0) {
            return 0;
        }

        return (($this->attendance_days + ($this->late_days * 0.5) + ($this->half_days * 0.5)) / $totalWorkingDays) * 100;
    }

    /**
     * Get performance score (0-100)
     */
    public function getPerformanceScoreAttribute()
    {
        $ratingScore = ($this->average_rating / 5) * 40; // 40% weight
        $completionScore = $this->completion_rate * 0.4; // 40% weight
        $attendanceScore = $this->attendance_rate * 0.2; // 20% weight

        return round($ratingScore + $completionScore + $attendanceScore, 2);
    }
}
