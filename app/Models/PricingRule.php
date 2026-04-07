<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'salon_id',
        'name',
        'day_of_week',
        'start_time',
        'end_time',
        'type',
        'percentage',
        'is_active',
    ];

    /**
     * Get the salon that owns the pricing rule.
     */
    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }
}
