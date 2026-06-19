<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'seller_id',
    ];

    protected $appends = ['products_count'];

    protected static function booted(): void
    {
        static::creating(function (ProductCategory $category) {
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->name);
            }
        });

        static::updating(function (ProductCategory $category) {
            if ($category->isDirty('name') && ! $category->isDirty('slug')) {
                $category->slug = static::generateUniqueSlug($category->name, $category->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name) ?: 'category';
        $original = $slug;
        $count = 1;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $original.'-'.$count++;
        }

        return $slug;
    }

    public static function findBySlugOrId(string $identifier): ?self
    {
        return static::query()
            ->where(function ($query) use ($identifier) {
                $query->where('slug', $identifier);

                if (ctype_digit($identifier)) {
                    $query->orWhere('id', (int) $identifier);
                }
            })
            ->first();
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function getProductsCountAttribute(): int
    {
        return Product::query()
            ->where('category', $this->name)
            ->where('status', 'active')
            ->count();
    }
}
