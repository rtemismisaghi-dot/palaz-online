<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Support\Facades\Http;

final class PalazCatalogImageResolver
{
    public static function resolve(Product $product): ?string
    {
        $code = (string) ($product->attributes['code'] ?? '');
        if ($code === '') {
            return null;
        }

        $name = trim((string) $product->name);
        $slug = rawurlencode(str_replace(' ', '-', $name . ' کد ' . $code));
        $url = 'https://palazonline.com/product/' . $slug;

        try {
            $response = Http::timeout(12)
                ->connectTimeout(5)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 PalazOnlineCatalog/1.0'])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $html = $response->body();
            $image = null;

            if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/iu', $html, $m)) {
                $image = html_entity_decode($m[1]);
            } elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/iu', $html, $m)) {
                $image = html_entity_decode($m[1]);
            }

            if (! $image) {
                return null;
            }

            ProductMedia::updateOrCreate(
                ['product_id' => $product->id, 'sort_order' => 0],
                [
                    'path' => $image,
                    'alt' => $name . ' - کد ' . $code,
                    'is_cover' => true,
                ]
            );

            return $image;
        } catch (\Throwable) {
            return null;
        }
    }
}
