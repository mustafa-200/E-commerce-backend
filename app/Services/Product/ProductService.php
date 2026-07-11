<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['category', 'brand'])
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Product
    {
        $data['slug'] = $this->ensureUniqueSlug($data['name_en']);

        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        if (isset($data['name_en'])) {
            $data['slug'] = $this->ensureUniqueSlug($data['name_en'], $product->id);
        }

        $product->update($data);

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    private function ensureUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $slug = Str::slug($value);
        $originalSlug = $slug;
        $count = 1;

        $query = Product::where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;

            $query = Product::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }
}
