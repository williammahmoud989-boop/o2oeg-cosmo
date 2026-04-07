<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'salon_id',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'category',
        'price',
        'discount_price',
        'duration_minutes',
        'image',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->discount_price ?? $this->price);
    }

    /**
     * Calculates the dynamic yield price for a specific date and time based on salon pricing rules.
     */
    public function getCalculatedYieldPrice(\Carbon\Carbon $date, string $time): array
    {
        $basePrice = $this->effective_price;
        $multiplier = 1.0;
        $dayOfWeek = $date->format('l');

        $pricingRule = $this->salon->pricingRules()
            ->where('is_active', true)
            ->where(function ($query) use ($dayOfWeek) {
                $query->whereNull('day_of_week')
                      ->orWhere('day_of_week', $dayOfWeek);
            })
            ->where(function ($query) use ($time) {
                $query->whereNull('start_time')
                      ->orWhere(function ($q) use ($time) {
                          $q->where('start_time', '<=', $time)
                            ->where('end_time', '>=', $time);
                      });
            })
            ->first();

        if ($pricingRule) {
            if ($pricingRule->type === 'surge') {
                $multiplier = 1 + ($pricingRule->percentage / 100);
            } else {
                $multiplier = 1 - ($pricingRule->percentage / 100);
            }
        }

        return [
            'original_price' => $basePrice,
            'final_price' => $basePrice * $multiplier,
            'multiplier' => $multiplier,
            'rule_applied' => $pricingRule ? $pricingRule->name : null,
        ];
    }
}
