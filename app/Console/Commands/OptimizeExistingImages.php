<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class OptimizeExistingImages extends Command
{
    /**
     * الأمر: php artisan images:optimize
     *
     * خيارات اختيارية:
     *   --width=1200   أقصى عرض للصورة بعد الضغط (افتراضي 1200)
     *   --quality=75   جودة WebP بعد الضغط (افتراضي 75)
     *   --dry-run      يعرض بس اللي هيتعمل من غير ما يعدل أي ملف فعليًا
     */
    protected $signature = 'images:optimize
                            {--width=1200 : أقصى عرض للصورة}
                            {--quality=75 : جودة الضغط (1-100)}
                            {--dry-run : تجربة بدون تعديل فعلي}';

    protected $description = 'إعادة ضغط كل الصور الموجودة في storage/app/public (منتجات، سلايدرز، أقسام) لتقليل حجمها';

    // المجلدات اللي فيها صور محتاجة ضغط
    protected array $folders = ['products', 'sliders', 'categories'];

    public function handle(): int
    {
        $maxWidth = (int) $this->option('width');
        $quality = (int) $this->option('quality');
        $dryRun = $this->option('dry-run');

        $disk = Storage::disk('public');

        $totalBefore = 0;
        $totalAfter = 0;
        $processedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($this->folders as $folder) {
            if (!$disk->exists($folder)) {
                $this->warn("المجلد '{$folder}' مش موجود، هنتخطاه.");
                continue;
            }

            $files = $disk->files($folder);
            $this->info("جاري فحص مجلد '{$folder}' — " . count($files) . " ملف موجود.");

            $bar = $this->output->createProgressBar(count($files));
            $bar->start();

            foreach ($files as $filePath) {
                $bar->advance();

                // نتعامل بس مع صور (احتياطًا لو فيه ملفات تانية اتحطت غلط)
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if (!in_array($extension, ['webp', 'jpg', 'jpeg', 'png'])) {
                    $skippedCount++;
                    continue;
                }

                try {
                    $sizeBefore = $disk->size($filePath);
                    $totalBefore += $sizeBefore;

                    if ($dryRun) {
                        // في وضع dry-run منحسبش حجم بعد الضغط فعليًا
                        // (هيتطلب فتح الصورة ومعالجتها، وده اللي بنتفاداه هنا)
                        $processedCount++;
                        continue;
                    }

                    $fullPath = $disk->path($filePath);
                    $image = Image::read($fullPath);

                    if ($image->width() > $maxWidth) {
                        $image->scale(width: $maxWidth);
                    }

                    $newContent = (string) $image->toWebp(quality: $quality);

                    // لو الملف الأصلي مش .webp، هنغيّر الامتداد ونحدّث الـ path
                    if ($extension !== 'webp') {
                        $newPath = preg_replace('/\.' . $extension . '$/i', '.webp', $filePath);
                        $disk->put($newPath, $newContent);
                        $disk->delete($filePath);

                        $this->newLine();
                        $this->warn("⚠️  الملف '{$filePath}' اتحول لـ '{$newPath}'. لو الـ path ده متخزن في الداتابيز، لازم تحدّثه يدويًا.");

                        $sizeAfter = $disk->size($newPath);
                    } else {
                        $disk->put($filePath, $newContent);
                        $sizeAfter = $disk->size($filePath);
                    }

                    $totalAfter += $sizeAfter;
                    $processedCount++;
                } catch (\Throwable $e) {
                    $errorCount++;
                    $this->newLine();
                    $this->error("فشل معالجة '{$filePath}': " . $e->getMessage());
                }
            }

            $bar->finish();
            $this->newLine(2);
        }

        $this->newLine();
        $this->info('=== النتيجة النهائية ===');
        $this->table(
            ['البند', 'القيمة'],
            [
                ['تم معالجته', $processedCount],
                ['تم تخطيه (مش صورة)', $skippedCount],
                ['فشل', $errorCount],
                ['الحجم قبل', $this->formatBytes($totalBefore)],
                ['الحجم بعد', $dryRun ? 'N/A (dry-run)' : $this->formatBytes($totalAfter)],
                ['نسبة التوفير', $dryRun || $totalBefore === 0 ? 'N/A' : round((1 - $totalAfter / $totalBefore) * 100, 1) . '%'],
            ]
        );

        if ($dryRun) {
            $this->comment('ده كان Dry Run بس — مفيش أي ملف اتغيّر فعليًا. شغّل الأمر من غير --dry-run عشان تطبّق الضغط فعليًا.');
        }

        return self::SUCCESS;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
