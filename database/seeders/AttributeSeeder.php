<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        // خصائص عامة تظهر لكل الأقسام (category_id = null)
        $color = Attribute::firstOrCreate(
            ['slug' => 'color'],
            ['name' => 'اللون']
        );
        foreach (['أحمر', 'أزرق', 'أسود', 'أبيض'] as $value) {
            $color->values()->firstOrCreate(['value' => $value]);
        }

        $size = Attribute::firstOrCreate(
            ['slug' => 'size'],
            ['name' => 'المقاس']
        );
        foreach (['S', 'M', 'L', 'XL'] as $value) {
            $size->values()->firstOrCreate(['value' => $value]);
        }

        // مثال لخاصية خاصة بقسم معين: الوزن لقسم المواد الغذائية (لو القسم موجود)
        $foodCategory = Category::where('slug', 'food')
            ->orWhere('name', 'like', '%غذائي%')
            ->first();

        $weight = Attribute::firstOrCreate(
            ['slug' => 'weight'],
            ['name' => 'الوزن', 'category_id' => $foodCategory?->id]
        );
        foreach (['250 جرام', '500 جرام', '1 كيلو', '2 كيلو'] as $value) {
            $weight->values()->firstOrCreate(['value' => $value]);
        }
    }
}
