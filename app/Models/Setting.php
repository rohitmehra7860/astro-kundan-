<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("setting.{$key}");
    }

    /**
     * Get all settings as a key => value array.
     */
    public static function allGrouped(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    /**
     * Flush all settings cache.
     */
    public static function flushCache(): void
    {
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
    }
}
