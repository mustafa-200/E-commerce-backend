<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
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


});