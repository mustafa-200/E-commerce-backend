<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Services\Storefront\StorefrontProductService;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected StorefrontProductService $productService)
    {
    }

    public function index(Request $request)
    {
        $products = $this->productService->list(
            [
                'category_id' => $request->query('category_id'),
                'brand_id' => $request->query('brand_id'),
                'search' => $request->query('search'),
                'featured' => $request->query('featured'),
                'sort' => $request->query('sort'),
            ],
            (int) $request->query('limit', 12) // ← جديد
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب المنتجات بنجاح',
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $product = $this->productService->findBySlug($slug);

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب المنتج بنجاح',
            'data' => new ProductResource($product),
        ]);
    }

    public function byCategorySlug(Request $request, string $slug)
    {
        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $products = $this->productService->list([
            'category_id' => $category->id,
            'sort' => $request->query('sort'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب منتجات التصنيف بنجاح',
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }
}
