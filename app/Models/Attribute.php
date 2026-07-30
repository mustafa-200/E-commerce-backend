<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
    ];

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // خاصية عامة تظهر لكل الأقسام (زي "اللون") أو خاصة بقسم واحد بس (زي "الوزن" لقسم المواد الغذائية)
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where(function ($q) use ($categoryId) {
            $q->whereNull('category_id')
                ->orWhere('category_id', $categoryId);
        });
    }
}