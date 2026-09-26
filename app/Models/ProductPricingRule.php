<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPricingRule extends Model
{
    protected $fillable = ['product_id','calculation_type','unit','waste_percent','parameters','is_active'];
    protected $casts = ['waste_percent'=>'decimal:2','parameters'=>'array','is_active'=>'boolean'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}