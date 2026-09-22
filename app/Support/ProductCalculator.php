<?php

namespace App\Support;

use App\Models\Product;
use InvalidArgumentException;

final class ProductCalculator
{
    public static function quote(Product $product, array $input): array
    {
        $price = (float) ($product->price ?? 0);
        $rule = $product->pricingRule;

        if (!$rule || !$rule->is_active) {
            return [
                'quantity' => 0,
                'billable_quantity' => 0,
                'unit_price' => $price,
                'total' => 0.0,
                'waste_percent' => 0.0,
                'calculation_type' => 'fixed',
                'unit' => $product->unit,
            ];
        }

        if ($price < 0) {
            throw new InvalidArgumentException('قیمت محصول نمی‌تواند منفی باشد.');
        }

        $waste = max(0, (float) $rule->waste_percent);

        if ($rule->calculation_type === 'roll') {
            $length = (float) ($input['length'] ?? 0);
            $rollCount = (float) ($input['quantity'] ?? 0);

            if ($length < 1 || $length > 15) {
                throw new InvalidArgumentException('طول طاقه ۳ متری باید بین ۱ تا ۱۵ متر باشد.');
            }

            if ($rollCount < 1) {
                throw new InvalidArgumentException('تعداد طاقه باید حداقل ۱ باشد.');
            }

            // فعلاً فقط طاقه عرض ۳ متر در فروش فعال است.
            $width = 3.0;
            $areaPerRoll = $width * $length;
            $billableArea = $areaPerRoll * $rollCount;
            $billableArea *= 1 + ($waste / 100);

            return [
                'quantity' => $rollCount,
                'billable_quantity' => round($billableArea, 2),
                'unit_price' => $price,
                'total' => round($billableArea * $price, 2),
                'waste_percent' => $waste,
                'calculation_type' => 'roll',
                'unit' => $rule->unit,
                'width' => $width,
                'length' => $length,
                'area_per_roll' => round($areaPerRoll, 2),
                'roll_count' => $rollCount,
            ];
        }

        $q = max(0, (float) ($input['quantity'] ?? 0));
        $billable = $q * (1 + $waste / 100);
        $billable = $rule->calculation_type === 'fixed' ? 1 : $billable;

        return [
            'quantity' => $q,
            'billable_quantity' => round($billable, 2),
            'unit_price' => $price,
            'total' => round($billable * $price, 2),
            'waste_percent' => $waste,
            'calculation_type' => $rule->calculation_type,
            'unit' => $rule->unit,
        ];
    }

    public static function total(Product $product, float $quantity): float
    {
        return self::quote($product, ['quantity' => $quantity])['total'];
    }
}
