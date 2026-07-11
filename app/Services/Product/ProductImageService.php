<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Image\ImageUploadService;

class ProductImageService
{
    public function __construct(protected ImageUploadService $imageUploadService)
    {
    }

    public function upload(Product $product, array $data): ProductImage
    {
        $path = $this->imageUploadService->upload($data['image'], 'products');

        // لو الصورة الجديدة Primary، نلغي الـ Primary من باقي صور نفس المنتج
        if (!empty($data['is_primary'])) {
            $product->images()->update(['is_primary' => false]);
        }

        return $product->images()->create([
            'image' => $path,
            'alt_text' => $data['alt_text'] ?? null,
            'is_primary' => $data['is_primary'] ?? false,
        ]);
    }

    public function delete(ProductImage $productImage): void
    {
        $this->imageUploadService->delete($productImage->image);
        $productImage->delete();
    }
}
