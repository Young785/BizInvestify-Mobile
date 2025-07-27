<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeaturedListing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'listable_type',
        'listable_id',
        'title',
        'description',
        'banner_image',
        'promotion_type',
        'daily_budget',
        'total_budget',
        'spent_amount',
        'impressions',
        'clicks',
        'ctr',
        'start_date',
        'end_date',
        'status',
        'targeting',
        'performance_metrics',
        'approved_at',
        'approved_by',
        'rejection_reason'
    ];

    protected $casts = [
        'banner_image' => 'array',
        'targeting' => 'array',
        'performance_metrics' => 'array',
        'daily_budget' => 'decimal:2',
        'total_budget' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'ctr' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'approved_at' => 'datetime'
    ];

    /**
     * Get the user who owns this featured listing
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the item being promoted (product or business)
     */
    public function listable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the admin who approved this listing
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if the promotion is currently active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               now()->between($this->start_date, $this->end_date) &&
               $this->spent_amount < $this->total_budget;
    }

    /**
     * Check if the promotion is within budget
     */
    public function isWithinBudget(): bool
    {
        return $this->spent_amount < $this->total_budget;
    }

    /**
     * Check if the promotion is within daily budget
     */
    public function isWithinDailyBudget(): bool
    {
        $todaySpent = $this->getTodaySpent();
        return $todaySpent < $this->daily_budget;
    }

    /**
     * Get amount spent today
     */
    public function getTodaySpent(): float
    {
        // This would be calculated based on actual spending
        // For now, return a simple calculation
        return $this->spent_amount / max(1, $this->start_date->diffInDays(now()));
    }

    /**
     * Record an impression
     */
    public function recordImpression(): void
    {
        $this->increment('impressions');
        $this->updateCTR();
    }

    /**
     * Record a click
     */
    public function recordClick(): void
    {
        $this->increment('clicks');
        $this->updateCTR();
    }

    /**
     * Update click-through rate
     */
    private function updateCTR(): void
    {
        if ($this->impressions > 0) {
            $this->ctr = round(($this->clicks / $this->impressions) * 100, 2);
            $this->save();
        }
    }

    /**
     * Add spending amount
     */
    public function addSpending(float $amount): void
    {
        $this->increment('spent_amount', $amount);
        
        // Check if budget exceeded
        if ($this->spent_amount >= $this->total_budget) {
            $this->update(['status' => 'completed']);
        }
    }

    /**
     * Approve the featured listing
     */
    public function approve(int $adminId): bool
    {
        return $this->update([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => $adminId
        ]);
    }

    /**
     * Reject the featured listing
     */
    public function reject(int $adminId, string $reason): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'approved_at' => now(),
            'approved_by' => $adminId,
            'rejection_reason' => $reason
        ]);
    }

    /**
     * Pause the promotion
     */
    public function pause(): bool
    {
        return $this->update(['status' => 'paused']);
    }

    /**
     * Resume the promotion
     */
    public function resume(): bool
    {
        return $this->update(['status' => 'active']);
    }

    /**
     * Get promotion performance summary
     */
    public function getPerformanceSummary(): array
    {
        return [
            'impressions' => $this->impressions,
            'clicks' => $this->clicks,
            'ctr' => $this->ctr,
            'spent_amount' => $this->spent_amount,
            'remaining_budget' => $this->total_budget - $this->spent_amount,
            'days_remaining' => max(0, now()->diffInDays($this->end_date)),
            'is_active' => $this->isActive(),
            'is_within_budget' => $this->isWithinBudget()
        ];
    }

    /**
     * Scope for active promotions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    /**
     * Scope for promotions by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('promotion_type', $type);
    }

    /**
     * Scope for promotions by listable type
     */
    public function scopeByListableType($query, $type)
    {
        return $query->where('listable_type', $type);
    }

    /**
     * Scope for promotions within budget
     */
    public function scopeWithinBudget($query)
    {
        return $query->whereRaw('spent_amount < total_budget');
    }

    /**
     * Scope for high-performing promotions
     */
    public function scopeHighPerforming($query)
    {
        return $query->where('ctr', '>', 2.0); // CTR > 2%
    }

    /**
     * Get promotion cost per click
     */
    public function getCostPerClick(): float
    {
        return $this->clicks > 0 ? $this->spent_amount / $this->clicks : 0;
    }

    /**
     * Get promotion cost per thousand impressions (CPM)
     */
    public function getCPM(): float
    {
        return $this->impressions > 0 ? ($this->spent_amount / $this->impressions) * 1000 : 0;
    }

    /**
     * Get ROI if the promoted item has sales
     */
    public function getROI(): float
    {
        // This would calculate ROI based on actual sales from the promotion
        // For now, return a placeholder
        return 0.0;
    }

    /**
     * Check if promotion meets targeting criteria
     */
    public function meetsTargeting(array $userCriteria): bool
    {
        if (!$this->targeting) {
            return true; // No targeting means show to everyone
        }

        $targeting = $this->targeting;

        // Check location targeting
        if (isset($targeting['locations']) && !empty($targeting['locations'])) {
            if (!in_array($userCriteria['location'] ?? '', $targeting['locations'])) {
                return false;
            }
        }

        // Check category targeting
        if (isset($targeting['categories']) && !empty($targeting['categories'])) {
            if (!in_array($userCriteria['category'] ?? '', $targeting['categories'])) {
                return false;
            }
        }

        // Check user type targeting
        if (isset($targeting['user_types']) && !empty($targeting['user_types'])) {
            if (!in_array($userCriteria['user_type'] ?? '', $targeting['user_types'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get promotion recommendations for a user
     */
    public static function getRecommendationsForUser(int $userId, string $type = 'product', int $limit = 10): array
    {
        $user = User::find($userId);
        if (!$user) {
            return [];
        }

        $userCriteria = [
            'location' => $user->city,
            'user_type' => $user->role,
            'category' => null // Would be based on user preferences
        ];

        return self::active()
            ->byListableType($type)
            ->withinBudget()
            ->with(['listable', 'user'])
            ->get()
            ->filter(function ($listing) use ($userCriteria) {
                return $listing->meetsTargeting($userCriteria);
            })
            ->take($limit)
            ->toArray();
    }

    /**
     * Get promotion statistics
     */
    public static function getStatistics(): array
    {
        $total = self::count();
        $active = self::active()->count();
        $totalSpent = self::sum('spent_amount');
        $totalImpressions = self::sum('impressions');
        $totalClicks = self::sum('clicks');
        $avgCTR = $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;

        return [
            'total_promotions' => $total,
            'active_promotions' => $active,
            'total_spent' => $totalSpent,
            'total_impressions' => $totalImpressions,
            'total_clicks' => $totalClicks,
            'average_ctr' => round($avgCTR, 2)
        ];
    }
}
