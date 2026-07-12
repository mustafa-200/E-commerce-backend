<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductVariantRequest;
use App\Http\Requests\Product\UpdateProductVariantRequest;
use App\Http\Resources\Product\ProductVariantResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Product\ProductVariantService;

class ProductVariantController extends Controller
{
    public function __construct(protected ProductVariantService $variantService)
    {
    }

    public function index(Product $product)
    {
        $variants = $this->variantService->list($product);

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب المتغيرات بنجاح',
            'data' => ProductVariantResource::collection($variants),
        ]);
    }

    public function store(StoreProductVariantRequest $request, Product $product)
    {
        $variant = $this->variantService->create($product, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنشاء المتغير بنجاح',
            'data' => new ProductVariantResource($variant),
        ], 201);
    }

    public function show(ProductVariant $variant)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب المتغير بنجاح',
            'data' => new ProductVariantResource($variant->load('attributeValues')),
        ]);
    }

    public function update(UpdateProductVariantRequest $request, ProductVariant $variant)
    {
        $variant = $this->variantService->update($variant, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث المتغير بنجاح',
            'data' => new ProductVariantResource($variant),
        ]);
    }

    public function destroy(ProductVariant $variant)
    {
        $this->variantService->delete($variant);

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف المتغير بنجاح',
        ]);
    }
}
