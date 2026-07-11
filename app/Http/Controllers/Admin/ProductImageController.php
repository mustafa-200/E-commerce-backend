<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductImageRequest;
use App\Http\Resources\Product\ProductImageResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Product\ProductImageService;

class ProductImageController extends Controller
{
    public function __construct(protected ProductImageService $productImageService)
    {
    }

    public function store(StoreProductImageRequest $request, Product $product)
    {
        $image = $this->productImageService->upload($product, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم رفع الصورة بنجاح',
            'data' => new ProductImageResource($image),
        ], 201);
    }

    public function destroy(ProductImage $productImage)
    {
        $this->productImageService->delete($productImage);

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف الصورة بنجاح',
        ]);
    }
}
