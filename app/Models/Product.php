<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'category_id','slug','name','description','price','unit','tone',
        'attributes','is_active','is_featured'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'attributes' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];


    public function inventoryRolls(): HasMany
    {
        return $this->hasMany(ProductInventoryRoll::class)->orderBy('width')->orderBy('length');
    }

    public function pricingRule(): HasOne
    {
        return $this->hasOne(ProductPricingRule::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order')->orderBy('id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
