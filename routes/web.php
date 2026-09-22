<?php

use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StoreController::class, 'home'])->name('home');
Route::get('/shop', [StoreController::class, 'shop'])->name('shop');
Route::get('/product/{id}', [StoreController::class, 'product'])->name('product');
Route::get('/services', [StoreController::class, 'services'])->name('services');
Route::get('/cart', [StoreController::class, 'cart'])->name('cart');
Route::post('/cart/add/{id}', [StoreController::class, 'addToCart'])->name('cart.add');
Route::get('/checkout', [StoreController::class, 'checkout'])->name('checkout');
Route::post('/checkout', [StoreController::class, 'placeOrder'])->name('checkout.place');
Route::post('/services/request', [StoreController::class, 'serviceRequest'])->name('services.request');
