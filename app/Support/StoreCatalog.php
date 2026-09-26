<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;

final class StoreCatalog
{
    public static function categories(): array
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (Category $category) => [
                $category->slug => [
                    'title' => $category->name,
                    'eyebrow' => $category->eyebrow,
                    'tone' => $category->tone,
                ],
            ])
            ->all();
    }

    public static function products(): array
    {
        return self::mapProducts(
            Product::query()
                ->with('category')
                ->where('is_active', true)
                ->latest('id')
                ->get()
        );
    }

    public static function find(string $id): ?array
    {
        $product = Product::query()
            ->with('category')
            ->where('slug', $id)
            ->where('is_active', true)
            ->first();

        return $product ? self::mapProduct($product) : null;
    }

    public static function byCategory(?string $category): array
    {
        $query = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($category, fn ($q) => $q->whereHas('category', fn ($cq) => $cq->where('slug', $category)))
            ->latest('id');

        return self::mapProducts($query->get());
    }

    public static function search(?string $query): array
    {
        $query = trim((string) $query);

        return self::mapProducts(
            Product::query()
                ->with('category')
                ->where('is_active', true)
                ->when($query !== '', function ($q) use ($query) {
                    $q->where(function ($search) use ($query) {
                        $search->where('name', 'like', '%' . $query . '%')
                            ->orWhere('description', 'like', '%' . $query . '%');
                    });
                })
                ->latest('id')
                ->get()
        );
    }

    private static function mapProducts($products): array
    {
        return $products->map(fn (Product $product) => self::mapProduct($product))->all();
    }

    private static function mapProduct(Product $product): array
    {
        return [
            'id' => $product->slug,
            'category' => $product->category?->slug,
            'name' => $product->name,
            'price' => $product->price,
            'unit' => $product->unit,
            'tone' => $product->tone,
            'description' => $product->description,
        ];
    }
}
