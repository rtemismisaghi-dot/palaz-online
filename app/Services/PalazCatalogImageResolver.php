<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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

            $storedPath = self::downloadAndStore($product, $image, $code);
            if (!$storedPath) return null;

            return $storedPath;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function migrateRemoteMediaBatch(int $limit = 200): array
    {
        $media = ProductMedia::query()
            ->where('path', 'like', 'http%')
            ->with('product')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $migrated = 0;
        $failed = 0;

        foreach ($media as $item) {
            $sourceUrl = $item->path;
            $product = $item->product;

            if (!$product) {
                $failed++;
                continue;
            }

            $code = trim((string) ($product->attributes['code'] ?? $product->slug));
            $storedPath = self::downloadAndStore($product, $sourceUrl, $code);

            if ($storedPath) {
                $migrated++;
            } else {
                $failed++;
            }
        }

        return [
            'processed_now' => $media->count(),
            'migrated_now' => $migrated,
            'failed_now' => $failed,
            'remaining' => ProductMedia::query()->where('path', 'like', 'http%')->count(),
        ];
    }

    public static function resolveStrictBatch(iterable $products): array
    {
        $productsByCode = [];
        foreach ($products as $product) {
            $code = trim((string) ($product->attributes['code'] ?? ''));
            if ($code !== '') $productsByCode[$code] = $product;
        }

        if (!$productsByCode) return [];

        $pages = Cache::remember('palaz:carpet-catalog-pages:v7', now()->addMinutes(30), function () {
            $responses = Http::pool(function ($pool) {
                $requests = [];
                for ($page = 1; $page <= 15; $page++) {
                    $url = 'https://palazonline.com/category/موکت' . ($page > 1 ? '?page=' . $page : '');
                    $requests[] = $pool->as('page' . $page)
                        ->timeout(8)->connectTimeout(3)
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 PalazOnlineCatalog/1.0',
                            'Accept' => 'text/html,application/xhtml+xml',
                        ])->get($url);
                }
                return $requests;
            }, concurrency: 6);

            $html = [];
            foreach ($responses as $response) {
                if ($response instanceof \Throwable) continue;
                if ($response->successful()) $html[] = $response->body();
            }
            return $html;
        });

        $found = [];

        foreach ($pages as $html) {
            if (!$html || !class_exists(\DOMDocument::class)) continue;

            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
            libxml_clear_errors();

            $xpath = new \DOMXPath($dom);
            $anchors = $xpath->query('//a[@href]');

            foreach ($anchors as $anchor) {
                $href = self::normalizeUrl(html_entity_decode((string) $anchor->getAttribute('href')));
                if (!$href) continue;

                $host = parse_url($href, PHP_URL_HOST);
                $path = rawurldecode((string) parse_url($href, PHP_URL_PATH));
                if (!$host || !preg_match('/(^|\.)palazonline\.com$/i', $host) || !preg_match('#^/product/#i', $path)) {
                    continue;
                }

                $node = $anchor;
                $matchedCode = null;

                // Product cards vary in markup. Check the anchor and a few parent
                // containers, but never mix content from neighboring cards.
                for ($level = 0; $level <= 5 && $node; $level++, $node = $node->parentNode) {
                    $text = preg_replace('/\s+/u', ' ', trim((string) $node->textContent));
                    foreach (array_keys($productsByCode) as $code) {
                        if (preg_match('/(?<!\d)' . preg_quote($code, '/') . '(?!\d)/u', $text)) {
                            $matchedCode = $code;
                            break 2;
                        }
                    }
                }

                if (!$matchedCode || isset($found[$matchedCode])) continue;

                // Prefer an image physically inside the same matched card.
                $imageNodes = $xpath->query('.//img[@src or @data-src or @data-lazy-src or @data-original]', $anchor);
                $image = null;
                foreach ($imageNodes as $img) {
                    foreach (['data-src', 'data-lazy-src', 'data-original', 'src'] as $attr) {
                        if (!$img->hasAttribute($attr)) continue;
                        $candidate = self::normalizeUrl(html_entity_decode((string) $img->getAttribute($attr)));
                        if ($candidate && self::isLikelyProductImage($candidate)) {
                            $image = $candidate;
                            break 2;
                        }
                    }
                }

                // If the image is on the card wrapper rather than the anchor,
                // inspect that exact matched wrapper only.
                if (!$image && $node instanceof \DOMElement) {
                    $imageNodes = $xpath->query('.//img[@src or @data-src or @data-lazy-src or @data-original]', $node);
                    foreach ($imageNodes as $img) {
                        foreach (['data-src', 'data-lazy-src', 'data-original', 'src'] as $attr) {
                            if (!$img->hasAttribute($attr)) continue;
                            $candidate = self::normalizeUrl(html_entity_decode((string) $img->getAttribute($attr)));
                            if ($candidate && self::isLikelyProductImage($candidate)) {
                                $image = $candidate;
                                break 2;
                            }
                        }
                    }
                }

                if (!$image) continue;

                $product = $productsByCode[$matchedCode];
                $storedPath = self::downloadAndStore($product, $image, $matchedCode);
                if (!$storedPath) continue;

                $found[$matchedCode] = $storedPath;
            }
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
        if (preg_match('/<meta\b[^>]*(?:property|name)=["\']og:image["\'][^>]*content=["\']([^"\']+)["\']/iu', $html, $m)
            || preg_match('/<meta\b[^>]*content=["\']([^"\']+)["\'][^>]*(?:property|name)=["\']og:image["\']/iu', $html, $m)) {
            $url = self::normalizeUrl(html_entity_decode(trim($m[1])));
            if ($url && self::isLikelyProductImage($url)) return $url;
        }

        if (preg_match_all('/<(?:img|source)\b[^>]*(?:data-src|data-lazy-src|data-original|data-image|src)=["\']([^"\']+)["\']/iu', $html, $matches)) {
            foreach ($matches[1] as $raw) {
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
            '/<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/isu',
            $html,
            $matches,
            PREG_SET_ORDER
        )) {
            return [];
        }

        foreach ($matches as $match) {
            $url = self::normalizeUrl(html_entity_decode($match[1]));
            if (!$url) continue;

            $host = parse_url($url, PHP_URL_HOST);
            $path = rawurldecode((string) parse_url($url, PHP_URL_PATH));
            if (!$host || !preg_match('/(^|\.)palazonline\.com$/i', $host) || !str_starts_with($path, '/product/')) {
                continue;
            }

            $anchorHtml = html_entity_decode(strip_tags($match[2]));
            $combined = $path . ' ' . $anchorHtml;

            // The product card itself must contain this exact code.
            if (!preg_match('/(?<!\d)' . preg_quote($code, '/') . '(?!\d)/u', $combined)) {
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

    private static function downloadAndStore(Product $product, string $sourceUrl, string $code): ?string
    {
        try {
            $disk = 'public';
            $existing = $product->media()
                ->where('sort_order', 0)
                ->first();

            if ($existing && $existing->disk === $disk && !preg_match('/^https?:\\/\\//i', $existing->path)
                && $existing->source_url === $sourceUrl && Storage::disk($disk)->exists($existing->path)) {
                return $existing->path;
            }

            $response = Http::retry(2, 300)
                ->timeout(15)
                ->connectTimeout(5)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 PalazOnlineCatalog/1.0',
                    'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                ])
                ->get($sourceUrl);

            if (!$response->successful()) return null;

            $body = $response->body();
            if ($body === '' || strlen($body) > 12 * 1024 * 1024) return null;

            $contentType = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
            $extension = match ($contentType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/avif' => 'avif',
                default => null,
            };

            if (!$extension) {
                $urlPath = strtolower((string) parse_url($sourceUrl, PHP_URL_PATH));
                $extension = match (true) {
                    str_ends_with($urlPath, '.jpg'),
                    str_ends_with($urlPath, '.jpeg') => 'jpg',
                    str_ends_with($urlPath, '.png') => 'png',
                    str_ends_with($urlPath, '.webp') => 'webp',
                    str_ends_with($urlPath, '.avif') => 'avif',
                    default => 'webp',
                };
            }

            $safeCode = preg_replace('/[^A-Za-z0-9_-]+/', '-', $code) ?: (string) $product->id;
            $path = 'products/catalog/' . $safeCode . '.' . $extension;

            Storage::disk($disk)->put($path, $body);

            $product->media()->updateOrCreate(
                ['sort_order' => 0],
                [
                    'disk' => $disk,
                    'path' => $path,
                    'source_url' => $sourceUrl,
                    'alt' => $product->name . ' - کد ' . $code,
                    'is_cover' => true,
                ]
            );

            return $path;
        } catch (\Throwable) {
            return null;
        }
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
