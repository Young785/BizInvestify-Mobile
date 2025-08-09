<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Business;
use App\Models\User;
use App\Models\Review;
use App\Models\Wishlist;
use App\Models\Transaction;
use App\Models\Investment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MarketplaceService
{
    /**
     * Get marketplace overview statistics
     */
    public function getMarketplaceStats(): array
    {
        $stats = [
            'total_products' => Product::where('status', 'active')->count(),
            'total_businesses' => Business::where('status', 'active')->count(),
            'total_users' => User::where('is_verified', true)->count(),
            'total_transactions' => Transaction::where('status', 'completed')->count(),
            'total_investments' => Investment::where('status', 'completed')->count(),
            'total_reviews' => Review::approved()->count(),
            'total_wishlists' => Wishlist::count(),
        ];

        // Calculate revenue statistics
        $revenueStats = $this->getRevenueStats();
        $stats = array_merge($stats, $revenueStats);

        // Get trending data
        $trendingStats = $this->getTrendingStats();
        $stats = array_merge($stats, $trendingStats);

        return $stats;
    }

    /**
     * Get revenue statistics
     */
    private function getRevenueStats(): array
    {
        $totalRevenue = Transaction::where('status', 'completed')
            ->sum('amount');

        $monthlyRevenue = Transaction::where('status', 'completed')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('amount');

        $commissionRevenue = Transaction::where('status', 'completed')
            ->sum('commission_amount');

        return [
            'total_revenue' => $totalRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'commission_revenue' => $commissionRevenue,
            'average_transaction_value' => Transaction::where('status', 'completed')
                ->avg('amount') ?? 0,
        ];
    }

    /**
     * Get trending statistics
     */
    private function getTrendingStats(): array
    {
        $trendingProducts = Product::where('status', 'active')
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'views_count', 'average_rating']);

        $trendingBusinesses = Business::where('status', 'active')
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'views_count', 'valuation']);

        $topCategories = DB::table('products')
            ->where('status', 'active')
            ->select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $topIndustries = DB::table('businesses')
            ->where('status', 'active')
            ->select('industry', DB::raw('COUNT(*) as count'))
            ->groupBy('industry')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return [
            'trending_products' => $trendingProducts,
            'trending_businesses' => $trendingBusinesses,
            'top_categories' => $topCategories,
            'top_industries' => $topIndustries,
        ];
    }

    /**
     * Get personalized recommendations for a user
     */
    public function getPersonalizedRecommendations(int $userId, string $type = 'product', int $limit = 10): array
    {
        $user = User::find($userId);
        if (!$user) {
            return ['data' => [], 'reason' => 'User not found'];
        }

        if ($type === 'product') {
            return $this->getProductRecommendations($user, $limit);
        } else {
            return $this->getBusinessRecommendations($user, $limit);
        }
    }

    /**
     * Get product recommendations based on user behavior
     */
    private function getProductRecommendations(User $user, int $limit): array
    {
        // Get user's purchase history
        $purchasedCategories = Transaction::where('buyer_id', $user->id)
            ->where('status', 'completed')
            ->where('listing_type', 'product')
            ->join('products', 'transactions.listing_id', '=', 'products.id')
            ->select('products.category')
            ->distinct()
            ->pluck('category');

        // Get user's wishlist categories
        $wishlistCategories = Wishlist::where('user_id', $user->id)
            ->where('wishlistable_type', 'product')
            ->join('products', 'wishlists.wishlistable_id', '=', 'products.id')
            ->select('products.category')
            ->distinct()
            ->pluck('category');

        // Get user's reviewed categories
        $reviewedCategories = Review::where('user_id', $user->id)
            ->where('reviewable_type', 'product')
            ->join('products', 'reviews.reviewable_id', '=', 'products.id')
            ->select('products.category')
            ->distinct()
            ->pluck('category');

        // Combine all categories
        $preferredCategories = $purchasedCategories
            ->merge($wishlistCategories)
            ->merge($reviewedCategories)
            ->unique()
            ->filter();

        if ($preferredCategories->isEmpty()) {
            // If no preferences, return trending products
            $recommendations = Product::where('status', 'active')
                ->where('user_id', '!=', $user->id)
                ->orderBy('views_count', 'desc')
                ->orderBy('average_rating', 'desc')
                ->limit($limit)
                ->with(['user'])
                ->get();

            return [
                'data' => $recommendations,
                'reason' => 'Based on trending products'
            ];
        }

        // Get recommendations based on preferred categories
        $recommendations = Product::where('status', 'active')
            ->where('user_id', '!=', $user->id)
            ->whereIn('category', $preferredCategories)
            ->orderBy('average_rating', 'desc')
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->with(['user'])
            ->get();

        return [
            'data' => $recommendations,
            'reason' => 'Based on your interests in ' . $preferredCategories->take(3)->implode(', ')
        ];
    }

    /**
     * Get business recommendations based on user behavior
     */
    private function getBusinessRecommendations(User $user, int $limit): array
    {
        // Get user's investment history
        $investedIndustries = Investment::where('investor_id', $user->id)
            ->where('status', 'completed')
            ->join('businesses', 'investments.business_id', '=', 'businesses.id')
            ->select('businesses.industry')
            ->distinct()
            ->pluck('industry');

        // Get user's wishlist industries
        $wishlistIndustries = Wishlist::where('user_id', $user->id)
            ->where('wishlistable_type', 'business')
            ->join('businesses', 'wishlists.wishlistable_id', '=', 'businesses.id')
            ->select('businesses.industry')
            ->distinct()
            ->pluck('industry');

        // Get user's reviewed industries
        $reviewedIndustries = Review::where('user_id', $user->id)
            ->where('reviewable_type', 'business')
            ->join('businesses', 'reviews.reviewable_id', '=', 'businesses.id')
            ->select('businesses.industry')
            ->distinct()
            ->pluck('industry');

        // Combine all industries
        $preferredIndustries = $investedIndustries
            ->merge($wishlistIndustries)
            ->merge($reviewedIndustries)
            ->unique()
            ->filter();

        if ($preferredIndustries->isEmpty()) {
            // If no preferences, return trending businesses
            $recommendations = Business::where('status', 'active')
                ->where('user_id', '!=', $user->id)
                ->orderBy('views_count', 'desc')
                ->orderBy('valuation', 'desc')
                ->limit($limit)
                ->with(['user'])
                ->get();

            return [
                'data' => $recommendations,
                'reason' => 'Based on trending businesses'
            ];
        }

        // Get recommendations based on preferred industries
        $recommendations = Business::where('status', 'active')
            ->where('user_id', '!=', $user->id)
            ->whereIn('industry', $preferredIndustries)
            ->orderBy('valuation', 'desc')
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->with(['user'])
            ->get();

        return [
            'data' => $recommendations,
            'reason' => 'Based on your interests in ' . $preferredIndustries->take(3)->implode(', ')
        ];
    }

    /**
     * Get featured listings
     */
    public function getFeaturedListings(string $type = 'product', int $limit = 10): Collection
    {
        if ($type === 'product') {
            return Product::where('status', 'active')
                ->where('is_featured', true)
                ->orderBy('featured_at', 'desc')
                ->orderBy('views_count', 'desc')
                ->limit($limit)
                ->with(['user'])
                ->get();
        } else {
            return Business::where('status', 'active')
                ->where('is_featured', true)
                ->orderBy('featured_at', 'desc')
                ->orderBy('views_count', 'desc')
                ->limit($limit)
                ->with(['user'])
                ->get();
        }
    }

    /**
     * Get marketplace analytics for admin
     */
    public function getMarketplaceAnalytics(Request $request): array
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period);

        $analytics = [
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'end_date' => now()->toDateString(),
        ];

        // User growth
        $analytics['user_growth'] = $this->getUserGrowth($startDate);

        // Transaction analytics
        $analytics['transaction_analytics'] = $this->getTransactionAnalytics($startDate);

        // Product analytics
        $analytics['product_analytics'] = $this->getProductAnalytics($startDate);

        // Business analytics
        $analytics['business_analytics'] = $this->getBusinessAnalytics($startDate);

        // Revenue analytics
        $analytics['revenue_analytics'] = $this->getRevenueAnalytics($startDate);

        return $analytics;
    }

    /**
     * Get user growth analytics
     */
    private function getUserGrowth($startDate): array
    {
        $totalUsers = User::where('created_at', '>=', $startDate)->count();
        $activeUsers = User::where('is_verified', true)
            ->where('created_at', '>=', $startDate)
            ->count();

        $verifiedUsers = User::where('kyc_status', 'verified')
            ->where('created_at', '>=', $startDate)
            ->count();

        $dailyRegistrations = User::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'verified_users' => $verifiedUsers,
            'daily_registrations' => $dailyRegistrations,
        ];
    }

    /**
     * Get transaction analytics
     */
    private function getTransactionAnalytics($startDate): array
    {
        $totalTransactions = Transaction::where('created_at', '>=', $startDate)->count();
        $completedTransactions = Transaction::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->count();

        $transactionTypes = Transaction::where('created_at', '>=', $startDate)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        $dailyTransactions = Transaction::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'total_transactions' => $totalTransactions,
            'completed_transactions' => $completedTransactions,
            'completion_rate' => $totalTransactions > 0 ? ($completedTransactions / $totalTransactions) * 100 : 0,
            'transaction_types' => $transactionTypes,
            'daily_transactions' => $dailyTransactions,
        ];
    }

    /**
     * Get product analytics
     */
    private function getProductAnalytics($startDate): array
    {
        $totalProducts = Product::where('created_at', '>=', $startDate)->count();
        $activeProducts = Product::where('status', 'active')
            ->where('created_at', '>=', $startDate)
            ->count();

        $topCategories = Product::where('created_at', '>=', $startDate)
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $averagePrice = Product::where('status', 'active')
            ->where('created_at', '>=', $startDate)
            ->avg('price');

        return [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'top_categories' => $topCategories,
            'average_price' => $averagePrice,
        ];
    }

    /**
     * Get business analytics
     */
    private function getBusinessAnalytics($startDate): array
    {
        $totalBusinesses = Business::where('created_at', '>=', $startDate)->count();
        $activeBusinesses = Business::where('status', 'active')
            ->where('created_at', '>=', $startDate)
            ->count();

        $topIndustries = Business::where('created_at', '>=', $startDate)
            ->selectRaw('industry, COUNT(*) as count')
            ->groupBy('industry')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $totalValuation = Business::where('status', 'active')
            ->where('created_at', '>=', $startDate)
            ->sum('valuation');

        $averageValuation = Business::where('status', 'active')
            ->where('created_at', '>=', $startDate)
            ->avg('valuation');

        return [
            'total_businesses' => $totalBusinesses,
            'active_businesses' => $activeBusinesses,
            'top_industries' => $topIndustries,
            'total_valuation' => $totalValuation,
            'average_valuation' => $averageValuation,
        ];
    }

    /**
     * Get revenue analytics
     */
    private function getRevenueAnalytics($startDate): array
    {
        $totalRevenue = Transaction::where('status', 'completed')
            ->whereIn('type', ['purchase', 'investment'])
            ->where('created_at', '>=', $startDate)
            ->sum('amount');

        $commissionRevenue = Transaction::where('status', 'completed')
            ->where('type', 'commission')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');

        $dailyRevenue = Transaction::where('status', 'completed')
            ->whereIn('type', ['purchase', 'investment'])
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $revenueByType = Transaction::where('status', 'completed')
            ->whereIn('type', ['purchase', 'investment'])
            ->where('created_at', '>=', $startDate)
            ->selectRaw('type, SUM(amount) as revenue')
            ->groupBy('type')
            ->get();

        return [
            'total_revenue' => $totalRevenue,
            'commission_revenue' => $commissionRevenue,
            'daily_revenue' => $dailyRevenue,
            'revenue_by_type' => $revenueByType,
        ];
    }

    /**
     * Get search insights
     */
    public function getSearchInsights(Request $request): array
    {
        $period = $request->get('period', '30');
        $startDate = now()->subDays($period);

        $insights = [
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'end_date' => now()->toDateString(),
        ];

        // Most searched terms
        $insights['popular_searches'] = DB::table('search_logs')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('query, COUNT(*) as count')
            ->groupBy('query')
            ->orderBy('count', 'desc')
            ->limit(20)
            ->get();

        // Search trends by type
        $insights['search_by_type'] = DB::table('search_logs')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        // Search success rate
        $totalSearches = DB::table('search_logs')
            ->where('created_at', '>=', $startDate)
            ->count();

        $successfulSearches = DB::table('search_logs')
            ->where('created_at', '>=', $startDate)
            ->where('results_count', '>', 0)
            ->count();

        $insights['search_success_rate'] = $totalSearches > 0 ? ($successfulSearches / $totalSearches) * 100 : 0;

        return $insights;
    }

    /**
     * Get marketplace health metrics
     */
    public function getMarketplaceHealth(): array
    {
        $health = [
            'overall_score' => 0,
            'metrics' => [],
        ];

        // User engagement
        $activeUsers = User::where('is_verified', true)->count();
        $totalUsers = User::count();
        $userEngagement = $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0;
        $health['metrics']['user_engagement'] = $userEngagement;

        // Transaction success rate
        $totalTransactions = Transaction::count();
        $completedTransactions = Transaction::where('status', 'completed')->count();
        $transactionSuccess = $totalTransactions > 0 ? ($completedTransactions / $totalTransactions) * 100 : 0;
        $health['metrics']['transaction_success'] = $transactionSuccess;

        // Content quality
        $productsWithImages = Product::where('status', 'active')
            ->whereNotNull('images')
            ->count();
        $totalProducts = Product::where('status', 'active')->count();
        $contentQuality = $totalProducts > 0 ? ($productsWithImages / $totalProducts) * 100 : 0;
        $health['metrics']['content_quality'] = $contentQuality;

        // User verification
        $verifiedUsers = User::where('kyc_status', 'verified')->count();
        $verificationRate = $totalUsers > 0 ? ($verifiedUsers / $totalUsers) * 100 : 0;
        $health['metrics']['verification_rate'] = $verificationRate;

        // Calculate overall score
        $health['overall_score'] = array_sum($health['metrics']) / count($health['metrics']);

        return $health;
    }
} 