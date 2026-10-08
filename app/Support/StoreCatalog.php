<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;

final class StoreCatalog
{
    public static function categories(): array
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (Category $category) => [
                $category->slug => [
                    'title' => $category->slug === 'spc' ? 'فرش‌گونه' : $category->name,
                    'eyebrow' => $category->slug === 'spc' ? 'مجموعه فرش‌گونه پالاز' : $category->eyebrow,
                    'tone' => $category->slug === 'spc' ? 'فرش‌گونه' : $category->tone,
                ],
            ])
            ->all();

        if (! isset($categories['wallpaper'])) {
            $categories['wallpaper'] = [
                'title' => 'کاغذ دیواری',
                'eyebrow' => 'طرح‌ها و رنگ‌های متنوع',
                'tone' => 'کاغذ دیواری',
            ];
        }

        return $categories;
    }

    private static function productQuery()
    {
        $relations = ['category', 'pricingRule'];

        if (Schema::hasTable('product_media')) {
            $relations[] = 'media';
        }

        return Product::query()->with($relations);
    }

    public static function products(): array
    {
        return self::mapProducts(
            self::productQuery()
                ->where('is_active', true)
                ->latest('id')
                ->get()
        );
    }

    public static function find(string $id): ?array
    {
        $product = self::productQuery()
            ->where('slug', $id)
            ->where('is_active', true)
            ->first();

        return $product ? self::mapProduct($product) : null;
    }

    public static function modelProducts(string $album): array
    {
        return self::mapProducts(
            self::productQuery()
                ->where('is_active', true)
                ->where('attributes->album', $album)
                ->orderBy('id')
                ->get()
        );
    }

    public static function findModelVariant(string $album, ?string $code = null): ?array
    {
        $query = self::productQuery()
            ->where('is_active', true)
            ->where('attributes->album', $album)
            ->orderBy('id');

        if ($code !== null && $code !== '') {
            $query->where('attributes->code', $code);
        }

        $product = $query->first();

        return $product ? self::mapProduct($product) : null;
    }

    public static function byCategory(?string $category): array
    {
        $query = self::productQuery()
            ->where('is_active', true)
            ->when($category, function ($q) use ($category) {
                if ($category === 'carpet_tile') {
                    $q->whereHas('category', fn ($cq) => $cq->whereIn('slug', ['carpet_tile', 'carpet-tile', 'tile-carpet']));
                    return;
                }

                $q->whereHas('category', fn ($cq) => $cq->where('slug', $category));
            })
            ->latest('id');

        return self::mapProducts($query->get());
    }

    public static function search(?string $query): array
    {
        $query = trim((string) $query);

        return self::mapProducts(
            self::productQuery()
                ->where('is_active', true)
                ->when($query !== '', function ($q) use ($query) {
                    $q->where(function ($search) use ($query) {
                        $search->where('name', 'like', '%' . $query . '%')
                            ->orWhere('description', 'like', '%' . $query . '%')
                            ->orWhere('attributes->code', 'like', '%' . $query . '%')
                            ->orWhere('attributes->album', 'like', '%' . $query . '%');
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
            'image' => self::productImageUrl($product),
            'attributes' => $product->attributes ?? [],
            'calculation_type' => $product->pricingRule?->calculation_type,
            'model' => $product->attributes['album'] ?? null,
            'code' => $product->attributes['code'] ?? null,
        ];
    }
    private static function productImageUrl(Product $product): ?string
    {
        if (! Schema::hasTable('product_media')) {
            return null;
        }

        $media = $product->media->first();
        if (! $media || ! $media->path) {
            return null;
        }

        $path = (string) $media->path;
        if (preg_match('/^https?:\\/\\//i', $path)) {
            return $path;
        }

        return route('media.public', ['path' => $path]);
    }
}
