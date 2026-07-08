<?php

namespace App\Services\Brand;

use App\Models\Brand;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class BrandService
{
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return Brand::orderBy('name')->paginate($perPage);
    }

    public function create(array $data): Brand
    {
        $data['slug'] = $this->generateUniqueSlug($data['slug'] ?? $data['name']);

        if (isset($data['logo'])) {
            $data['logo'] = $data['logo']->store('brands', 'public');
        }

        return Brand::create($data);
    }

    public function update(Brand $brand, array $data): Brand
    {
        if (isset($data['slug']) || isset($data['name'])) {
            $data['slug'] = $this->generateUniqueSlug($data['slug'] ?? $data['name'], $brand->id);
        }

        if (isset($data['logo'])) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $data['logo'] = $data['logo']->store('brands', 'public');
        }

        $brand->update($data);

        return $brand;
    }

    public function delete(Brand $brand): void
    {
        $brand->delete();
    }

    private function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $slug = Str::slug($value);
        $originalSlug = $slug;
        $count = 1;

        $query = Brand::where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;

            $query = Brand::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }
}