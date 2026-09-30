<?php

namespace App\\Services;

use App\\Models\\Product;
use App\\Models\\ProductMedia;
use Illuminate\\Support\\Facades\\Http;

final class PalazCatalogImageResolver
{
    public static function resolve(Product $product): ?string
    {
        $code = trim((string) ($product->attributes['code'] ?? ''));
        if ($code === '') {
            return null;
        }

        $name = trim((string) $product->name);
        $slug = rawurlencode(str_replace(' ', '-', $name . ' کد ' . $code));
        $url = 'https://palazonline.com/product/' . $slug;

        try {
            $response = Http::timeout(5)
                ->connectTimeout(3)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 PalazOnlineCatalog/1.0'])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $html = $response->body();
            $image = self::extractImage($html);

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
        } catch (\\Throwable) {
            return null;
        }
    }

    private static function extractImage(string $html): ?string
    {
        $patterns = [
            '/<meta[^>]+property=["\\\']og:image["\\\'][^>]+content=["\\\']([^"\\\']+)["\\\']/iu',
            '/<meta[^>]+content=["\\\']([^"\\\']+)["\\\'][^>]+property=["\\\']og:image["\\\']/iu',
            '/<meta[^>]+name=["\\\']twitter:image["\\\'][^>]+content=["\\\']([^"\\\']+)["\\\']/iu',
            '/<meta[^>]+content=["\\\']([^"\\\']+)["\\\'][^>]+name=["\\\']twitter:image["\\\']/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                return html_entity_decode(trim($m[1]));
            }
        }

        return null;
    }
}
