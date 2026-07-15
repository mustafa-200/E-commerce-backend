<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Slider\SliderResource;
use App\Services\Slider\SliderService;

class SliderController extends Controller
{
    public function __construct(protected SliderService $sliderService)
    {
    }

    public function index()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'تم جلب السلايدرز بنجاح',
            'data' => SliderResource::collection($this->sliderService->listActive()),
        ]);
    }
}
