<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffReview extends Model
{
    use HasFactory;
    protected $fillable = [
        'staff_id',
        'user_id',
        'booking_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get average rating for a staff member
     */
    public static function getAverageRating($staffId)
    {
        return self::where('staff_id', $staffId)->avg('rating') ?? 0;
    }

    /**
     * Get total reviews for a staff member
     */
    public static function getTotalReviews($staffId)
    {
        return self::where('staff_id', $staffId)->count();
    }
}
