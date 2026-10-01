<?php

use App\Http\Controllers\StoreController;
use App\Http\Controllers\AdvisorController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

Route::get('/', [StoreController::class, 'home'])->name('home');
Route::get('/shop', [StoreController::class, 'shop'])->name('shop');

Route::get('/dev/import-carpet-catalog', function () {
    abort_unless(app()->environment('local'), 404);

    if (! Schema::hasColumn('products', 'attributes') || ! Schema::hasColumn('products', 'is_featured')) {
        Schema::table('products', function ($table) {
            if (! Schema::hasColumn('products', 'attributes')) {
                $table->json('attributes')->nullable()->after('tone');
            }
            if (! Schema::hasColumn('products', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_active');
            }
        });
    }

    $path = base_path('docs/palaz-catalog-extraction-batch-2026-09-27.json');
    abort_unless(is_file($path), 404);

    $catalog = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    $items = collect($catalog['products'] ?? [])
        ->where('category', 'موکت')
        ->filter(fn ($item) => filled($item['code'] ?? null));

    $category = \App\Models\Category::updateOrCreate(
        ['slug' => 'carpet'],
        [
            'name' => 'موکت',
            'eyebrow' => 'کالکشن موکت پالاز',
            'tone' => 'موکت',
            'is_active' => true,
            'sort_order' => 10,
        ]
    );

    foreach ($items as $item) {
        $code = trim((string) $item['code']);
        $album = trim((string) ($item['album'] ?? ''));

        $product = \App\Models\Product::updateOrCreate(
            ['slug' => 'carpet-'.$code],
            [
                'category_id' => $category->id,
                'name' => trim((string) ($item['product_name'] ?? 'موکت پالاز')),
                'description' => $album ? 'آلبوم '.$album.' | کد '.$code : 'کد '.$code,
                'price' => isset($item['final_unit_price']) ? (float) $item['final_unit_price'] : null,
                'unit' => 'متر مربع',
                'tone' => 'موکت',
                'attributes' => [
                    'code' => $code,
                    'album' => $album,
                    'base_unit_price' => $item['base_unit_price'] ?? null,
                    'final_unit_price' => $item['final_unit_price'] ?? null,
                    'vat_percent' => $item['vat_percent'] ?? null,
                    'catalog_source' => $item['source_url'] ?? null,
                    'stock_type' => 'roll',
                    'roll_width' => 3,
                    'roll_lengths' => range(1, 15),
                ],
                'is_active' => true,
                'is_featured' => false,
            ]
        );

        \App\Models\ProductPricingRule::updateOrCreate(
            ['product_id' => $product->id],
            [
                'calculation_type' => 'roll',
                'unit' => 'm²',
                'waste_percent' => 0,
                'parameters' => [
                    'width' => 3,
                    'lengths' => range(1, 15),
                    'inventory_unit' => 'roll',
                    'sale_unit' => 'm²',
                ],
                'is_active' => true,
            ]
        );
    }

    return redirect()->route('shop', ['category' => 'carpet']);
})->name('dev.import-carpet-catalog');

Route::get('/dev/restore-carpet/{code}', function (string $code) {
    abort_unless(app()->environment('local'), 404);

    $code = trim($code);
    abort_unless($code !== '', 422);

    $path = base_path('docs/palaz-catalog-extraction-batch-2026-09-27.json');
    abort_unless(is_file($path), 404, 'Catalog source file not found.');

    $catalog = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    $item = collect($catalog['products'] ?? [])
        ->first(fn ($item) => trim((string) ($item['code'] ?? '')) === $code);

    abort_unless($item, 404, 'Catalog product code not found.');

    $category = \App\Models\Category::updateOrCreate(
        ['slug' => 'carpet'],
        [
            'name' => 'موکت',
            'eyebrow' => 'کالکشن موکت پالاز',
            'tone' => 'موکت',
            'is_active' => true,
            'sort_order' => 10,
        ]
    );

    $product = \App\Models\Product::updateOrCreate(
        ['slug' => 'carpet-' . $code],
        [
            'category_id' => $category->id,
            'name' => trim((string) ($item['product_name'] ?? 'موکت پالاز')),
            'description' => filled($item['album'] ?? null)
                ? 'آلبوم ' . trim((string) $item['album']) . ' | کد ' . $code
                : 'کد ' . $code,
            'price' => isset($item['final_unit_price']) ? (float) $item['final_unit_price'] : null,
            'unit' => 'متر مربع',
            'tone' => 'موکت',
            'attributes' => [
                'code' => $code,
                'album' => trim((string) ($item['album'] ?? '')),
                'base_unit_price' => $item['base_unit_price'] ?? null,
                'final_unit_price' => $item['final_unit_price'] ?? null,
                'vat_percent' => $item['vat_percent'] ?? null,
                'catalog_source' => $item['source_url'] ?? null,
                'stock_type' => 'roll',
                'roll_width' => 3,
                'roll_lengths' => range(1, 15),
                'image_import_attempted' => false,
            ],
            'is_active' => true,
            'is_featured' => false,
        ]
    );

    \App\Models\ProductPricingRule::updateOrCreate(
        ['product_id' => $product->id],
        [
            'calculation_type' => 'roll',
            'unit' => 'm²',
            'waste_percent' => 0,
            'parameters' => [
                'width' => 3,
                'lengths' => range(1, 15),
                'inventory_unit' => 'roll',
                'sale_unit' => 'm²',
            ],
            'is_active' => true,
        ]
    );

    $image = \App\Services\PalazCatalogImageResolver::resolve($product->fresh());

    return response()->json([
        'restored' => true,
        'product_id' => $product->id,
        'code' => $code,
        'name' => $product->name,
        'image' => $image,
        'message' => $image
            ? 'Product and image restored.'
            : 'Product restored; image was not found automatically.',
    ]);
})->name('dev.restore-carpet');

Route::get('/dev/debug-carpet-image/{code}', function (string $code) {
    abort_unless(app()->environment('local'), 404);
    return response()->json(\App\Services\PalazCatalogImageResolver::debugCode($code));
});

Route::get('/media/{path}', function (string $path) {
    $disk = Storage::disk('public');
    abort_unless($disk->exists($path), 404);

    return response()->file($disk->path($path), [
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*')->name('media.public');

Route::get('/dev/migrate-product-images', function () {
    abort_unless(app()->environment('local'), 404);
    abort_unless(Schema::hasTable('product_media'), 503, 'product_media migration is required.');

    // Keep the local migration endpoint self-healing if the storage migration
    // has not yet been applied to the developer database.
    if (!Schema::hasColumn('product_media', 'disk')) {
        Schema::table('product_media', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->string('disk', 32)->default('public')->after('product_id');
        });
    }

    if (!Schema::hasColumn('product_media', 'source_url')) {
        Schema::table('product_media', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->text('source_url')->nullable()->after('path');
        });
    }

    return response()->json(
        \App\Services\PalazCatalogImageResolver::migrateRemoteMediaBatch(
            max(1, min(20, (int) request()->integer('limit', 20)))
        )
    );
})->name('dev.migrate-product-images');

Route::get('/dev/import-carpet-images', function () {
    abort_unless(app()->environment('local'), 404);
    abort_unless(Schema::hasTable('product_media'), 503, 'product_media migration is required.');

    $products = \App\Models\Product::query()
        ->where('is_active', true)
        ->where('attributes->stock_type', 'roll')
        ->whereDoesntHave('media')
        ->when(! request()->boolean('retry'), function ($query) {
            $query->where(function ($query) {
                $query->whereNull('attributes->image_import_attempted')
                    ->orWhere('attributes->image_import_attempted', false);
            });
        })
        ->orderBy('id')
        ->limit(200)
        ->get();

    $found = \App\Services\PalazCatalogImageResolver::resolveStrictBatch($products);

    foreach ($products as $product) {
        $attributes = $product->attributes ?? [];
        $attributes['image_import_attempted'] = true;
        $product->forceFill(['attributes' => $attributes])->saveQuietly();
    }

    $remaining = \App\Models\Product::query()
        ->where('is_active', true)
        ->where('attributes->stock_type', 'roll')
        ->whereDoesntHave('media')
        ->count();

    return response()->json([
        'processed_now' => $products->count(),
        'images_found_now' => count($found),
        'remaining' => $remaining,
        'codes_found' => array_keys($found),
        'message' => $remaining ? 'Strict batch complete. Only exact product-code images were accepted.' : 'Carpet product images import completed.',
    ]);
})->name('dev.import-carpet-images');

Route::get('/product/{id}', [StoreController::class, 'product'])->name('product');
Route::get('/visualizer/products', [StoreController::class, 'visualizerProducts'])->name('visualizer.products');
Route::get('/services', [StoreController::class, 'services'])->name('services');
Route::get('/advisor', fn () => view('advisor.mobile'))->name('advisor');
Route::get('/calculate/{id}', [CalculatorController::class, 'show'])->name('calculator.show');
Route::post('/calculate/{id}', [CalculatorController::class, 'calculate'])->name('calculator.calculate');
Route::get('/cart', [StoreController::class, 'cart'])->name('cart');
Route::post('/cart/add/{id}', [StoreController::class, 'addToCart'])->name('cart.add');
Route::get('/checkout', [StoreController::class, 'checkout'])->name('checkout');
Route::post('/checkout', [StoreController::class, 'placeOrder'])->name('checkout.place');
Route::post('/services/request', [StoreController::class, 'serviceRequest'])->name('services.request');
Route::post('/advisor/chat', [AdvisorController::class, 'chat'])->name('advisor.chat');
Route::post('/advisor/analyze-space', [AdvisorController::class, 'analyzeSpace'])->name('advisor.analyze-space');

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'submit'])->name('login.submit');
Route::post('/login/verify', [LoginController::class, 'verify'])->name('login.verify');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/login/admin', [AuthController::class, 'login'])->name('login.admin');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::middleware(\App\Http\Middleware\AdminAuth::class)->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/management', [DashboardController::class, 'management'])->name('management');
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::post('/products/{product}/media', [ProductController::class, 'uploadMedia'])->name('products.media.store');
    Route::delete('/products/{product}/media/{media}', [ProductController::class, 'deleteMedia'])->name('products.media.destroy');
    Route::post('/products/{product}/media/{media}/cover', [ProductController::class, 'setCover'])->name('products.media.cover');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::get('/sales', [DashboardController::class, 'sales'])->name('sales.dashboard')->middleware('staff:sales');
Route::get('/installation', [DashboardController::class, 'installation'])->name('installation.dashboard')->middleware('staff:installation');
