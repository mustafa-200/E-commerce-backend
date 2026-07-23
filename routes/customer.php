<?php

use App\Http\Controllers\Customer\{
    AddressController,
    CartController,
    CheckoutController,
    OrderController,
    ProductController,
    CategoryController,
    BrandController,
    SliderController,
    SettingController
};
use Illuminate\Support\Facades\Route;

/* ======================= Public Routes ======================= */
// Public Storefront Routes —without any Middleware
Route::get('categories', [CategoryController::class, 'index']);
Route::get('brands', [BrandController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{slug}', [ProductController::class, 'show']);
Route::get('categories/{slug}/products', [ProductController::class, 'byCategorySlug']);

/* ======================= Setting Routes ======================= */
Route::get('sliders', [SliderController::class, 'index']);
Route::get('settings', [SettingController::class, 'index']);


/* ======================= Customer Routes ======================= */
// Authenticated Customer Routes —with Middleware
Route::middleware('resolve.user')->group(function () {
    Route::get('cart', [CartController::class, 'show']);
    Route::post('cart', [CartController::class, 'store']);
    Route::put('cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('cart/{cartItem}', [CartController::class, 'destroy']);
});

// Authenticated Customer Routes —with Sanctum Middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('addresses', AddressController::class)->except(['show']);
    Route::post('checkout', [CheckoutController::class, 'store']);

    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{orderId}', [OrderController::class, 'show']);
    Route::post('cart/merge', [CartController::class, 'merge']); 
});