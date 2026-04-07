<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'salon_id',
        'staff_id',
        'month',
        'year',
        'base_salary',
        'total_commission',
        'advances',
        'deductions',
        'net_salary',
        'status',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'advances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
