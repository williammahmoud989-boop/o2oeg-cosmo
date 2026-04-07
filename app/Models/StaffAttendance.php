<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'staff_id',
        'date',
        'check_in_time',
        'check_out_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Get attendance stats for a month
     */
    public static function getMonthlyStats($staffId, $month, $year)
    {
        return self::where('staff_id', $staffId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->selectRaw('
                COUNT(CASE WHEN status = "present" THEN 1 END) as present,
                COUNT(CASE WHEN status = "absent" THEN 1 END) as absent,
                COUNT(CASE WHEN status = "late" THEN 1 END) as late,
                COUNT(CASE WHEN status = "half_day" THEN 1 END) as half_day,
                COUNT(CASE WHEN status = "on_leave" THEN 1 END) as on_leave
            ')
            ->first();
    }

    /**
     * Check in staff member
     */
    public static function checkIn($staffId, $date = null)
    {
        $date = $date ? \Carbon\Carbon::parse($date)->toDateString() : \Carbon\Carbon::now()->toDateString();

        return self::updateOrCreate(
            ['staff_id' => $staffId, 'date' => $date],
            [
                'check_in_time' => \Carbon\Carbon::now()->toTimeString(),
                'status' => 'present',
            ]
        );
    }

    /**
     * Check out staff member
     */
    public static function checkOut($staffId, $date = null)
    {
        $date = $date ? \Carbon\Carbon::parse($date)->toDateString() : \Carbon\Carbon::now()->toDateString();

        return self::where('staff_id', $staffId)
            ->where('date', $date)
            ->update(['check_out_time' => \Carbon\Carbon::now()->toTimeString()]);
    }
}
