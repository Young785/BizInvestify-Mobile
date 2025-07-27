<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Business;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchService
{
    /**
     * Search products with advanced filtering
     */
    public function searchProducts(Request $request): array
    {
        $query = Product::with(['user', 'category'])
            ->where('status', 'active');

        // Text search
        if ($request->filled('q')) {
            $searchTerm = $request->get('q');
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('tags', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('category', function (Builder $categoryQuery) use ($searchTerm) {
                      $categoryQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->get('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->get('max_price'));
        }

        // Location filter
        if ($request->filled('location')) {
            $query->whereHas('user', function (Builder $userQuery) use ($request) {
                $userQuery->where('city', 'LIKE', "%{$request->get('location')}%")
                         ->orWhere('state', 'LIKE', "%{$request->get('location')}%")
                         ->orWhere('country', 'LIKE', "%{$request->get('location')}%");
            });
        }

        // Seller rating filter
        if ($request->filled('min_rating')) {
            $query->whereHas('user', function (Builder $userQuery) use ($request) {
                $userQuery->where('trust_score', '>=', $request->get('min_rating'));
            });
        }

        // Condition filter
        if ($request->filled('condition')) {
            $query->where('condition', $request->get('condition'));
        }

        // Tags filter
        if ($request->filled('tags')) {
            $tags = explode(',', $request->get('tags'));
            foreach ($tags as $tag) {
                $query->where('tags', 'LIKE', "%{$tag}%");
            }
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        switch ($sortBy) {
            case 'price':
                $query->orderBy('price', $sortOrder);
                break;
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'popularity':
                $query->orderBy('views_count', $sortOrder);
                break;
            case 'rating':
                $query->orderBy('average_rating', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        // Get search statistics
        $stats = $this->getProductSearchStats($request);

        return [
            'data' => $products,
            'stats' => $stats,
            'filters' => $this->getAvailableFilters($request)
        ];
    }

    /**
     * Search businesses with advanced filtering
     */
    public function searchBusinesses(Request $request): array
    {
        $query = Business::with(['user', 'industry'])
            ->where('status', 'active');

        // Text search
        if ($request->filled('q')) {
            $searchTerm = $request->get('q');
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('industry', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('industry', function (Builder $industryQuery) use ($searchTerm) {
                      $industryQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }

        // Industry filter
        if ($request->filled('industry')) {
            $query->where('industry', $request->get('industry'));
        }

        // Valuation range filter
        if ($request->filled('min_valuation')) {
            $query->where('valuation', '>=', $request->get('min_valuation'));
        }
        if ($request->filled('max_valuation')) {
            $query->where('valuation', '<=', $request->get('max_valuation'));
        }

        // Funding goal range filter
        if ($request->filled('min_funding')) {
            $query->where('funding_goal', '>=', $request->get('min_funding'));
        }
        if ($request->filled('max_funding')) {
            $query->where('funding_goal', '<=', $request->get('max_funding'));
        }

        // Equity offered range filter
        if ($request->filled('min_equity')) {
            $query->where('equity_offered', '>=', $request->get('min_equity'));
        }
        if ($request->filled('max_equity')) {
            $query->where('equity_offered', '<=', $request->get('max_equity'));
        }

        // Location filter
        if ($request->filled('location')) {
            $query->whereHas('user', function (Builder $userQuery) use ($request) {
                $userQuery->where('city', 'LIKE', "%{$request->get('location')}%")
                         ->orWhere('state', 'LIKE', "%{$request->get('location')}%")
                         ->orWhere('country', 'LIKE', "%{$request->get('location')}%");
            });
        }

        // Owner rating filter
        if ($request->filled('min_rating')) {
            $query->whereHas('user', function (Builder $userQuery) use ($request) {
                $userQuery->where('trust_score', '>=', $request->get('min_rating'));
            });
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        switch ($sortBy) {
            case 'valuation':
                $query->orderBy('valuation', $sortOrder);
                break;
            case 'funding_goal':
                $query->orderBy('funding_goal', $sortOrder);
                break;
            case 'equity_offered':
                $query->orderBy('equity_offered', $sortOrder);
                break;
            case 'name':
                $query->orderBy('name', $sortOrder);
                break;
            case 'popularity':
                $query->orderBy('views_count', $sortOrder);
                break;
            default:
                $query->orderBy('created_at', $sortOrder);
        }

        $perPage = $request->get('per_page', 15);
        $businesses = $query->paginate($perPage);

        // Get search statistics
        $stats = $this->getBusinessSearchStats($request);

        return [
            'data' => $businesses,
            'stats' => $stats,
            'filters' => $this->getAvailableBusinessFilters($request)
        ];
    }

    /**
     * Get product search statistics
     */
    private function getProductSearchStats(Request $request): array
    {
        $query = Product::where('status', 'active');

        // Apply same filters as search
        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->get('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->get('max_price'));
        }

        return [
            'total_count' => $query->count(),
            'price_range' => [
                'min' => $query->min('price'),
                'max' => $query->max('price'),
                'avg' => $query->avg('price')
            ],
            'categories' => $query->select('category')
                ->distinct()
                ->pluck('category')
                ->count()
        ];
    }

    /**
     * Get business search statistics
     */
    private function getBusinessSearchStats(Request $request): array
    {
        $query = Business::where('status', 'active');

        // Apply same filters as search
        if ($request->filled('industry')) {
            $query->where('industry', $request->get('industry'));
        }
        if ($request->filled('min_valuation')) {
            $query->where('valuation', '>=', $request->get('min_valuation'));
        }
        if ($request->filled('max_valuation')) {
            $query->where('valuation', '<=', $request->get('max_valuation'));
        }

        return [
            'total_count' => $query->count(),
            'valuation_range' => [
                'min' => $query->min('valuation'),
                'max' => $query->max('valuation'),
                'avg' => $query->avg('valuation')
            ],
            'funding_range' => [
                'min' => $query->min('funding_goal'),
                'max' => $query->max('funding_goal'),
                'avg' => $query->avg('funding_goal')
            ],
            'industries' => $query->select('industry')
                ->distinct()
                ->pluck('industry')
                ->count()
        ];
    }

    /**
     * Get available filters for products
     */
    public function getAvailableFilters(Request $request): array
    {
        $query = Product::where('status', 'active');

        // Apply current filters to get relevant options
        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        return [
            'categories' => Product::where('status', 'active')
                ->select('category')
                ->distinct()
                ->pluck('category')
                ->filter()
                ->values(),
            'price_ranges' => [
                ['label' => 'Under $50', 'min' => 0, 'max' => 50],
                ['label' => '$50 - $100', 'min' => 50, 'max' => 100],
                ['label' => '$100 - $500', 'min' => 100, 'max' => 500],
                ['label' => '$500 - $1000', 'min' => 500, 'max' => 1000],
                ['label' => 'Over $1000', 'min' => 1000, 'max' => null],
            ],
            'conditions' => ['new', 'like_new', 'good', 'fair', 'used'],
            'locations' => Product::where('status', 'active')
                ->join('users', 'products.user_id', '=', 'users.id')
                ->select('users.city', 'users.state', 'users.country')
                ->whereNotNull('users.city')
                ->distinct()
                ->get()
                ->map(function ($item) {
                    return $item->city . ', ' . $item->state . ', ' . $item->country;
                })
                ->filter()
                ->values()
        ];
    }

    /**
     * Get available filters for businesses
     */
    public function getAvailableBusinessFilters(Request $request): array
    {
        return [
            'industries' => Business::where('status', 'active')
                ->select('industry')
                ->distinct()
                ->pluck('industry')
                ->filter()
                ->values(),
            'valuation_ranges' => [
                ['label' => 'Under $10K', 'min' => 0, 'max' => 10000],
                ['label' => '$10K - $50K', 'min' => 10000, 'max' => 50000],
                ['label' => '$50K - $100K', 'min' => 50000, 'max' => 100000],
                ['label' => '$100K - $500K', 'min' => 100000, 'max' => 500000],
                ['label' => 'Over $500K', 'min' => 500000, 'max' => null],
            ],
            'funding_ranges' => [
                ['label' => 'Under $5K', 'min' => 0, 'max' => 5000],
                ['label' => '$5K - $25K', 'min' => 5000, 'max' => 25000],
                ['label' => '$25K - $50K', 'min' => 25000, 'max' => 50000],
                ['label' => '$50K - $100K', 'min' => 50000, 'max' => 100000],
                ['label' => 'Over $100K', 'min' => 100000, 'max' => null],
            ],
            'equity_ranges' => [
                ['label' => 'Under 10%', 'min' => 0, 'max' => 10],
                ['label' => '10% - 25%', 'min' => 10, 'max' => 25],
                ['label' => '25% - 50%', 'min' => 25, 'max' => 50],
                ['label' => 'Over 50%', 'min' => 50, 'max' => 100],
            ],
            'locations' => Business::where('status', 'active')
                ->join('users', 'businesses.user_id', '=', 'users.id')
                ->select('users.city', 'users.state', 'users.country')
                ->whereNotNull('users.city')
                ->distinct()
                ->get()
                ->map(function ($item) {
                    return $item->city . ', ' . $item->state . ', ' . $item->country;
                })
                ->filter()
                ->values()
        ];
    }

    /**
     * Get trending products
     */
    public function getTrendingProducts(int $limit = 10): array
    {
        $trending = Product::where('status', 'active')
            ->with(['user'])
            ->orderBy('views_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return [
            'data' => $trending,
            'period' => 'last_30_days'
        ];
    }

    /**
     * Get trending businesses
     */
    public function getTrendingBusinesses(int $limit = 10): array
    {
        $trending = Business::where('status', 'active')
            ->with(['user'])
            ->orderBy('views_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return [
            'data' => $trending,
            'period' => 'last_30_days'
        ];
    }

    /**
     * Get personalized recommendations
     */
    public function getRecommendations(int $userId, string $type = 'product', int $limit = 10): array
    {
        if ($type === 'product') {
            return $this->getProductRecommendations($userId, $limit);
        } else {
            return $this->getBusinessRecommendations($userId, $limit);
        }
    }

    /**
     * Get product recommendations based on user behavior
     */
    private function getProductRecommendations(int $userId, int $limit): array
    {
        // Get user's viewing history
        $viewedCategories = DB::table('product_views')
            ->where('user_id', $userId)
            ->join('products', 'product_views.product_id', '=', 'products.id')
            ->select('products.category')
            ->distinct()
            ->pluck('category');

        // Get user's purchase history
        $purchasedCategories = DB::table('transactions')
            ->where('user_id', $userId)
            ->where('type', 'purchase')
            ->join('products', 'transactions.reference_id', '=', 'products.id')
            ->select('products.category')
            ->distinct()
            ->pluck('category');

        $preferredCategories = $viewedCategories->merge($purchasedCategories)->unique();

        $recommendations = Product::where('status', 'active')
            ->where('user_id', '!=', $userId) // Exclude user's own products
            ->whereIn('category', $preferredCategories)
            ->with(['user'])
            ->orderBy('average_rating', 'desc')
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();

        return [
            'data' => $recommendations,
            'reason' => 'Based on your interests'
        ];
    }

    /**
     * Get business recommendations based on user behavior
     */
    private function getBusinessRecommendations(int $userId, int $limit): array
    {
        // Get user's viewing history
        $viewedIndustries = DB::table('business_views')
            ->where('user_id', $userId)
            ->join('businesses', 'business_views.business_id', '=', 'businesses.id')
            ->select('businesses.industry')
            ->distinct()
            ->pluck('industry');

        // Get user's investment history
        $investedIndustries = DB::table('investments')
            ->where('investor_id', $userId)
            ->join('businesses', 'investments.business_id', '=', 'businesses.id')
            ->select('businesses.industry')
            ->distinct()
            ->pluck('industry');

        $preferredIndustries = $viewedIndustries->merge($investedIndustries)->unique();

        $recommendations = Business::where('status', 'active')
            ->where('user_id', '!=', $userId) // Exclude user's own businesses
            ->whereIn('industry', $preferredIndustries)
            ->with(['user'])
            ->orderBy('valuation', 'desc')
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();

        return [
            'data' => $recommendations,
            'reason' => 'Based on your investment interests'
        ];
    }
} 