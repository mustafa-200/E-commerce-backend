<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Slider\StoreSliderRequest;
use App\Http\Requests\Slider\UpdateSliderRequest;
use App\Http\Resources\Slider\SliderResource;
use App\Models\Slider;
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
            'data' => SliderResource::collection($this->sliderService->list()),
        ]);
    }

    public function store(StoreSliderRequest $request)
    {
        $slider = $this->sliderService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم إنشاء السلايدر بنجاح',
            'data' => new SliderResource($slider),
        ], 201);
    }

    public function update(UpdateSliderRequest $request, Slider $slider)
    {
        $slider = $this->sliderService->update($slider, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث السلايدر بنجاح',
            'data' => new SliderResource($slider),
        ]);
    }

    public function destroy(Slider $slider)
    {
        $this->sliderService->delete($slider);

        return response()->json([
            'status' => 'success',
            'message' => 'تم حذف السلايدر بنجاح',
        ]);
    }
}
