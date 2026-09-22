<?php

namespace App\Support;

use App\Models\Product;
use InvalidArgumentException;

final class ProductCalculator
{
    public static function quote(Product $product, array $input): array
    {
        $price=(float)($product->price ?? 0);
        $rule=$product->pricingRule;
        if (!$rule || !$rule->is_active) return ['quantity'=>0,'billable_quantity'=>0,'unit_price'=>$price,'total'=>0.0,'waste_percent'=>0.0,'calculation_type'=>'fixed'];
        if ($price < 0) throw new InvalidArgumentException('قیمت محصول نمی‌تواند منفی باشد.');
        $q=max(0,(float)($input['quantity'] ?? 0));
        $waste=max(0,(float)$rule->waste_percent);
        $billable=$q*(1+$waste/100);
        $billable=$rule->calculation_type==='fixed' ? 1 : $billable;
        return ['quantity'=>$q,'billable_quantity'=>round($billable,2),'unit_price'=>$price,'total'=>round($billable*$price,2),'waste_percent'=>$waste,'calculation_type'=>$rule->calculation_type,'unit'=>$rule->unit];
    }

    public static function total(Product $product, float $quantity): float
    {
        return self::quote($product,['quantity'=>$quantity])['total'];
    }
}