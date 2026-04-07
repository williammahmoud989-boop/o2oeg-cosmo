<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $referral_code
 * @property float $commission_rate
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Influencer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'referral_code',
        'commission_rate',
        'phone',
        'email',
        'instagram_handle',
        'payment_info',
        'is_active',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the bookings referred by this influencer.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Calculate total earnings for the influencer.
     */
    public function getTotalEarningsAttribute()
    {
        return $this->bookings()
            ->where('payment_status', 'paid')
            ->sum('influencer_commission');
    }
}
