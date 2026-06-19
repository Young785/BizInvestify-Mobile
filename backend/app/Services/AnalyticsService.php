<?php

namespace App\Services;

use App\Models\User;
use App\Models\Product;
use App\Models\Business;
use App\Models\Investment;
use App\Models\Transaction;
use App\Models\AnalyticsEvent;
use App\Models\UserAnalytics;
use App\Models\Review;
use App\Models\Wishlist;
use App\Models\Comparison;
use App\Models\FeaturedListing;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Support\DatabaseDateExpressions;

class AnalyticsService
{
    /**
     * Get platform overview analytics
     */
    public function getPlatformOverview(string $period = '30d'): array
    {
        $startDate = $this->getStartDate($period);

        return [
            'users' => $this->getUserMetrics($startDate),
            'revenue' => $this->getRevenueMetrics($startDate),
            'engagement' => $this->getEngagementMetrics($startDate),
            'performance' => $this->getPerformanceMetrics($startDate),
            'trends' => $this->getTrendMetrics($startDate)
        ];
    }

    /**
     * Get user analytics
     */
    public function getUserMetrics(Carbon $startDate): array
    {
        $totalUsers = User::count();
        $newUsers = User::where('created_at', '>=', $startDate)->count();
        $activeUsers = $this->getActiveUsers($startDate);
        $verifiedUsers = User::where('email_verified_at', '!=', null)->count();
        $kycVerifiedUsers = User::where('kyc_status', 'verified')->count();

        $userGrowth = $this->calculateGrowthRate(
            User::where('created_at', '>=', $startDate->copy()->subDays(30))->count(),
            User::where('created_at', '>=', $startDate)->count()
        );

        return [
            'total' => $totalUsers,
            'new' => $newUsers,
            'active' => $activeUsers,
            'verified' => $verifiedUsers,
            'kyc_verified' => $kycVerifiedUsers,
            'growth_rate' => $userGrowth,
            'by_role' => $this->getUsersByRole(),
            'by_status' => $this->getUsersByStatus(),
            'registration_trend' => $this->getRegistrationTrend($startDate)
        ];
    }

    /**
     * Get revenue analytics
     */
    public function getRevenueMetrics(Carbon $startDate): array
    {
        $totalRevenue = Transaction::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');

        $previousPeriodRevenue = Transaction::where('status', 'completed')
            ->whereBetween('created_at', [
                $startDate->copy()->subDays(30),
                $startDate
            ])
            ->sum('amount');

        $revenueGrowth = $this->calculateGrowthRate($previousPeriodRevenue, $totalRevenue);

        $transactions = Transaction::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->count();

        $avgTransactionValue = $transactions > 0 ? $totalRevenue / $transactions : 0;

        return [
            'total' => $totalRevenue,
            'growth_rate' => $revenueGrowth,
            'transactions' => $transactions,
            'avg_transaction_value' => $avgTransactionValue,
            'by_type' => $this->getRevenueByType($startDate),
            'by_month' => $this->getRevenueByMonth($startDate),
            'top_products' => $this->getTopRevenueProducts($startDate),
            'top_businesses' => $this->getTopRevenueBusinesses($startDate)
        ];
    }

    /**
     * Get engagement analytics
     */
    public function getEngagementMetrics(Carbon $startDate): array
    {
        $pageViews = AnalyticsEvent::where('event_type', 'page_view')
            ->where('created_at', '>=', $startDate)
            ->count();

        $uniquePageViews = AnalyticsEvent::where('event_type', 'page_view')
            ->where('created_at', '>=', $startDate)
            ->distinct('user_id')
            ->count();

        $sessions = AnalyticsEvent::where('event_type', 'session_start')
            ->where('created_at', '>=', $startDate)
            ->count();

        $avgSessionDuration = $this->calculateAverageSessionDuration($startDate);
        $bounceRate = $this->calculateBounceRate($startDate);

        return [
            'page_views' => $pageViews,
            'unique_page_views' => $uniquePageViews,
            'sessions' => $sessions,
            'avg_session_duration' => $avgSessionDuration,
            'bounce_rate' => $bounceRate,
            'top_pages' => $this->getTopPages($startDate),
            'top_searches' => $this->getTopSearches($startDate),
            'user_behavior' => $this->getUserBehavior($startDate)
        ];
    }

    /**
     * Get performance analytics
     */
    public function getPerformanceMetrics(Carbon $startDate): array
    {
        $conversionRate = $this->calculateConversionRate($startDate);
        $retentionRate = $this->calculateRetentionRate($startDate);
        $churnRate = $this->calculateChurnRate($startDate);

        return [
            'conversion_rate' => $conversionRate,
            'retention_rate' => $retentionRate,
            'churn_rate' => $churnRate,
            'funnel_analysis' => $this->getFunnelAnalysis($startDate),
            'cohort_analysis' => $this->getCohortAnalysis($startDate)
        ];
    }

    /**
     * Get trend analytics
     */
    public function getTrendMetrics(Carbon $startDate): array
    {
        return [
            'user_growth_trend' => $this->getUserGrowthTrend($startDate),
            'revenue_trend' => $this->getRevenueTrend($startDate),
            'engagement_trend' => $this->getEngagementTrend($startDate),
            'seasonal_patterns' => $this->getSeasonalPatterns($startDate)
        ];
    }

    /**
     * Get seller analytics
     */
    public function getSellerAnalytics(int $userId, string $period = '30d'): array
    {
        $startDate = $this->getStartDate($period);

        return [
            'overview' => $this->getSellerOverview($userId, $startDate),
            'products' => $this->getSellerProducts($userId, $startDate),
            'businesses' => $this->getSellerBusinesses($userId, $startDate),
            'revenue' => $this->getSellerRevenue($userId, $startDate),
            'performance' => $this->getSellerPerformance($userId, $startDate)
        ];
    }

    /**
     * Get buyer analytics
     */
    public function getBuyerAnalytics(int $userId, string $period = '30d'): array
    {
        $startDate = $this->getStartDate($period);

        return [
            'overview' => $this->getBuyerOverview($userId, $startDate),
            'purchases' => $this->getBuyerPurchases($userId, $startDate),
            'investments' => $this->getBuyerInvestments($userId, $startDate),
            'behavior' => $this->getBuyerBehavior($userId, $startDate),
            'preferences' => $this->getBuyerPreferences($userId, $startDate)
        ];
    }

    /**
     * Generate custom report
     */
    public function generateCustomReport(array $filters): array
    {
        $query = AnalyticsEvent::query();

        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $data = $query->get();

        return [
            'summary' => $this->generateReportSummary($data),
            'details' => $data,
            'charts' => $this->generateReportCharts($data),
            'insights' => $this->generateReportInsights($data)
        ];
    }

    /**
     * Export analytics data
     */
    public function exportAnalyticsData(array $filters, string $format = 'csv'): string
    {
        $data = $this->generateCustomReport($filters);

        switch ($format) {
            case 'csv':
                return $this->exportToCsv($data['details']);
            case 'json':
                return json_encode($data, JSON_PRETTY_PRINT);
            case 'excel':
                return $this->exportToExcel($data['details']);
            default:
                throw new \InvalidArgumentException('Unsupported export format');
        }
    }

    // Private helper methods

    private function getStartDate(string $period): Carbon
    {
        return match ($period) {
            '7d' => Carbon::now()->subDays(7),
            '30d' => Carbon::now()->subDays(30),
            '90d' => Carbon::now()->subDays(90),
            '1y' => Carbon::now()->subYear(),
            default => Carbon::now()->subDays(30)
        };
    }

    private function getActiveUsers(Carbon $startDate): int
    {
        return UserAnalytics::where('date', '>=', $startDate->toDateString())
            ->where('sessions', '>', 0)
            ->distinct('user_id')
            ->count();
    }

    private function calculateGrowthRate(float $oldValue, float $newValue): float
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        return (($newValue - $oldValue) / $oldValue) * 100;
    }

    private function getUsersByRole(): array
    {
        return User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();
    }

    private function getUsersByStatus(): array
    {
        return User::select('kyc_status', DB::raw('count(*) as count'))
            ->groupBy('kyc_status')
            ->pluck('count', 'kyc_status')
            ->toArray();
    }

    private function getRegistrationTrend(Carbon $startDate): array
    {
        return User::select(
            DatabaseDateExpressions::dateKey(),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }

    private function getRevenueByType(Carbon $startDate): array
    {
        return Transaction::select('type', DB::raw('sum(amount) as total'))
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();
    }

    private function getRevenueByMonth(Carbon $startDate): array
    {
        return Transaction::select(
            DatabaseDateExpressions::monthKey(),
            DB::raw('sum(amount) as total')
        )
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();
    }

    private function getTopRevenueProducts(Carbon $startDate): array
    {
        return Product::select('products.title', DB::raw('sum(transactions.amount) as revenue'))
            ->join('transactions', function ($join) {
                $join->on('products.id', '=', 'transactions.listing_id')
                    ->where('transactions.listing_type', '=', 'product');
            })
            ->where('transactions.status', 'completed')
            ->where('transactions.created_at', '>=', $startDate)
            ->groupBy('products.id', 'products.title')
            ->orderBy('revenue', 'desc')
            ->limit(10)
            ->pluck('revenue', 'title')
            ->toArray();
    }

    private function getTopRevenueBusinesses(Carbon $startDate): array
    {
        return Business::select('businesses.name', DB::raw('sum(transactions.amount) as revenue'))
            ->join('transactions', function ($join) {
                $join->on('businesses.id', '=', 'transactions.listing_id')
                    ->where('transactions.listing_type', '=', 'business');
            })
            ->where('transactions.status', 'completed')
            ->where('transactions.created_at', '>=', $startDate)
            ->groupBy('businesses.id', 'businesses.name')
            ->orderBy('revenue', 'desc')
            ->limit(10)
            ->pluck('revenue', 'name')
            ->toArray();
    }

    private function calculateAverageSessionDuration(Carbon $startDate): float
    {
        $sessions = AnalyticsEvent::where('event_type', 'session_start')
            ->where('created_at', '>=', $startDate)
            ->get();

        if ($sessions->isEmpty()) {
            return 0;
        }

        $totalDuration = 0;
        $sessionCount = 0;

        foreach ($sessions as $session) {
            $endEvent = AnalyticsEvent::where('event_type', 'session_end')
                ->where('session_id', $session->session_id)
                ->first();

            if ($endEvent) {
                $duration = $endEvent->created_at->diffInSeconds($session->created_at);
                $totalDuration += $duration;
                $sessionCount++;
            }
        }

        return $sessionCount > 0 ? $totalDuration / $sessionCount : 0;
    }

    private function calculateBounceRate(Carbon $startDate): float
    {
        $totalSessions = AnalyticsEvent::where('event_type', 'session_start')
            ->where('created_at', '>=', $startDate)
            ->count();

        $bounceSessions = AnalyticsEvent::where('event_type', 'session_start')
            ->where('created_at', '>=', $startDate)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('analytics_events as ae2')
                    ->whereRaw('ae2.session_id = analytics_events.session_id')
                    ->where('ae2.event_type', '!=', 'session_start')
                    ->where('ae2.event_type', '!=', 'session_end');
            })
            ->count();

        return $totalSessions > 0 ? ($bounceSessions / $totalSessions) * 100 : 0;
    }

    private function getTopPages(Carbon $startDate): array
    {
        return AnalyticsEvent::select('page_url', DB::raw('count(*) as views'))
            ->where('event_type', 'page_view')
            ->where('created_at', '>=', $startDate)
            ->groupBy('page_url')
            ->orderBy('views', 'desc')
            ->limit(10)
            ->pluck('views', 'page_url')
            ->toArray();
    }

    private function getTopSearches(Carbon $startDate): array
    {
        return AnalyticsEvent::select('event_label', DB::raw('count(*) as searches'))
            ->where('event_type', 'search')
            ->where('created_at', '>=', $startDate)
            ->groupBy('event_label')
            ->orderBy('searches', 'desc')
            ->limit(10)
            ->pluck('searches', 'event_label')
            ->toArray();
    }

    private function getUserBehavior(Carbon $startDate): array
    {
        return [
            'device_breakdown' => $this->getDeviceBreakdown($startDate),
            'browser_breakdown' => $this->getBrowserBreakdown($startDate),
            'location_breakdown' => $this->getLocationBreakdown($startDate)
        ];
    }

    private function getDeviceBreakdown(Carbon $startDate): array
    {
        return AnalyticsEvent::select('device_type', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('device_type')
            ->pluck('count', 'device_type')
            ->toArray();
    }

    private function getBrowserBreakdown(Carbon $startDate): array
    {
        return AnalyticsEvent::select('browser', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('browser')
            ->pluck('count', 'browser')
            ->toArray();
    }

    private function getLocationBreakdown(Carbon $startDate): array
    {
        return AnalyticsEvent::select('country', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('country')
            ->pluck('count', 'country')
            ->toArray();
    }

    private function calculateConversionRate(Carbon $startDate): float
    {
        $visitors = AnalyticsEvent::where('event_type', 'page_view')
            ->where('created_at', '>=', $startDate)
            ->distinct('user_id')
            ->count();

        $conversions = Transaction::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->distinct('user_id')
            ->count();

        return $visitors > 0 ? ($conversions / $visitors) * 100 : 0;
    }

    private function calculateRetentionRate(Carbon $startDate): float
    {
        // Implementation for retention rate calculation
        return 0.0; // Placeholder
    }

    private function calculateChurnRate(Carbon $startDate): float
    {
        // Implementation for churn rate calculation
        return 0.0; // Placeholder
    }

    private function getFunnelAnalysis(Carbon $startDate): array
    {
        return [
            'page_views' => AnalyticsEvent::where('event_type', 'page_view')
                ->where('created_at', '>=', $startDate)->count(),
            'product_views' => AnalyticsEvent::where('event_type', 'product_view')
                ->where('created_at', '>=', $startDate)->count(),
            'add_to_cart' => AnalyticsEvent::where('event_type', 'add_to_cart')
                ->where('created_at', '>=', $startDate)->count(),
            'purchases' => Transaction::where('status', 'completed')
                ->where('created_at', '>=', $startDate)->count()
        ];
    }

    private function getCohortAnalysis(Carbon $startDate): array
    {
        // Implementation for cohort analysis
        return []; // Placeholder
    }

    private function getSellerOverview(int $userId, Carbon $startDate): array
    {
        $products = Product::where('seller_id', $userId)->count();
        $businesses = Business::where('seller_id', $userId)->count();
        $revenue = Transaction::where('seller_id', $userId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');

        return [
            'total_products' => $products,
            'total_businesses' => $businesses,
            'total_revenue' => $revenue,
            'active_listings' => $this->getActiveListings($userId)
        ];
    }

    private function getActiveListings(int $userId): int
    {
        return Product::where('seller_id', $userId)
            ->where('status', 'active')
            ->count() +
            Business::where('seller_id', $userId)
                ->where('status', 'active')
                ->count();
    }

    private function getSellerProducts(int $userId, Carbon $startDate): array
    {
        return Product::where('seller_id', $userId)
            ->with([
                'transactions' => function ($query) {
                    $query->where('status', 'completed');
                }
            ])
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'views' => $product->views_count ?? 0,
                    'purchases' => $product->transactions->count(),
                    'revenue' => $product->transactions->sum('amount')
                ];
            })
            ->toArray();
    }

    private function getSellerBusinesses(int $userId, Carbon $startDate): array
    {
        return Business::where('seller_id', $userId)
            ->with([
                'transactions' => function ($query) {
                    $query->where('status', 'completed');
                }
            ])
            ->get()
            ->map(function ($business) {
                return [
                    'id' => $business->id,
                    'name' => $business->name,
                    'views' => $business->views_count ?? 0,
                    'investments' => $business->transactions->count(),
                    'revenue' => $business->transactions->sum('amount')
                ];
            })
            ->toArray();
    }

    private function getSellerRevenue(int $userId, Carbon $startDate): array
    {
        return Transaction::where('seller_id', $userId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->select(
                DatabaseDateExpressions::dateKey(),
                DB::raw('sum(amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue', 'date')
            ->toArray();
    }

    private function getSellerPerformance(int $userId, Carbon $startDate): array
    {
        return [
            'conversion_rate' => $this->calculateSellerConversionRate($userId, $startDate),
            'avg_order_value' => $this->calculateSellerAvgOrderValue($userId, $startDate),
            'customer_satisfaction' => $this->calculateSellerSatisfaction($userId, $startDate)
        ];
    }

    private function getBuyerOverview(int $userId, Carbon $startDate): array
    {
        $purchases = Transaction::where('buyer_id', $userId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->count();

        $totalSpent = Transaction::where('buyer_id', $userId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');

        return [
            'total_purchases' => $purchases,
            'total_spent' => $totalSpent,
            'avg_order_value' => $purchases > 0 ? $totalSpent / $purchases : 0,
            'favorite_categories' => $this->getBuyerFavoriteCategories($userId, $startDate)
        ];
    }

    private function getBuyerPurchases(int $userId, Carbon $startDate): array
    {
        return Transaction::where('buyer_id', $userId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->with(['product', 'business'])
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'item_name' => $transaction->product ? $transaction->product->title : $transaction->business->name,
                    'date' => $transaction->created_at->format('Y-m-d')
                ];
            })
            ->toArray();
    }

    private function getBuyerInvestments(int $userId, Carbon $startDate): array
    {
        return Investment::where('investor_id', $userId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->with('business')
            ->get()
            ->map(function ($investment) {
                return [
                    'id' => $investment->id,
                    'business_name' => $investment->business->name ?? 'Unknown',
                    'amount' => $investment->amount,
                    'equity_percentage' => $investment->equity_percentage,
                    'date' => $investment->created_at
                ];
            })
            ->toArray();
    }

    private function getBuyerBehavior(int $userId, Carbon $startDate): array
    {
        return [
            'page_views' => AnalyticsEvent::where('user_id', $userId)
                ->where('event_type', 'page_view')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'searches' => AnalyticsEvent::where('user_id', $userId)
                ->where('event_type', 'search')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'wishlist_adds' => Wishlist::where('user_id', $userId)
                ->where('created_at', '>=', $startDate)
                ->count(),
            'comparisons' => Comparison::where('user_id', $userId)
                ->where('created_at', '>=', $startDate)
                ->count()
        ];
    }

    private function getBuyerPreferences(int $userId, Carbon $startDate): array
    {
        return [
            'top_categories' => $this->getBuyerTopCategories($userId, $startDate),
            'top_sellers' => $this->getBuyerTopSellers($userId, $startDate),
            'price_range' => $this->getBuyerPriceRange($userId, $startDate)
        ];
    }

    private function getBuyerTopCategories(int $userId, Carbon $startDate): array
    {
        return Product::join('transactions', function ($join) {
            $join->on('products.id', '=', 'transactions.listing_id')
                ->where('transactions.listing_type', '=', 'product');
        })
            ->where('transactions.buyer_id', $userId)
            ->where('transactions.status', 'completed')
            ->where('transactions.created_at', '>=', $startDate)
            ->select('products.category', DB::raw('count(*) as purchases'))
            ->groupBy('products.category')
            ->orderBy('purchases', 'desc')
            ->limit(5)
            ->pluck('purchases', 'category')
            ->toArray();
    }

    private function getBuyerTopSellers(int $userId, Carbon $startDate): array
    {
        return Transaction::where('transactions.buyer_id', $userId)
            ->where('transactions.status', 'completed')
            ->where('transactions.created_at', '>=', $startDate)
            ->join('users', 'transactions.seller_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as purchases'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('purchases', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'purchases' => $item->purchases
                ];
            })
            ->toArray();
    }

    private function getBuyerPriceRange(int $userId, Carbon $startDate): array
    {
        $transactions = Transaction::where('buyer_id', $userId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->pluck('amount');

        if ($transactions->isEmpty()) {
            return ['min' => 0, 'max' => 0, 'avg' => 0];
        }

        return [
            'min' => $transactions->min(),
            'max' => $transactions->max(),
            'avg' => $transactions->avg()
        ];
    }

    private function calculateSellerConversionRate(int $userId, Carbon $startDate): float
    {
        $views = AnalyticsEvent::where('event_type', 'product_view')
            ->where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->count();

        $purchases = Transaction::where('seller_id', $userId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->count();

        return $views > 0 ? ($purchases / $views) * 100 : 0;
    }

    private function calculateSellerAvgOrderValue(int $userId, Carbon $startDate): float
    {
        $transactions = Transaction::where('seller_id', $userId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate);

        $totalRevenue = $transactions->sum('amount');
        $orderCount = $transactions->count();

        return $orderCount > 0 ? $totalRevenue / $orderCount : 0;
    }

    private function calculateSellerSatisfaction(int $userId, Carbon $startDate): float
    {
        return Review::whereHas('reviewable', function ($query) use ($userId) {
            $query->where('reviewable_type', 'App\\Models\\Product')
                ->where('seller_id', $userId);
        })
            ->where('created_at', '>=', $startDate)
            ->avg('rating') ?? 0;
    }

    private function generateReportSummary(Collection $data): array
    {
        return [
            'total_events' => $data->count(),
            'unique_users' => $data->unique('user_id')->count(),
            'date_range' => [
                'start' => $data->min('created_at'),
                'end' => $data->max('created_at')
            ]
        ];
    }

    private function generateReportCharts(Collection $data): array
    {
        return [
            'events_by_type' => $data->groupBy('event_type')->map->count(),
            'events_by_date' => $data->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            })->map->count()
        ];
    }

    private function generateReportInsights(Collection $data): array
    {
        return [
            'most_active_users' => $data->groupBy('user_id')->map->count()->sortDesc()->take(5),
            'most_common_events' => $data->groupBy('event_type')->map->count()->sortDesc()->take(5)
        ];
    }

    private function exportToCsv(Collection $data): string
    {
        $headers = ['id', 'user_id', 'event_type', 'event_category', 'event_action', 'created_at'];
        $csv = implode(',', $headers) . "\n";

        foreach ($data as $row) {
            $csv .= implode(',', [
                $row->id,
                $row->user_id,
                $row->event_type,
                $row->event_category,
                $row->event_action,
                $row->created_at
            ]) . "\n";
        }

        return $csv;
    }

    private function exportToExcel(Collection $data): string
    {
        // Implementation for Excel export
        return $this->exportToCsv($data); // Fallback to CSV for now
    }

    private function getUserGrowthTrend(Carbon $startDate): array
    {
        return User::select(
            DatabaseDateExpressions::dateKey(),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }

    private function getRevenueTrend(Carbon $startDate): array
    {
        return Transaction::select(
            DatabaseDateExpressions::dateKey(),
            DB::raw('sum(amount) as revenue')
        )
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue', 'date')
            ->toArray();
    }

    private function getEngagementTrend(Carbon $startDate): array
    {
        return AnalyticsEvent::select(
            DatabaseDateExpressions::dateKey(),
            DB::raw('count(*) as events')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('events', 'date')
            ->toArray();
    }

    private function getSeasonalPatterns(Carbon $startDate): array
    {
        return [
            'daily_pattern' => $this->getDailyPattern($startDate),
            'weekly_pattern' => $this->getWeeklyPattern($startDate),
            'monthly_pattern' => $this->getMonthlyPattern($startDate)
        ];
    }

    private function getDailyPattern(Carbon $startDate): array
    {
        return AnalyticsEvent::select(
            DatabaseDateExpressions::hour(),
            DB::raw('count(*) as events')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('events', 'hour')
            ->toArray();
    }

    private function getWeeklyPattern(Carbon $startDate): array
    {
        return AnalyticsEvent::select(
            DatabaseDateExpressions::dayOfWeek(),
            DB::raw('count(*) as events')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('events', 'day')
            ->toArray();
    }

    private function getMonthlyPattern(Carbon $startDate): array
    {
        return AnalyticsEvent::select(
            DatabaseDateExpressions::monthNumber(),
            DB::raw('count(*) as events')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('events', 'month')
            ->toArray();
    }

    private function getBuyerFavoriteCategories(int $userId, Carbon $startDate): array
    {
        return Product::join('transactions', function ($join) {
            $join->on('products.id', '=', 'transactions.listing_id')
                ->where('transactions.listing_type', '=', 'product');
        })
            ->where('transactions.buyer_id', $userId)
            ->where('transactions.status', 'completed')
            ->where('transactions.created_at', '>=', $startDate)
            ->select('products.category', DB::raw('count(*) as purchases'))
            ->groupBy('products.category')
            ->orderBy('purchases', 'desc')
            ->limit(3)
            ->pluck('purchases', 'category')
            ->toArray();
    }
}