<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Brand\BrandResource;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب الماركات بنجاح',
            'data' => BrandResource::collection($brands),
        ]);
    }
}
