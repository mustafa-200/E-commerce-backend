<?php

namespace App\Services\Category;

use App\Models\Category;
use App\Exceptions\CategoryHasProductsException;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Image\ImageUploadService;

class CategoryService
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {
    }

    public function list(int $perPage = 15): LengthAwarePaginator
    {
        return Category::with('parent')
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    /**
     * التصنيفات الرئيسية مع الأبناء
     */
    public function tree()
    {
        return Category::whereNull('parent_id')
            ->with([
                'children' => fn ($q) =>
                    $q->orderBy('sort_order')
            ])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * التصنيفات النشطة مع الأبناء النشطين
     */
    public function listActiveTree()
    {
        return Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with([
                'children' => fn ($q) =>
                    $q->where('is_active', true)
                        ->orderBy('sort_order')
            ])
            ->orderBy('sort_order')
            ->get();
    }

    public function create(array $data): Category
    {
        $data['slug'] = $this->generateUniqueSlug(
            $data['slug'] ?? $data['name']
        );

        if (
            isset($data['image']) &&
            $data['image'] instanceof \Illuminate\Http\UploadedFile
        ) {
            $data['image'] = $this->imageUploadService->upload(
                $data['image'],
                'categories'
            );
        }

        return Category::create($data);
    }

    public function update(
        Category $category,
        array $data
    ): Category {
        $oldImage = $category->image;
        $newImage = null;

        if (
            isset($data['image']) &&
            $data['image'] instanceof \Illuminate\Http\UploadedFile
        ) {
            // نرفع الصورة الجديدة أولاً
            $newImage = $this->imageUploadService->upload(
                $data['image'],
                'categories'
            );

            $data['image'] = $newImage;
        }

        $category->update($data);

        // نحذف القديمة بعد نجاح التحديث
        if ($newImage && $oldImage) {
            $this->imageUploadService->delete($oldImage);
        }

        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        if ($category->products()->exists()) {
            throw new CategoryHasProductsException(
                'لا يمكن حذف هذا التصنيف لأنه مرتبط بمنتجات موجودة. قم بحذف أو نقل المنتجات أولاً.'
            );
        }

        // حذف صورة التصنيف
        if ($category->image) {
            $this->imageUploadService->delete(
                $category->image
            );
        }

        $category->delete();
    }

    private function generateUniqueSlug(
        string $value,
        ?int $ignoreId = null
    ): string {
        $slug = Str::slug($value);

        $originalSlug = $slug;
        $count = 1;

        while (
            Category::where('slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) =>
                        $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}