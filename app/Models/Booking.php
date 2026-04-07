<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Events\BookingSaved;

/**
 * @property int $id
 * @property string $booking_code
 * @property int $user_id
 * @property int $salon_id
 * @property int $service_id
 * @property float $total_price
 * @property int|null $influencer_id
 * @property float|null $influencer_commission
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_code',
        'user_id',
        'salon_id',
        'service_id',
        'booking_date',
        'start_time',
        'end_time',
        'total_price',
        'deposit_amount',
        'payment_receipt',
        'status',
        'payment_status',
        'payment_id',
        'payment_method',
        'notes',
        'cancellation_reason',
        'confirmed_at',
        'cancelled_at',
        'coupon_id',
        'discount_amount',
        'points_earned',
        'points_redeemed',
        'promo_code_id',
        'original_price',
        'price_multiplier',
        'staff_id',
        'commission_amount',
        'reminder_sent',
        'influencer_id',
        'influencer_commission',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'total_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'influencer_commission' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    protected $dispatchesEvents = [
        'saved' => BookingSaved::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = 'BK-' . strtoupper(Str::random(8));
            }
        });

        static::saving(function (Booking $booking) {
            if ($booking->staff_id && $booking->isDirty(['staff_id', 'total_price'])) {
                $staff = Staff::find($booking->staff_id);
                if ($staff) {
                    $booking->commission_amount = ($booking->total_price * $staff->commission_rate) / 100;
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get the promo code used for this booking.
     */
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    /**
     * Get the influencer who referred this booking.
     */
    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class);
    }
}
