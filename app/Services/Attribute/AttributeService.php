<?php

namespace App\Services\Attribute;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class AttributeService
{
    public function list(): Collection
    {
        return Attribute::with('values')->get();
    }

    public function create(array $data): Attribute
    {
        $attribute = Attribute::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['slug'] ?? $data['name']),
        ]);

        if (!empty($data['values'])) {
            foreach ($data['values'] as $value) {
                $attribute->values()->create(['value' => $value]);
            }
        }

        return $attribute->load('values');
    }

    public function update(Attribute $attribute, array $data): Attribute
    {
        $attribute->update($data);

        return $attribute->load('values');
    }

    public function delete(Attribute $attribute): void
    {
        $attribute->delete(); // بيمسح الـ Values تلقائي بسبب cascadeOnDelete
    }

    public function addValue(Attribute $attribute, string $value): AttributeValue
    {
        return $attribute->values()->create(['value' => $value]);
    }

    public function deleteValue(AttributeValue $attributeValue): void
    {
        $attributeValue->delete();
    }
}