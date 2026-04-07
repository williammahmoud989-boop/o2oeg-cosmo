<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'salon_id',
        'code',
        'discount_percentage',
        'commission_percentage',
        'usage_limit',
        'usage_count',
        'total_commission',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the salon that issued the promo code.
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    /**
     * Get the bookings that used this promo code.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
