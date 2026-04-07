<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'salon_id',
        'user_id',
        'package_id',
        'total_price',
        'payment_status',
        'purchase_date',
        'expiry_date',
        'is_active',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'purchase_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class)->withTrashed();
    }

    public function usages(): HasMany
    {
        return $this->hasMany(UserPackageUsage::class);
    }
}
