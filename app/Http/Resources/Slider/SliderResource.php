<?php

namespace App\Http\Resources\Slider;

use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => asset('storage/' . $this->image),
            'link' => $this->link,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];
    }
}