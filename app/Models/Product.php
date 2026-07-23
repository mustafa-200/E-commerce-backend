<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Brand;


class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'name_en',
        'slug',
        'short_description',
        'description',
        'is_featured',
        'is_best_seller',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_best_seller' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }


    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    protected static function booted(): void
    {
        // cascadeOnDelete على مستوى الداتابيز بيتفعل بس وقت DELETE حقيقي،
        // وبما إن Product بيستخدم SoftDeletes، لازم نعمل الـ Cascade
        // يدويًا هنا، وإلا الـ Variants تفضل is_active = true رغم
        // إن المنتج بتاعها اتمسح (Soft Delete) بالفعل.
        static::deleting(function (Product $product) {
            if ($product->isForceDeleting()) {
                $product->variants()->forceDelete();
            } else {
                $product->variants()->delete();
            }
        });
    }
}
