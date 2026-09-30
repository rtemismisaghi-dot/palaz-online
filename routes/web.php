<?php

use App\Http\Controllers\StoreController;
use App\Http\Controllers\AdvisorController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

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

Route::get('/dev/import-carpet-images', function () {
    abort_unless(app()->environment('local'), 404);
    abort_unless(Schema::hasTable('product_media'), 503, 'product_media migration is required.');

    $products = \App\Models\Product::query()
        ->where('is_active', true)
        ->where('attributes->stock_type', 'roll')
        ->with('media')
        ->get();

    $pending = $products->filter(fn ($product) => $product->media->isEmpty())->values();
    $batch = $pending->take(10);
    $found = 0;

    foreach ($batch as $product) {
        if (\App\Services\PalazCatalogImageResolver::resolve($product)) {
            $found++;
        }
    }

    $remaining = max(0, $pending->count() - $batch->count());

    return response()->json([
        'total' => $products->count(),
        'processed_now' => $batch->count(),
        'images_found_now' => $found,
        'remaining' => $remaining,
        'message' => $remaining > 0
            ? '10 products processed. Refresh this URL to continue.'
            : 'Carpet product images import completed.',
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

Route::get('/login', [AuthController::class, 'show'])->name('login');
Route::post('/login/staff', [AuthController::class, 'staffLogin'])->name('login.staff');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware('staff:admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/management', [DashboardController::class, 'management'])->name('management');
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
});

Route::get('/sales', [DashboardController::class, 'sales'])->name('sales.dashboard')->middleware('staff:sales');
Route::get('/installation', [DashboardController::class, 'installation'])->name('installation.dashboard')->middleware('staff:installation');
