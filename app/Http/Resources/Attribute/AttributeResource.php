<?php

namespace App\Http\Resources\Attribute;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'values' => $this->whenLoaded('values', function () {
                return $this->values->map(fn($v) => [
                    'id' => $v->id,
                    'value' => $v->value,
                ]);
            }),
        ];
    }
}