<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'event_type',
        'event_category',
        'event_action',
        'event_label',
        'event_data',
        'page_url',
        'referrer',
        'user_agent',
        'ip_address',
        'country',
        'city',
        'device_type',
        'browser',
        'os',
        'value'
    ];

    protected $casts = [
        'event_data' => 'array',
        'value' => 'decimal:2'
    ];

    /**
     * Get the user who triggered this event
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for events by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope for events by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('event_category', $category);
    }

    /**
     * Scope for events by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for events by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for events by session
     */
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Get event summary statistics
     */
    public static function getEventSummary($startDate = null, $endDate = null, $userId = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        if ($userId) {
            $query->byUser($userId);
        }

        return [
            'total_events' => $query->count(),
            'unique_users' => $query->distinct('user_id')->count('user_id'),
            'unique_sessions' => $query->distinct('session_id')->count('session_id'),
            'events_by_type' => $query->selectRaw('event_type, COUNT(*) as count')
                ->groupBy('event_type')
                ->pluck('count', 'event_type')
                ->toArray(),
            'events_by_category' => $query->selectRaw('event_category, COUNT(*) as count')
                ->groupBy('event_category')
                ->pluck('count', 'event_category')
                ->toArray(),
            'total_value' => $query->sum('value'),
            'average_value' => $query->avg('value')
        ];
    }

    /**
     * Get conversion funnel data
     */
    public static function getConversionFunnel($startDate = null, $endDate = null, $userId = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        if ($userId) {
            $query->byUser($userId);
        }

        $funnel = [
            'page_views' => $query->byType('page_view')->count(),
            'searches' => $query->byType('search')->count(),
            'clicks' => $query->byType('click')->count(),
            'purchases' => $query->byType('purchase')->count(),
            'investments' => $query->byType('investment')->count()
        ];

        // Calculate conversion rates
        $funnel['search_to_view_rate'] = $funnel['page_views'] > 0 ? 
            ($funnel['searches'] / $funnel['page_views']) * 100 : 0;
        $funnel['click_to_search_rate'] = $funnel['searches'] > 0 ? 
            ($funnel['clicks'] / $funnel['searches']) * 100 : 0;
        $funnel['purchase_to_click_rate'] = $funnel['clicks'] > 0 ? 
            ($funnel['purchases'] / $funnel['clicks']) * 100 : 0;

        return $funnel;
    }

    /**
     * Get geographic distribution
     */
    public static function getGeographicDistribution($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->selectRaw('country, city, COUNT(*) as count')
            ->whereNotNull('country')
            ->groupBy('country', 'city')
            ->orderBy('count', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Get device breakdown
     */
    public static function getDeviceBreakdown($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->selectRaw('device_type, browser, os, COUNT(*) as count')
            ->whereNotNull('device_type')
            ->groupBy('device_type', 'browser', 'os')
            ->orderBy('count', 'desc')
            ->get();
    }

    /**
     * Get top performing pages
     */
    public static function getTopPages($startDate = null, $endDate = null, $limit = 10)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->selectRaw('page_url, COUNT(*) as views, COUNT(DISTINCT user_id) as unique_users')
            ->byType('page_view')
            ->whereNotNull('page_url')
            ->groupBy('page_url')
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top search terms
     */
    public static function getTopSearches($startDate = null, $endDate = null, $limit = 10)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        return $query->selectRaw('event_label as search_term, COUNT(*) as searches')
            ->byType('search')
            ->whereNotNull('event_label')
            ->groupBy('event_label')
            ->orderBy('searches', 'desc')
            ->limit($limit)
            ->get();
    }
}
