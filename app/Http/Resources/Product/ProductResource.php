<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'category' => [
                'id'        => $this->category?->id,
                'name'      => $this->category?->name,
            ],

            'brand'         => $this->brand ? [
                'id'        => $this->brand->id,
                'name'      => $this->brand->name,
            ] : null,

            'name'              => $this->name,
            'name_en'           => $this->name_en,
            'slug'              => $this->slug,
            'short_description' => $this->short_description,
            'description'       => $this->description,
            'is_featured'       => $this->is_featured,
            'is_best_seller'    => $this->is_best_seller,
            'is_active'         => $this->is_active,
            'created_at'        => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
