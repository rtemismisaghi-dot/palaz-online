<?php

namespace App\Support;

use App\Models\Product;

final class ProductCalculator
{
    public static function total(Product $product, float $quantity): float
    {
        $price=(float)($product->price ?? 0);
        $q=max(0,$quantity);
        $rule=$product->pricingRule;
        if (!$rule || !$rule->is_active || $price<=0) return 0.0;
        $base=match($rule->calculation_type) {
            'area','roll','quantity' => $q,
            default => 1,
        };
        $waste=1+((float)$rule->waste_percent/100);
        return round($base*$price*$waste,2);
    }
}