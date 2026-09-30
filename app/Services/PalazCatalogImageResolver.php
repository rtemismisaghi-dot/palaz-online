<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Support\Facades\Cache;
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

    public static function resolveStrictBatch(iterable $products): array
    {
        $productsByCode = [];
        foreach ($products as $product) {
            $code = trim((string) ($product->attributes['code'] ?? ''));
            if ($code !== '') $productsByCode[$code] = $product;
        }

        if (!$productsByCode) return [];

        $pages = Cache::remember('palaz:carpet-catalog-pages:v4', now()->addMinutes(30), function () {
            $responses = Http::pool(function ($pool) {
                $requests = [];
                for ($page = 1; $page <= 15; $page++) {
                    $url = 'https://palazonline.com/category/موکت' . ($page > 1 ? '?page=' . $page : '');
                    $requests[] = $pool->as('page' . $page)
                        ->timeout(4)->connectTimeout(2)
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 PalazOnlineCatalog/1.0',
                            'Accept' => 'text/html,application/xhtml+xml',
                        ])->get($url);
                }
                return $requests;
            }, concurrency: 8);

            $html = [];
            foreach ($responses as $response) {
                if ($response instanceof \Throwable) continue;
                if ($response->successful()) $html[] = $response->body();
            }
            return $html;
        });

        $urlByCode = [];
        foreach ($pages as $html) {
            foreach (array_keys($productsByCode) as $code) {
                if (isset($urlByCode[$code]) || stripos($html, $code) === false) continue;
                $urls = self::extractProductUrlsForCode($html, $code);
                if ($urls) $urlByCode[$code] = $urls[0];
            }
        }

        if (!$urlByCode) return [];

        $responses = Http::pool(function ($pool) use ($urlByCode) {
            $requests = [];
            foreach ($urlByCode as $code => $url) {
                $requests[$code] = $pool->as('code_' . $code)
                    ->timeout(5)->connectTimeout(2)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 PalazOnlineCatalog/1.0',
                        'Accept' => 'text/html,application/xhtml+xml',
                    ])->get($url);
            }
            return $requests;
        }, concurrency: 8);

        $found = [];
        foreach ($urlByCode as $code => $url) {
            $response = $responses[$code] ?? null;
            if (!$response || $response instanceof \Throwable || !$response->successful()) continue;

            $image = self::extractProductPageImage($url);
            if (!$image) continue;

            $product = $productsByCode[$code];
            ProductMedia::updateOrCreate(
                ['product_id' => $product->id, 'sort_order' => 0],
                [
                    'path' => $image,
                    'alt' => $product->name . ' - کد ' . $code,
                    'is_cover' => true,
                ]
            );
            $found[$code] = $image;
        }

        return $found;
    }

    public static function debugCode(string $code): array
    {
        $url = 'https://palazonline.com/category/موکت?page=10';

        try {
            $response = Http::timeout(5)->connectTimeout(2)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 PalazOnlineCatalog/1.0',
                'Accept' => 'text/html,application/xhtml+xml',
            ])->get($url);

            $body = $response->body();
            $position = stripos($body, $code);

            if ($position === false) {
                return ['code'=>$code,'status'=>$response->status(),'length'=>strlen($body),'found'=>false];
            }

            $start = max(0, $position - 10000);
            $block = substr($body, $start, 20000);
            preg_match_all("/https?:\\/\\/[^\"'\\s<>]+/iu", $block, $links);

            return [
                'code'=>$code,
                'status'=>$response->status(),
                'length'=>strlen($body),
                'found'=>true,
                'near_code_urls'=>array_values(array_unique($links[0] ?? [])),
                'snippet'=>substr($block, 0, 20000),
            ];
        } catch (\Throwable $e) {
            return ['code'=>$code,'error'=>$e->getMessage()];
        }
    }

    private static function searchCatalogPages(string $code): ?string
    {
        $pages = Cache::remember('palaz:carpet-catalog-pages:v3', now()->addMinutes(30), function () {
            $responses = Http::pool(function ($pool) {
                $requests = [];
                for ($page = 1; $page <= 15; $page++) {
                    $url = 'https://palazonline.com/category/موکت' . ($page > 1 ? '?page=' . $page : '');
                    $requests[] = $pool->as('page' . $page)
                        ->timeout(4)->connectTimeout(2)
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 PalazOnlineCatalog/1.0',
                            'Accept' => 'text/html,application/xhtml+xml',
                        ])->get($url);
                }
                return $requests;
            }, concurrency: 8);

            $html = [];
            foreach ($responses as $response) {
                if ($response instanceof \Throwable) continue;
                if ($response->successful()) $html[] = $response->body();
            }
            return $html;
        });

        $productUrl = null;
        foreach ($pages as $html) {
            if (stripos($html, $code) === false) continue;

            $urls = self::extractProductUrlsForCode($html, $code);
            if ($urls) {
                $productUrl = $urls[0];
                break;
            }

            $image = self::extractImageForCode($html, $code);
            if ($image) return $image;
        }

        if (!$productUrl) return null;

        try {
            $response = Http::timeout(5)->connectTimeout(2)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 PalazOnlineCatalog/1.0',
                'Accept' => 'text/html,application/xhtml+xml',
            ])->get($productUrl);

            if ($response->successful()) {
                return self::extractProductPageImageFromHtml($response->body());
            }
        } catch (\Throwable) {
            // Product page is only a fallback; never fail the whole import.
        }

        return null;
    }

    private static function extractProductPageImageFromHtml(string $html): ?string
    {
        $patterns = [
            '/<(?:meta|img|source)\\b[^>]*(?:content|src|data-src|data-lazy-src|data-original|data-image)\\s*=\\s*["\']([^"\']+)["\']/iu',
            "~https?://[^\"'\\s<>]+~iu",
        ];

        foreach ($patterns as $pattern) {
            if (!preg_match_all($pattern, $html, $matches)) continue;

            foreach (($matches[1] ?? $matches[0]) as $raw) {
                $url = self::normalizeUrl(html_entity_decode(trim($raw)));
                if ($url && self::isLikelyProductImage($url)) return $url;
            }
        }

        return null;
    }

    private static function extractProductUrlsForCode(string $html, string $code): array
    {
        $urls = [];

        if (!preg_match_all(
            "/<a\\b[^>]*href=[\"']([^\"']+)[\"'][^>]*>/iu",
            $html,
            $matches
        )) {
            return [];
        }

        foreach ($matches[1] as $rawUrl) {
            $url = self::normalizeUrl(html_entity_decode($rawUrl));
            if (!$url || !self::isLikelyProductPage($url)) continue;

            $path = rawurldecode((string) parse_url($url, PHP_URL_PATH));

            // Only accept a product URL whose slug explicitly ends with this code.
            if (!preg_match('/(?:^|[-_])(?:کد|code)[-_ ]?' . preg_quote($code, '/') . '(?:$|[-_])/iu', $path)) {
                continue;
            }

            $urls[$url] = true;
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
        $offset = 0;
        $candidates = [];

        while (($position = stripos($html, $code, $offset)) !== false) {
            $start = max(0, $position - 30000);
            $length = min(60000, strlen($html) - $start);
            $block = substr($html, $start, $length);

            if (preg_match_all("/https?:\\/\\/[^\"'\\s<>]+/iu", $block, $links)) {
                foreach ($links[0] as $raw) {
                    $url = self::normalizeUrl(html_entity_decode($raw));
                    if ($url && self::isLikelyProductImage($url)) {
                        $candidates[] = $url;
                    }
                }
            }

            if (preg_match_all("/(?:src|data-src|data-lazy-src|data-original|background-image|image|thumbnail|url)\\s*[=:()]\\s*[\"']?([^\"'\\)\\s]+)[\"']?/iu", $block, $embedded)) {
                foreach ($embedded[1] as $raw) {
                    $url = self::normalizeUrl(html_entity_decode(trim($raw)));
                    if ($url && self::isLikelyProductImage($url)) {
                        $candidates[] = $url;
                    }
                }
            }

            $offset = $position + strlen($code);
        }

        return array_values(array_unique($candidates))[0] ?? null;
    }

    private static function searchIndexedImage(string $name, string $code): ?string
    {
        return null;
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
