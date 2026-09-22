<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['tracking_code','name','phone','city','postal_code','address','service','payment_status','status','subtotal','total'];
    protected $casts = ['subtotal'=>'decimal:2','total'=>'decimal:2'];
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
}
