<?php

namespace App\Services\Image;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageUploadService
{
    public function upload(
        UploadedFile $file,
        string $folder,
        int $maxWidth = 1200,
        int $quality = 75
    ): string {
        $image = Image::read($file);

        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        $filename = uniqid('', true) . '.webp';
        $path = "{$folder}/{$filename}";

        Storage::disk('public')->put(
            $path,
            (string) $image->toJpeg(quality: $quality)
        );

        return $path;
    }

    /**
     * زي upload() بالظبط، لكن بتعمل Crop إجباري لنسبة عرض/ارتفاع ثابتة
     * بغض النظر عن مقاس الصورة الأصلي. مناسبة للسلايدرز والبانرات
     * اللي محتاجة تملأ مساحة بنسبة معينة من غير تمدد أو بكسلة.
     */
    public function uploadWithCrop(
        UploadedFile $file,
        string $folder,
        int $width = 1600,
        int $height = 600,
        int $quality = 100
    ): string {
        $image = Image::read($file);

        // pad() بيعمل Cover + Crop من النص عشان الصورة تملأ المقاس
        // المطلوب بالظبط من غير ما تتمدد، حتى لو نسبتها الأصلية مختلفة
        $image->cover($width, $height);

        $filename = uniqid('', true) . '.webp';
        $path = "{$folder}/{$filename}";

        Storage::disk('public')->put(
            $path,
            (string) $image->toJpeg(quality: $quality)
        );

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}