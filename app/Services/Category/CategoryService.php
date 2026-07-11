<?php

namespace App\Services\Category;

use App\Models\Category;
use App\Exceptions\CategoryHasProductsException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use App\Services\Image\ImageUploadService;


class CategoryService
{
    protected ImageUploadService $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }
    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return Category::with('parent')
            ->orderBy('sort_order')
            ->paginate($perPage);
    }


    
    public function create(array $data): Category
    {
        $data['slug'] = $this->generateUniqueSlug($data['slug'] ?? $data['name']);

        if (isset($data['image'])) {
            $data['image'] = $this->imageUploadService->upload($data['image'], 'categories');
        }

        return Category::create($data);
    }



    public function update(Category $category, array $data): Category
    {
        if (isset($data['image'])) {
            $this->imageUploadService->delete($category->image);
            $data['image'] = $this->imageUploadService->upload($data['image'], 'categories');
        }

        $category->update($data);

        return $category;
    }

    public function delete(Category $category): void
    {
        try {
            $category->delete();
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                throw new CategoryHasProductsException(
                    'لا يمكن حذف هذا التصنيف لأنه مرتبط بمنتجات موجودة. قم بحذف أو نقل المنتجات أولاً.'
                );
            }

            throw $e; // أي خطأ تاني غير متوقع، خليه يظهر عادي
        }
    }


    private function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $slug = Str::slug($value);
        $originalSlug = $slug;
        $count = 1;

        $query = Category::where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;

            $query = Category::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }
}
