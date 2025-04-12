<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GlobalSetting extends Model
{
    protected $fillable = ['key', 'label', 'value'];

    // Retrieves a setting value, using caching to minimize database queries.
    public static function get($key)
    {
        return Cache::remember("global_setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->value('value');
        });
    }

    // Sets (or updates) a setting and clears any cache.
    public static function set($key, $value)
    {
        Cache::forget("global_setting_{$key}");
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
