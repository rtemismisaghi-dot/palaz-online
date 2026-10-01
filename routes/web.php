<?php

use App\Http\Controllers\StoreController;
use App\Http\Controllers\AdvisorController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'home'])->name('home');
Route::get('/shop', [StoreController::class, 'shop'])->name('shop');
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

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('products', ProductController::class)->except(['show']);
    Route::post('products/{product}/media', [ProductController::class, 'uploadMedia'])->name('products.media.store');
    Route::delete('products/{product}/media/{media}', [ProductController::class, 'deleteMedia'])->name('products.media.destroy');
    Route::post('products/{product}/media/{media}/cover', [ProductController::class, 'setCover'])->name('products.media.cover');
});
