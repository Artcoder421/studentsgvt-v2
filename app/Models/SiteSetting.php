<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'label'];

    public static function allMap(): array
    {
        return Cache::remember('sonit_site_settings', 300, function () {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $map = static::allMap();

        return $map[$key] ?? $default;
    }

    public static function setValue(string $key, ?string $value, string $group = 'general', ?string $label = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => $value,
                'group' => $group,
                'label' => $label,
            ], fn ($v) => $v !== null)
        );

        Cache::forget('sonit_site_settings');
    }

    public static function clearCache(): void
    {
        Cache::forget('sonit_site_settings');
    }
}
