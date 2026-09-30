<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductInventoryRoll extends Model
{
    protected $fillable = ['product_id', 'width', 'length', 'quantity'];

    protected $casts = [
        'width' => 'integer',
        'length' => 'integer',
        'quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function area(): int
    {
        return $this->width * $this->length * $this->quantity;
    }
}
