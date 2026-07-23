<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Services\Image\ImageUploadService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {
    }

    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with([
                'category:id,name',
                'brand:id,name',
                'images:id,product_id,image,is_primary',
                'variants:id,product_id,price,sale_price,stock_quantity,is_default',
            ])
            ->select([
                'id',
                'category_id',
                'brand_id',
                'name',
                'name_en',
                'slug',
                'is_featured',
                'is_best_seller',
                'created_at',
            ])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * إنشاء منتج كامل (بيانات أساسية + variants + صور) في عملية واحدة ذرية.
     * لو أي جزء فشل (حتى لو أثناء رفع صورة)، كل حاجة بترجع لورا تلقائيًا
     * ومفيش أي سجل يتخزن في الداتابيز.
     */
    public function createFull(array $productData, array $variants, array $images): Product
    {
        return DB::transaction(function () use ($productData, $variants, $images) {
            $productData['slug'] = $this->ensureUniqueSlug($productData['name_en']);
            $product = Product::create($productData);

            foreach ($variants as $index => $variantData) {
                $variant = $product->variants()->create([
                    'sku' => $variantData['sku'],
                    'barcode' => $variantData['barcode'] ?? null,
                    'price' => $variantData['price'],
                    'sale_price' => $variantData['sale_price'] ?? null,
                    'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                    'is_default' => $index === 0,
                    'is_active' => true,
                ]);

                if (!empty($variantData['attribute_value_ids'])) {
                    $variant->attributeValues()->sync($variantData['attribute_value_ids']);
                }
            }

            foreach ($images as $index => $imageFile) {
                $path = $this->imageUploadService->upload($imageFile, 'products');

                $product->images()->create([
                    'image' => $path,
                    'is_primary' => $index === 0,
                ]);
            }

            return $product->load(['category', 'brand', 'variants.attributeValues.attribute', 'images']);
        });
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

    public function featured(int $limit = 8)
    {
        return Product::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with([
                'images:id,product_id,image,is_primary',
                'variants:id,product_id,price,sale_price,is_default',
            ])
            ->latest()
            ->limit($limit)
            ->get();
    }
}