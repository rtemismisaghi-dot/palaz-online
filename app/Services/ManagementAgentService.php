<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Carbon;

class ManagementAgentService
{
    public function snapshot(?string $from = null, ?string $to = null): array
    {
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : now()->subDays(29)->startOfDay();
        $toDate = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();

        $products = Product::with(['inventoryRolls', 'category'])->get();
        $orders = Order::whereBetween('created_at', [$fromDate, $toDate])->with('items')->get();

        $items = $orders->flatMap->items;
        $salesItems = $items->filter(fn ($item) => !in_array($item->order?->status, ['cancelled', 'canceled'], true));

        $topProducts = $salesItems
            ->groupBy('product_id')
            ->map(function ($rows) {
                $first = $rows->first();
                return [
                    'product_id' => $first->product_id,
                    'name' => $first->product_name,
                    'quantity' => $rows->sum('quantity'),
                    'revenue' => $rows->sum('line_total'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values();

        $inventory = $products->map(function ($product) {
            $rolls = $product->inventoryRolls;
            $rollCount = $rolls->sum('quantity');
            $area = $rolls->sum(fn ($roll) => $roll->area());
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category?->name ?? '—',
                'price' => (float) $product->price,
                'roll_count' => $rollCount,
                'area' => $area,
                'is_roll' => $rolls->isNotEmpty(),
                'low' => $rolls->isNotEmpty() && $rollCount <= 2,
            ];
        });

        return [
            'from' => $fromDate->toDateString(),
            'to' => $toDate->toDateString(),
            'orders' => $orders->count(),
            'sales_quantity' => $salesItems->sum('quantity'),
            'sales_revenue' => $salesItems->sum('line_total'),
            'active_products' => $products->where('is_active', true)->count(),
            'inventory_rolls' => $inventory->sum('roll_count'),
            'inventory_area' => $inventory->sum('area'),
            'low_stock' => $inventory->where('low', true)->values(),
            'top_products' => $topProducts,
            'inventory' => $inventory->sortByDesc('roll_count')->values(),
        ];
    }
}
