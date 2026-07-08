<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Http\Resources\Brand\BrandResource;
use App\Models\Brand;
use App\Services\Brand\BrandService;

class BrandController extends Controller
{
    public function __construct(protected BrandService $brandService)
    {
    }

    public function index()
    {
        $brands = $this->brandService->list();

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب الماركات بنجاح',
            'data' => BrandResource::collection($brands),
            'meta' => [
                'current_page' => $brands->currentPage(),
                'last_page' => $brands->lastPage(),
                'total' => $brands->total(),
            ],
        ]);
    }

    public function store(StoreBrandRequest $request)
    {
        $brand = $this->brandService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنشاء الماركة بنجاح',
            'data' => new BrandResource($brand),
        ], 201);
    }

    public function show(Brand $brand)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب الماركة بنجاح',
            'data' => new BrandResource($brand),
        ]);
    }

    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $brand = $this->brandService->update($brand, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث الماركة بنجاح',
            'data' => new BrandResource($brand),
        ]);
    }

    public function destroy(Brand $brand)
    {
        $this->brandService->delete($brand);

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف الماركة بنجاح',
        ]);
    }
}