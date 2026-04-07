<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Booking;
use App\Models\PricingRule;
use App\Models\PromoCode;
use App\Models\Review;

class Salon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'phone',
        'email',
        'address',
        'city',
        'governorate',
        'latitude',
        'longitude',
        'logo',
        'cover_image',
        'working_hours',
        'status',
        'daily_capacity',
        'occupancy_threshold_high',
        'peak_surcharge_percentage',
        'is_featured',
        'rating',
        'total_reviews',
        'requires_deposit',
        'deposit_percentage',
        'vodafone_cash_number',
        'instapay_id',
        'deposit_days',
        'whatsapp_number',
        'instagram_url',
        'facebook_url',
        'tiktok_url',
        'gallery',
        'slug',
        'subdomain',
        'custom_domain',
        'website',
        'address_ar',
        'payment_methods',
        'commission_rate',
        'paymob_api_key',
        'paymob_hmac_secret',
        'paymob_card_integration_id',
        'paymob_iframe_id',
    ];

    protected $casts = [
        'working_hours' => 'array',
        'deposit_days' => 'array',
        'gallery' => 'array',
        'is_featured' => 'boolean',
        'requires_deposit' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'decimal:2',
        'payment_methods' => 'array',
        'commission_rate' => 'decimal:2',
        'paymob_api_key' => 'encrypted',
        'paymob_hmac_secret' => 'encrypted',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the pricing rules for the salon.
     */
    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    /**
     * Get the promo codes for the salon.
     */
    public function promoCodes(): HasMany
    {
        return $this->hasMany(PromoCode::class);
    }

    /**
     * Get the reviews for the salon.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
