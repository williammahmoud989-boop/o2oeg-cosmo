<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'title_ar',
        'title',
        'description_ar',
        'description',
        'discount_percentage',
        'original_price',
        'discounted_price',
        'expires_at',
        'is_active',
        'salon_id',
        'service_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'date',
        'discount_percentage' => 'decimal:2',
        'original_price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
    ];

    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
