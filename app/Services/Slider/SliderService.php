<?php

namespace App\Services\Slider;

use App\Models\Slider;
use App\Services\Image\ImageUploadService;
use Illuminate\Support\Collection;

class SliderService
{
    public function __construct(protected ImageUploadService $imageUploadService)
    {
    }

    public function list(): Collection
    {
        return Slider::orderBy('sort_order')->get();
    }

    public function listActive(): Collection
    {
        return Slider::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function create(array $data): Slider
    {
        $data['image'] = $this->imageUploadService->upload($data['image'], 'sliders');

        return Slider::create($data);
    }

    public function update(Slider $slider, array $data): Slider
    {
        if (isset($data['image'])) {
            $this->imageUploadService->delete($slider->image);
            $data['image'] = $this->imageUploadService->upload($data['image'], 'sliders');
        }

        $slider->update($data);

        return $slider;
    }

    public function delete(Slider $slider): void
    {
        $this->imageUploadService->delete($slider->image);
        $slider->delete();
    }
}
