<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
        'validation_rules',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'value' => 'json',
    ];

    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_ARRAY = 'array';
    const TYPE_JSON = 'json';

    /**
     * Get setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->getCastedValue() : $default;
        });
    }

    /**
     * Set setting value.
     */
    public static function set(string $key, $value, string $type = self::TYPE_STRING): void
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
            ]
        );

        Cache::forget("setting.{$key}");
    }

    /**
     * Get casted value based on type.
     */
    public function getCastedValue()
    {
        switch ($this->type) {
            case self::TYPE_INTEGER:
                return (int) $this->value;
            case self::TYPE_FLOAT:
                return (float) $this->value;
            case self::TYPE_BOOLEAN:
                return (bool) $this->value;
            case self::TYPE_ARRAY:
            case self::TYPE_JSON:
                return is_array($this->value) ? $this->value : json_decode($this->value, true);
            default:
                return $this->value;
        }
    }

    /**
     * Get all settings grouped by category.
     */
    public static function getAllGrouped(): array
    {
        return static::all()
            ->groupBy('group')
            ->map(function ($settings) {
                return $settings->mapWithKeys(function ($setting) {
                    return [$setting->key => $setting->getCastedValue()];
                });
            })
            ->toArray();
    }

    /**
     * Get public settings only.
     */
    public static function getPublic(): array
    {
        return static::where('is_public', true)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->getCastedValue()];
            })
            ->toArray();
    }

    /**
     * Clear settings cache.
     */
    public static function clearCache(): void
    {
        Cache::flush(); // In production, you might want to be more specific
    }

    /**
     * Boot method to clear cache on model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            Cache::forget("setting.{$setting->key}");
        });

        static::deleted(function ($setting) {
            Cache::forget("setting.{$setting->key}");
        });
    }
}