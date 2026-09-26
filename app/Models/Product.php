<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = ['category_id','slug','name','description','price','unit','tone','is_active'];
    protected $casts = ['price'=>'decimal:2','is_active'=>'boolean'];
    public function pricingRule(): HasOne { return $this->hasOne(ProductPricingRule::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
}
