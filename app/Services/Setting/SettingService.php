<?php

namespace App\Services\Setting;

use App\Models\Setting;
use Illuminate\Support\Collection;

class SettingService
{
    public function all(): Collection
    {
        return Setting::all()->pluck('value', 'key');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Setting::where('key', $key)->value('value') ?? $default;
    }

    public function updateMany(array $settings): Collection
    {
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return $this->all();
    }
}
