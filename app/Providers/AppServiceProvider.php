<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Railway (وأي منصة بتعمل SSL termination) بتوصل الريكوست لـ Laravel كـ http من جوه
        // فلازم نجبر الروابط المولّدة (asset/Storage::url) تطلع https في الإنتاج
        if ($this->app->environment('production') || config('app.url') && str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}