<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallationQuote extends Model
{
    protected $fillable = [
        'order_id',
        'tracking_code',
        'payload',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
        'total_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
