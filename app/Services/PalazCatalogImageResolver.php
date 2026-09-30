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

                $requests[] = $pool->as('page' . $page)
                    ->timeout(3)
                    ->connectTimeout(1)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 PalazOnlineCatalog/1.0',
                        'Accept' => 'text/html,application/xhtml+xml',
                    ])
                    ->get($url);
            }

            return $requests;
        });

        foreach ($responses as $response) {
            if (!$response->successful()) {
                continue;
            }

            $productUrls = self::extractProductUrlsForCode($response->body(), $code);

            foreach ($productUrls as $productUrl) {
                $image = self::extractProductPageImage($productUrl);
                if ($image) {
                    return $image;
                }
            }

            $image = self::extractImageForCode($response->body(), $code);
            if ($image) {
                return $image;
            }
        }

        return null;
    }

    private static function extractProductUrlsForCode(string $html, string $code): array
    {
        $offset = 0;
        $urls = [];

        while (($position = stripos($html, $code, $offset)) !== false) {
            $start = max(0, $position - 12000);
            $length = min(24000, strlen($html) - $start);
            $window = substr($html, $start, $length);

            if (preg_match_all(
                "/<a\\b[^>]*href=[\"']([^\"']+)[\"'][^>]*>/iu",
                $window,
                $matches
            )) {
                foreach ($matches[1] as $rawUrl) {
                    $url = self::normalizeUrl(html_entity_decode($rawUrl));
                    if ($url && self::isLikelyProductPage($url)) {
                        $urls[$url] = true;
                    }
                }
            }

            $offset = $position + strlen($code);
        }

        return array_keys($urls);
    }

    private static function extractProductPageImage(string $productUrl): ?string
    {
        try {
            $response = Http::timeout(3)
                ->connectTimeout(1)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 PalazOnlineCatalog/1.0',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->get($productUrl);

            if (!$response->successful()) {
                return null;
            }

            $html = $response->body();
            $candidates = [];

            // Product pages expose the real source image through the product gallery
            // and often also through og:image.
            if (preg_match_all(
                '/<meta\\b[^>]*(?:property|name)=["\\\']og:image["\\\'][^>]*content=["\\\']([^"\\\']+)["\\\'][^>]*>/iu',
                $html,
                $meta
            )) {
                foreach ($meta[1] as $raw) {
                    $url = self::normalizeUrl(html_entity_decode($raw));
                    if ($url && self::isLikelyProductImage($url)) {
                        $candidates[] = $url;
                    }
                }
            }

            if (preg_match_all(
                '/<(?:img|source)\\b[^>]*(?:src|srcset|data-src|data-srcset|data-lazy-src|data-original)\\s*=\\s*["\\\']([^"\\\']+)["\\\'][^>]*>/iu',
                $html,
                $images
            )) {
                foreach ($images[1] as $raw) {
                    foreach (preg_split('/\\s*,\\s*/', html_entity_decode($raw)) as $part) {
                        $url = trim((string) preg_replace('/\\s+\\d+[wx](?=\\s|$)/i', '', $part));
                        $url = self::normalizeUrl($url);

                        if ($url && self::isLikelyProductImage($url)) {
                            $candidates[] = $url;
                        }
                    }
                }
            }

            // Ignore site chrome and keep storage/products assets first.
            foreach ($candidates as $url) {
                $path = strtolower((string) parse_url($url, PHP_URL_PATH));
                if (str_contains($path, '/storage/products/')) {
                    return $url;
                }
            }

            return $candidates[0] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function extractImageForCode(string $html, string $code): ?string
    {
        $escapedCode = preg_quote($code, '/');

        if (!preg_match('/.{0,8000}' . $escapedCode . '.{0,8000}/isu', $html, $match)) {
            return null;
        }

        $block = $match[0];
        $candidates = [];

        if (preg_match_all(
            '/<(?:img|source)\\b[^>]*(?:src|srcset|data-src|data-srcset|data-lazy-src|data-original)\\s*=\\s*["\\\']([^"\\\']+)["\\\'][^>]*>/iu',
            $block,
            $images
        )) {
            foreach ($images[1] as $raw) {
                foreach (preg_split('/\\s*,\\s*/', html_entity_decode($raw)) as $part) {
                    $url = trim((string) preg_replace('/\\s+\\d+[wx](?=\\s|$)/i', '', $part));
                    $url = self::normalizeUrl($url);

                    if ($url && self::isLikelyProductImage($url)) {
                        $candidates[] = $url;
                    }
                }
            }
        }

        if (preg_match_all(
            '/(?:image|thumbnail|src|url|medium|large)["\']?\\s*[:=]\\s*["\']([^"\']+)["\']/iu',
            $block,
            $embedded
        )) {
            foreach ($embedded[1] as $raw) {
                $url = self::normalizeUrl(html_entity_decode(trim($raw)));
                if ($url && self::isLikelyProductImage($url)) {
                    $candidates[] = $url;
                }
            }
        }

        return $candidates[0] ?? null;
    }

    private static function searchIndexedImage(string $name, string $code): ?string
    {
        try {
            $query = rawurlencode('site:palazonline.com ' . $code . ' ' . $name);
            $response = Http::timeout(3)->connectTimeout(1)
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

    private static function isLikelyProductPage(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        if (!$host || !preg_match('/(^|\.)palazonline\.com$/i', $host)) {
            return false;
        }

        return str_contains($path, '/product/')
            || str_contains($path, '/products/')
            || str_contains($path, '/موکت-');
    }

    private static function normalizeUrl(string $url): ?string
    {
        $url = trim(html_entity_decode($url));

        if ($url === '' || str_starts_with($url, 'data:')) return null;

        $url = str_replace(['\\/', '\\u002F'], '/', $url);

        if (str_starts_with($url, '//')) return 'https:' . $url;
        if (str_starts_with($url, '/')) return 'https://palazonline.com' . $url;
        if (preg_match('/^https?:\\/\\//i', $url)) return $url;

        return null;
    }

    private static function isLikelyProductImage(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (!$host || !preg_match('/(^|\\.)palazonline\\.com$/i', $host)) {
            return false;
        }

        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        if (preg_match('/(?:logo|icon|avatar|favicon|banner|loading|placeholder)/i', $path)) {
            return false;
        }

        return str_contains($path, '/wp-content/uploads/')
            || str_contains($path, '/storage/products/')
            || preg_match('/\\.(?:jpg|jpeg|png|webp|avif)$/i', $path)
            || str_contains($path, '/uploads/');
    }
}
