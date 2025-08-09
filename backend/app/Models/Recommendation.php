<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recommendable_type',
        'recommendable_id',
        'score',
        'algorithm_type',
        'factors',
        'is_viewed',
        'is_clicked',
        'is_purchased',
        'viewed_at',
        'clicked_at',
        'purchased_at',
        'is_active'
    ];

    protected $casts = [
        'factors' => 'array',
        'score' => 'decimal:4',
        'is_viewed' => 'boolean',
        'is_clicked' => 'boolean',
        'is_purchased' => 'boolean',
        'is_active' => 'boolean',
        'viewed_at' => 'datetime',
        'clicked_at' => 'datetime',
        'purchased_at' => 'datetime'
    ];

    /**
     * Get the user who received this recommendation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recommended item (product or business)
     */
    public function recommendable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark recommendation as viewed
     */
    public function markAsViewed(): bool
    {
        return $this->update([
            'is_viewed' => true,
            'viewed_at' => now()
        ]);
    }

    /**
     * Mark recommendation as clicked
     */
    public function markAsClicked(): bool
    {
        return $this->update([
            'is_clicked' => true,
            'clicked_at' => now()
        ]);
    }

    /**
     * Mark recommendation as purchased/invested
     */
    public function markAsPurchased(): bool
    {
        return $this->update([
            'is_purchased' => true,
            'purchased_at' => now()
        ]);
    }

    /**
     * Scope for active recommendations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for recommendations by algorithm type
     */
    public function scopeByAlgorithm($query, $algorithm)
    {
        return $query->where('algorithm_type', $algorithm);
    }

    /**
     * Scope for recommendations by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('recommendable_type', $type);
    }

    /**
     * Scope for high-scoring recommendations
     */
    public function scopeHighScoring($query, $threshold = 0.7)
    {
        return $query->where('score', '>=', $threshold);
    }

    /**
     * Get recommendation performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'view_rate' => $this->is_viewed ? 1 : 0,
            'click_rate' => $this->is_clicked ? 1 : 0,
            'conversion_rate' => $this->is_purchased ? 1 : 0,
            'score' => $this->score,
            'algorithm_type' => $this->algorithm_type
        ];
    }

    /**
     * Get recommendation factors as readable text
     */
    public function getFactorsText(): string
    {
        if (!$this->factors) {
            return 'No specific factors';
        }

        $factors = [];
        foreach ($this->factors as $factor => $value) {
            switch ($factor) {
                case 'category_match':
                    $factors[] = "Category preference match";
                    break;
                case 'price_match':
                    $factors[] = "Price range match";
                    break;
                case 'location_match':
                    $factors[] = "Location preference match";
                    break;
                case 'similar_users':
                    $factors[] = "Similar users liked this";
                    break;
                case 'trending':
                    $factors[] = "Currently trending";
                    break;
                case 'rating':
                    $factors[] = "High rating";
                    break;
                default:
                    $factors[] = ucfirst(str_replace('_', ' ', $factor));
            }
        }

        return implode(', ', $factors);
    }
}
