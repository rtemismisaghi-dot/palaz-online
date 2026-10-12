<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

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
    /**
     * Return a product's exact-code image as a data URL for server-side visual classification.
     * Only local stored images or known Palaz Online source URLs are accepted.
     */
    public static function imageDataUrlForProduct(string $slug): ?string
    {
        if (! Schema::hasTable('product_media')) {
            return null;
        }

        $product = self::productQuery()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        $media = $product?->media?->first();
        if (! $media || ! $media->path) {
            return null;
        }

        $path = trim((string) $media->path);
        $bytes = null;
        $mime = null;

        try {
            if (preg_match('/^https?:\\/\\//i', $path)) {
                $host = strtolower((string) parse_url($path, PHP_URL_HOST));
                if (! in_array($host, ['palazonline.com', 'www.palazonline.com', 'cdn.palazonline.com'], true)) {
                    return null;
                }

                $response = Http::timeout(8)->accept('image/*')->get($path);
                if (! $response->successful()) {
                    return null;
                }

                $mime = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
                $bytes = $response->body();
            } else {
                $disk = trim((string) ($media->disk ?: 'public'));
                $storage = Storage::disk($disk);
                if ($storage->exists($path)) {
                    $bytes = $storage->get($path);
                    $mime = strtolower((string) ($storage->mimeType($path) ?: ''));
                } else {
                    // Some migrated catalog rows retain the official source image URL even
                    // if the local file was not copied successfully. Use only known Palaz hosts.
                    $sourceUrl = trim((string) ($media->source_url ?? ''));
                    $sourceHost = strtolower((string) parse_url($sourceUrl, PHP_URL_HOST));
                    if (
                        $sourceUrl === ''
                        || ! preg_match('/^https:\\/\\//i', $sourceUrl)
                        || ! in_array($sourceHost, ['palazonline.com', 'www.palazonline.com', 'cdn.palazonline.com'], true)
                    ) {
                        return null;
                    }

                    $response = Http::timeout(8)->accept('image/*')->get($sourceUrl);
                    if (! $response->successful()) {
                        return null;
                    }

                    $bytes = $response->body();
                    $mime = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
                }
            }
        } catch (\Throwable $e) {
            report($e);
            return null;
        }

        if (! is_string($bytes) || $bytes === '' || strlen($bytes) > 3 * 1024 * 1024) {
            return null;
        }

        if (! is_string($mime) || ! str_starts_with($mime, 'image/')) {
            $extension = strtolower(pathinfo(parse_url($path, PHP_URL_PATH) ?: $path, PATHINFO_EXTENSION));
            $mime = match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                default => '',
            };
        }

        if ($mime === '') {
            return null;
        }

        // Compress large catalog swatches before sending them to the vision model.
        // The product code and visible carpet texture remain readable at this size.
        if (function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
            $source = @imagecreatefromstring($bytes);
            if ($source !== false) {
                $sourceWidth = imagesx($source);
                $sourceHeight = imagesy($source);
                if ($sourceWidth > 320 || $sourceHeight > 320 || strlen($bytes) > 120 * 1024) {
                    $scale = min(1, 320 / max(1, $sourceWidth), 320 / max(1, $sourceHeight));
                    $targetWidth = max(1, (int) round($sourceWidth * $scale));
                    $targetHeight = max(1, (int) round($sourceHeight * $scale));
                    $target = imagecreatetruecolor($targetWidth, $targetHeight);
                    if ($target !== false) {
                        $white = imagecolorallocate($target, 255, 255, 255);
                        imagefill($target, 0, 0, $white);
                        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
                        ob_start();
                        $encoded = imagejpeg($target, null, 74);
                        $optimized = ob_get_clean();
                        if ($encoded && is_string($optimized) && $optimized !== '') {
                            $bytes = $optimized;
                            $mime = 'image/jpeg';
                        }
                        imagedestroy($target);
                    }
                }
                imagedestroy($source);
            }
        }

        if (strlen($bytes) > 512 * 1024) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($bytes);
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
