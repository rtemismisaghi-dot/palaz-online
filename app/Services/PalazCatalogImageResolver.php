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
        if ($code === '') return null;

        $name = trim((string) $product->name);

        try {
            $image = self::searchCatalogPages($code);

            if (!$image) {
                $image = self::searchIndexedImage($name, $code);
            }

            if (!$image) return null;

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

    private static function searchCatalogPages(string $code): ?string
    {
        $responses = Http::pool(function ($pool) {
            $requests = [];

            for ($page = 1; $page <= 15; $page++) {
                $url = 'https://palazonline.com/category/موکت' . ($page > 1 ? '?page=' . $page : '');
                $requests[] = $pool->as('page'.$page)
                    ->timeout(2)
                    ->connectTimeout(1)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 PalazOnlineCatalog/1.0'])
                    ->get($url);
            }

            return $requests;
        });

        foreach ($responses as $response) {
            if ($response->successful()) {
                $image = self::extractImageForCode($response->body(), $code);
                if ($image) return $image;
            }
        }

        return null;
    }

    private static function extractImageForCode(string $html, string $code): ?string
    {
        $escapedCode = preg_quote($code, '/');

        if (!preg_match('/.{0,2200}' . $escapedCode . '.{0,2200}/isu', $html, $match)) {
            return null;
        }

        $block = $match[0];

        if (preg_match_all(
            '/(?:src|data-src|data-lazy-src|data-original|href)\s*=\s*["\']([^"\']+)["\']/iu',
            $block,
            $images
        )) {
            foreach ($images[1] as $image) {
                $url = self::normalizeUrl(html_entity_decode(trim($image)));
                if ($url && self::isImageUrl($url)) return $url;
            }
        }

        if (preg_match(
            '/https?:\/\/[^"\'\s<>]+\.(?:jpg|jpeg|png|webp)(?:\?[^"\'\s<>]*)?/iu',
            $block,
            $m
        )) {
            return html_entity_decode($m[0]);
        }

        return null;
    }

    private static function searchIndexedImage(string $name, string $code): ?string
    {
        try {
            $query = rawurlencode('site:palazonline.com ' . $code . ' ' . $name);
            $response = Http::timeout(2)->connectTimeout(1)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get('https://www.bing.com/images/search?q=' . $query);

            if (!$response->successful()) return null;

            if (preg_match_all('/"murl":"(https?:\\/\\/[^"]+)"/i', $response->body(), $matches)) {
                foreach ($matches[1] as $raw) {
                    $image = json_decode('"' . $raw . '"');
                    if (!is_string($image)) $image = str_replace('\\/', '/', $raw);

                    $host = parse_url($image, PHP_URL_HOST);
                    if ($host && preg_match('/(^|\\.)palazonline\\.com$/i', $host)) {
                        return $image;
                    }
                }
            }
        } catch (\Throwable) {}

        return null;
    }

    private static function normalizeUrl(string $url): ?string
    {
        if ($url === '') return null;
        if (str_starts_with($url, '//')) return 'https:' . $url;
        if (str_starts_with($url, '/')) return 'https://palazonline.com' . $url;
        if (preg_match('/^https?:\/\//i', $url)) return $url;
        return null;
    }

    private static function isImageUrl(string $url): bool
    {
        return (bool) preg_match('/\.(?:jpg|jpeg|png|webp)(?:\?|$)/iu', $url);
    }
}
