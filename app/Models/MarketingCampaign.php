<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'salon_id',
        'title',
        'message',
        'status',
        'scheduled_at',
        'total_recipients',
        'sent_count',
        'error_log',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }
}
