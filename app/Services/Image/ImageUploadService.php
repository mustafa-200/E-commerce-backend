<?php

namespace App\Services\Image;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ImageUploadService
{
    public function upload(UploadedFile $file, string $folder, int $maxWidth = 1200, int $quality = 80): string
    {
        $image = Image::read($file);

        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        $filename = uniqid() . '.webp';
        $path = "{$folder}/{$filename}";

        Storage::disk('public')->put(
            $path,
            (string) $image->toWebp(quality: $quality)
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
