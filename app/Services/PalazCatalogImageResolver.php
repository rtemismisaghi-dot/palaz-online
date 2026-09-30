<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Support\Facades\Http;

final class PalazCatalogImageResolver
{
    public static function resolve(Product $product): ?string
    {
        $code = trim((string) ($product->attributes['code'] ?? ''));
        if ($code === '') {
            return null;
        }

        $name = trim((string) $product->name);
        $pathName = preg_match('/(?:^|\s)کد\s*' . preg_quote($code, '/') . '\b/u', $name)
            ? $name
            : $name . ' کد ' . $code;

        $url = 'https://palazonline.com/product/' . rawurlencode(str_replace(' ', '-', $pathName));

        try {
            $response = Http::timeout(8)
                ->connectTimeout(4)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 PalazOnlineCatalog/1.0'])
                ->get($url);

            $image = $response->successful() ? self::extractImage($response->body()) : null;

            if (! $image) {
                $image = self::searchIndexedImage($name, $code);
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

    private static function searchIndexedImage(string $name, string $code): ?string
    {
        try {
            $query = rawurlencode('site:palazonline.com/product/ ' . $code . ' ' . $name);
            $response = Http::timeout(8)
                ->connectTimeout(4)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get('https://www.bing.com/images/search?q=' . $query);

            if (! $response->successful()) {
                return null;
            }

            $html = $response->body();

            if (preg_match_all('/"murl":"(https?:\\/\\/[^"]+)"/i', $html, $matches)) {
                foreach ($matches[1] as $raw) {
                    $image = json_decode('"' . $raw . '"');
                    if (! is_string($image)) {
                        $image = str_replace('\\/', '/', $raw);
                    }

                    $host = parse_url($image, PHP_URL_HOST);
                    if ($host && preg_match('/(^|\.)palazonline\.com$/i', $host)) {
                        return $image;
                    }
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private static function extractImage(string $html): ?string
    {
        $patterns = [
            '~<meta[^>]+property=["']og:image["'][^>]+content=["']([^"']+)["']~iu',
            '~<meta[^>]+content=["']([^"']+)["'][^>]+property=["']og:image["']~iu',
            '~<meta[^>]+name=["']twitter:image["'][^>]+content=["']([^"']+)["']~iu',
            '~<meta[^>]+content=["']([^"']+)["'][^>]+name=["']twitter:image["']~iu',
            '~(?:href|src|data-src|data-lazy-src)=["']([^"']*?/wp-content/uploads/[^"']+)["']~iu',
            '~(?:href|src|data-src|data-lazy-src)=["']([^"']*?/storage/uploads/[^"']+)["']~iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $image = html_entity_decode(trim($m[1]));

                if (str_starts_with($image, '//')) {
                    return 'https:' . $image;
                }

                if (str_starts_with($image, '/')) {
                    return 'https://palazonline.com' . $image;
                }

                if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
                    return $image;
                }
            }
        }

        return null;
    }
}
