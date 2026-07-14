<?php

namespace App\Services\Storefront;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Pagination\LengthAwarePaginator;

class StorefrontProductService
{
    public function list(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['category', 'brand', 'images', 'variants' => fn($q) => $q->where('is_active', true)]);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        match ($filters['sort'] ?? null) {
            'price_asc' => $query->orderBy(
                ProductVariant::select('price')
                    ->whereColumn('product_id', 'products.id')
                    ->orderBy('price')
                    ->limit(1)
            ),
            'price_desc' => $query->orderByDesc(
                ProductVariant::select('price')
                    ->whereColumn('product_id', 'products.id')
                    ->orderByDesc('price')
                    ->limit(1)
            ),
            default => $query->latest(),
        };

        return $query->paginate($perPage);
    }

    public function findBySlug(string $slug): Product
    {
        return Product::where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'category',
                'brand',
                'images',
                'variants' => fn($q) => $q->where('is_active', true),
                'variants.attributeValues.attribute',
            ])
            ->firstOrFail();
    }
}
