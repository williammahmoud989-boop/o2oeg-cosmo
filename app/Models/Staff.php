<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'salon_id',
        'name',
        'specialization',
        'base_salary',
        'commission_rate',
        'is_active',
        'attendance_reminder_enabled',
        'attendance_time',
        'whatsapp_number',
        'privacy_consent',
        'consent_given_at',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'attendance_reminder_enabled' => 'boolean',
        'privacy_consent' => 'boolean',
        'consent_given_at' => 'datetime',
        'attendance_time' => 'datetime:H:i',
    ];

    protected $attributes = [
        'attendance_reminder_enabled' => false,
        'privacy_consent' => false,
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }
}
