<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\StatsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    /* ======================= Stats Routes ======================= */
    Route::get('stats', [StatsController::class, 'index']);

    /* ======================= Category Routes ======================= */
    Route::get('categories/tree', [CategoryController::class, 'tree']);
    Route::apiResource('categories', CategoryController::class);

    /* ======================= Brand Routes ======================= */

    Route::apiResource('brands', BrandController::class);

    /* ======================= Attribute Routes ======================= */
    Route::apiResource('attributes', AttributeController::class);
    Route::post('attributes/{attribute}/values', [AttributeController::class, 'storeValue']);
    Route::delete('attribute-values/{attributeValue}', [AttributeController::class, 'destroyValue']);

    /* ======================= Product Routes ======================= */
    Route::apiResource('products', ProductController::class);
    Route::post('products/{product}/images', [ProductImageController::class, 'store']);
    Route::delete('product-images/{productImage}', [ProductImageController::class, 'destroy']);

    Route::patch('products/{product}/toggle-featured', [ProductController::class, 'toggleFeatured']);

    Route::get('products/{product}/variants', [ProductVariantController::class, 'index']);
    Route::post('products/{product}/variants', [ProductVariantController::class, 'store']);
    Route::get('variants/{variant}', [ProductVariantController::class, 'show']);
    Route::put('variants/{variant}', [ProductVariantController::class, 'update']);
    Route::delete('variants/{variant}', [ProductVariantController::class, 'destroy']);

    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{orderId}', [OrderController::class, 'show']);
    Route::put('orders/{orderId}/status', [OrderController::class, 'updateStatus']);
    Route::patch('/orders/{order}/shipping-cost', [OrderController::class, 'updateShippingCost']);

    /* ======================= Setting Routes ======================= */
    Route::apiResource('sliders', SliderController::class)->except(['show']);
    Route::get('settings', [SettingController::class, 'index']);
    Route::put('settings', [SettingController::class, 'update']);
});