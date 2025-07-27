<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'page_views',
        'unique_page_views',
        'sessions',
        'session_duration',
        'bounce_rate',
        'searches',
        'unique_searches',
        'clicks',
        'unique_clicks',
        'purchases',
        'purchase_value',
        'investments',
        'investment_value',
        'reviews',
        'wishlist_adds',
        'comparisons',
        'featured_listing_views',
        'featured_listing_clicks',
        'recommendation_views',
        'recommendation_clicks',
        'recommendation_purchases',
        'top_pages',
        'top_searches',
        'top_categories',
        'top_products',
        'top_businesses',
        'conversion_funnel',
        'device_breakdown',
        'location_breakdown'
    ];

    protected $casts = [
        'date' => 'date',
        'purchase_value' => 'decimal:2',
        'investment_value' => 'decimal:2',
        'top_pages' => 'array',
        'top_searches' => 'array',
        'top_categories' => 'array',
        'top_products' => 'array',
        'top_businesses' => 'array',
        'conversion_funnel' => 'array',
        'device_breakdown' => 'array',
        'location_breakdown' => 'array'
    ];

    /**
     * Get the user who owns these analytics
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for analytics by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for analytics by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get user engagement score
     */
    public function getEngagementScore(): float
    {
        $score = 0;
        
        // Page views weight: 20%
        $score += min($this->page_views / 10, 1) * 0.2;
        
        // Session duration weight: 25%
        $score += min($this->session_duration / 300, 1) * 0.25; // 5 minutes = 100%
        
        // Interactions weight: 30%
        $interactions = $this->searches + $this->clicks + $this->reviews + $this->wishlist_adds;
        $score += min($interactions / 5, 1) * 0.3;
        
        // Purchases/Investments weight: 25%
        $monetary = $this->purchases + $this->investments;
        $score += min($monetary / 2, 1) * 0.25;
        
        return round($score * 100, 2);
    }

    /**
     * Get conversion rate
     */
    public function getConversionRate(): float
    {
        if ($this->page_views === 0) {
            return 0;
        }
        
        $conversions = $this->purchases + $this->investments;
        return round(($conversions / $this->page_views) * 100, 2);
    }

    /**
     * Get average order value
     */
    public function getAverageOrderValue(): float
    {
        $totalOrders = $this->purchases + $this->investments;
        if ($totalOrders === 0) {
            return 0;
        }
        
        $totalValue = $this->purchase_value + $this->investment_value;
        return round($totalValue / $totalOrders, 2);
    }

    /**
     * Get user lifetime value
     */
    public function getLifetimeValue(): float
    {
        return round($this->purchase_value + $this->investment_value, 2);
    }

    /**
     * Get user behavior insights
     */
    public function getBehaviorInsights(): array
    {
        $insights = [];

        // Engagement insights
        if ($this->page_views > 20) {
            $insights[] = 'High engagement user';
        } elseif ($this->page_views < 5) {
            $insights[] = 'Low engagement user';
        }

        // Session insights
        if ($this->session_duration > 600) { // 10 minutes
            $insights[] = 'Long session duration';
        } elseif ($this->session_duration < 60) { // 1 minute
            $insights[] = 'Short session duration';
        }

        // Bounce rate insights
        if ($this->bounce_rate > 80) {
            $insights[] = 'High bounce rate';
        } elseif ($this->bounce_rate < 30) {
            $insights[] = 'Low bounce rate';
        }

        // Purchase insights
        if ($this->purchases > 0) {
            $insights[] = 'Active purchaser';
        }

        if ($this->investments > 0) {
            $insights[] = 'Active investor';
        }

        // Search insights
        if ($this->searches > 10) {
            $insights[] = 'Frequent searcher';
        }

        // Review insights
        if ($this->reviews > 5) {
            $insights[] = 'Active reviewer';
        }

        return $insights;
    }

    /**
     * Get aggregated analytics for multiple users
     */
    public static function getAggregatedAnalytics($startDate = null, $endDate = null, $userId = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        if ($userId) {
            $query->byUser($userId);
        }

        $analytics = $query->selectRaw('
            SUM(page_views) as total_page_views,
            SUM(unique_page_views) as total_unique_page_views,
            SUM(sessions) as total_sessions,
            AVG(session_duration) as avg_session_duration,
            AVG(bounce_rate) as avg_bounce_rate,
            SUM(searches) as total_searches,
            SUM(clicks) as total_clicks,
            SUM(purchases) as total_purchases,
            SUM(purchase_value) as total_purchase_value,
            SUM(investments) as total_investments,
            SUM(investment_value) as total_investment_value,
            SUM(reviews) as total_reviews,
            SUM(wishlist_adds) as total_wishlist_adds,
            SUM(comparisons) as total_comparisons,
            COUNT(DISTINCT user_id) as unique_users
        ')->first();

        return [
            'total_page_views' => $analytics->total_page_views ?? 0,
            'total_unique_page_views' => $analytics->total_unique_page_views ?? 0,
            'total_sessions' => $analytics->total_sessions ?? 0,
            'avg_session_duration' => round($analytics->avg_session_duration ?? 0, 2),
            'avg_bounce_rate' => round($analytics->avg_bounce_rate ?? 0, 2),
            'total_searches' => $analytics->total_searches ?? 0,
            'total_clicks' => $analytics->total_clicks ?? 0,
            'total_purchases' => $analytics->total_purchases ?? 0,
            'total_purchase_value' => round($analytics->total_purchase_value ?? 0, 2),
            'total_investments' => $analytics->total_investments ?? 0,
            'total_investment_value' => round($analytics->total_investment_value ?? 0, 2),
            'total_reviews' => $analytics->total_reviews ?? 0,
            'total_wishlist_adds' => $analytics->total_wishlist_adds ?? 0,
            'total_comparisons' => $analytics->total_comparisons ?? 0,
            'unique_users' => $analytics->unique_users ?? 0,
            'avg_page_views_per_user' => $analytics->unique_users > 0 ? 
                round($analytics->total_page_views / $analytics->unique_users, 2) : 0,
            'avg_purchase_value' => $analytics->total_purchases > 0 ? 
                round($analytics->total_purchase_value / $analytics->total_purchases, 2) : 0,
            'avg_investment_value' => $analytics->total_investments > 0 ? 
                round($analytics->total_investment_value / $analytics->total_investments, 2) : 0
        ];
    }

    /**
     * Get top performing users
     */
    public static function getTopUsers($startDate = null, $endDate = null, $limit = 10, $metric = 'purchase_value')
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->selectRaw("user_id, SUM($metric) as total_$metric")
            ->groupBy('user_id')
            ->orderBy("total_$metric", 'desc')
            ->limit($limit)
            ->with('user')
            ->get();
    }

    /**
     * Get daily trends
     */
    public static function getDailyTrends($startDate = null, $endDate = null, $userId = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        if ($userId) {
            $query->byUser($userId);
        }

        return $query->selectRaw('
            date,
            SUM(page_views) as page_views,
            SUM(sessions) as sessions,
            SUM(purchases) as purchases,
            SUM(purchase_value) as purchase_value,
            SUM(investments) as investments,
            SUM(investment_value) as investment_value
        ')
        ->groupBy('date')
        ->orderBy('date')
        ->get();
    }
}


    protected $fillable = [
        'user_id',
        'date',
        'page_views',
        'unique_page_views',
        'sessions',
        'session_duration',
        'bounce_rate',
        'searches',
        'unique_searches',
        'clicks',
        'unique_clicks',
        'purchases',
        'purchase_value',
        'investments',
        'investment_value',
        'reviews',
        'wishlist_adds',
        'comparisons',
        'featured_listing_views',
        'featured_listing_clicks',
        'recommendation_views',
        'recommendation_clicks',
        'recommendation_purchases',
        'top_pages',
        'top_searches',
        'top_categories',
        'top_products',
        'top_businesses',
        'conversion_funnel',
        'device_breakdown',
        'location_breakdown'
    ];

    protected $casts = [
        'date' => 'date',
        'purchase_value' => 'decimal:2',
        'investment_value' => 'decimal:2',
        'top_pages' => 'array',
        'top_searches' => 'array',
        'top_categories' => 'array',
        'top_products' => 'array',
        'top_businesses' => 'array',
        'conversion_funnel' => 'array',
        'device_breakdown' => 'array',
        'location_breakdown' => 'array'
    ];

    /**
     * Get the user who owns these analytics
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for analytics by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for analytics by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get user engagement score
     */
    public function getEngagementScore(): float
    {
        $score = 0;
        
        // Page views weight: 20%
        $score += min($this->page_views / 10, 1) * 0.2;
        
        // Session duration weight: 25%
        $score += min($this->session_duration / 300, 1) * 0.25; // 5 minutes = 100%
        
        // Interactions weight: 30%
        $interactions = $this->searches + $this->clicks + $this->reviews + $this->wishlist_adds;
        $score += min($interactions / 5, 1) * 0.3;
        
        // Purchases/Investments weight: 25%
        $monetary = $this->purchases + $this->investments;
        $score += min($monetary / 2, 1) * 0.25;
        
        return round($score * 100, 2);
    }

    /**
     * Get conversion rate
     */
    public function getConversionRate(): float
    {
        if ($this->page_views === 0) {
            return 0;
        }
        
        $conversions = $this->purchases + $this->investments;
        return round(($conversions / $this->page_views) * 100, 2);
    }

    /**
     * Get average order value
     */
    public function getAverageOrderValue(): float
    {
        $totalOrders = $this->purchases + $this->investments;
        if ($totalOrders === 0) {
            return 0;
        }
        
        $totalValue = $this->purchase_value + $this->investment_value;
        return round($totalValue / $totalOrders, 2);
    }

    /**
     * Get user lifetime value
     */
    public function getLifetimeValue(): float
    {
        return round($this->purchase_value + $this->investment_value, 2);
    }

    /**
     * Get user behavior insights
     */
    public function getBehaviorInsights(): array
    {
        $insights = [];

        // Engagement insights
        if ($this->page_views > 20) {
            $insights[] = 'High engagement user';
        } elseif ($this->page_views < 5) {
            $insights[] = 'Low engagement user';
        }

        // Session insights
        if ($this->session_duration > 600) { // 10 minutes
            $insights[] = 'Long session duration';
        } elseif ($this->session_duration < 60) { // 1 minute
            $insights[] = 'Short session duration';
        }

        // Bounce rate insights
        if ($this->bounce_rate > 80) {
            $insights[] = 'High bounce rate';
        } elseif ($this->bounce_rate < 30) {
            $insights[] = 'Low bounce rate';
        }

        // Purchase insights
        if ($this->purchases > 0) {
            $insights[] = 'Active purchaser';
        }

        if ($this->investments > 0) {
            $insights[] = 'Active investor';
        }

        // Search insights
        if ($this->searches > 10) {
            $insights[] = 'Frequent searcher';
        }

        // Review insights
        if ($this->reviews > 5) {
            $insights[] = 'Active reviewer';
        }

        return $insights;
    }

    /**
     * Get aggregated analytics for multiple users
     */
    public static function getAggregatedAnalytics($startDate = null, $endDate = null, $userId = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        if ($userId) {
            $query->byUser($userId);
        }

        $analytics = $query->selectRaw('
            SUM(page_views) as total_page_views,
            SUM(unique_page_views) as total_unique_page_views,
            SUM(sessions) as total_sessions,
            AVG(session_duration) as avg_session_duration,
            AVG(bounce_rate) as avg_bounce_rate,
            SUM(searches) as total_searches,
            SUM(clicks) as total_clicks,
            SUM(purchases) as total_purchases,
            SUM(purchase_value) as total_purchase_value,
            SUM(investments) as total_investments,
            SUM(investment_value) as total_investment_value,
            SUM(reviews) as total_reviews,
            SUM(wishlist_adds) as total_wishlist_adds,
            SUM(comparisons) as total_comparisons,
            COUNT(DISTINCT user_id) as unique_users
        ')->first();

        return [
            'total_page_views' => $analytics->total_page_views ?? 0,
            'total_unique_page_views' => $analytics->total_unique_page_views ?? 0,
            'total_sessions' => $analytics->total_sessions ?? 0,
            'avg_session_duration' => round($analytics->avg_session_duration ?? 0, 2),
            'avg_bounce_rate' => round($analytics->avg_bounce_rate ?? 0, 2),
            'total_searches' => $analytics->total_searches ?? 0,
            'total_clicks' => $analytics->total_clicks ?? 0,
            'total_purchases' => $analytics->total_purchases ?? 0,
            'total_purchase_value' => round($analytics->total_purchase_value ?? 0, 2),
            'total_investments' => $analytics->total_investments ?? 0,
            'total_investment_value' => round($analytics->total_investment_value ?? 0, 2),
            'total_reviews' => $analytics->total_reviews ?? 0,
            'total_wishlist_adds' => $analytics->total_wishlist_adds ?? 0,
            'total_comparisons' => $analytics->total_comparisons ?? 0,
            'unique_users' => $analytics->unique_users ?? 0,
            'avg_page_views_per_user' => $analytics->unique_users > 0 ? 
                round($analytics->total_page_views / $analytics->unique_users, 2) : 0,
            'avg_purchase_value' => $analytics->total_purchases > 0 ? 
                round($analytics->total_purchase_value / $analytics->total_purchases, 2) : 0,
            'avg_investment_value' => $analytics->total_investments > 0 ? 
                round($analytics->total_investment_value / $analytics->total_investments, 2) : 0
        ];
    }

    /**
     * Get top performing users
     */
    public static function getTopUsers($startDate = null, $endDate = null, $limit = 10, $metric = 'purchase_value')
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->selectRaw("user_id, SUM($metric) as total_$metric")
            ->groupBy('user_id')
            ->orderBy("total_$metric", 'desc')
            ->limit($limit)
            ->with('user')
            ->get();
    }

    /**
     * Get daily trends
     */
    public static function getDailyTrends($startDate = null, $endDate = null, $userId = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        if ($userId) {
            $query->byUser($userId);
        }

        return $query->selectRaw('
            date,
            SUM(page_views) as page_views,
            SUM(sessions) as sessions,
            SUM(purchases) as purchases,
            SUM(purchase_value) as purchase_value,
            SUM(investments) as investments,
            SUM(investment_value) as investment_value
        ')
        ->groupBy('date')
        ->orderBy('date')
        ->get();
    }
}
