<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\InvestmentController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ComparisonController;
use App\Http\Controllers\Api\FeaturedListingController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\MessagingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes with rate limiting
Route::middleware(['rate_limit:auth,5,1'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Email verification routes (public) with rate limiting
Route::middleware(['rate_limit:email_verify,10,1'])->group(function () {
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-email-verification', [AuthController::class, 'resendEmailVerification']);
});

// Phone verification routes (public) with rate limiting
Route::middleware(['rate_limit:phone_verify,10,1'])->group(function () {
    Route::post('/send-phone-verification', [AuthController::class, 'sendPhoneVerification']);
    Route::post('/verify-phone', [AuthController::class, 'verifyPhone']);
});

// Contact form (public) with rate limiting
Route::middleware(['rate_limit:contact,3,5'])->group(function () {
    Route::post('/contact', [ContactController::class, 'submit']);
});

// Protected routes (require authentication)
Route::middleware(['auth:sanctum', 'rate_limit:api,60,1'])->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/profile', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile/upload-image', [AuthController::class, 'uploadProfileImage']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/stats', [AuthController::class, 'getStats']);
    Route::post('/stop-impersonating', [AdminController::class, 'stopImpersonating']);

    // KYC and verification routes (more restrictive rate limiting)
    Route::middleware(['rate_limit:kyc,3,10'])->group(function () {
        Route::post('/kyc-upload', [AuthController::class, 'uploadKyc']);
        Route::get('/kyc/my-application', [AuthController::class, 'getMyKyc']);
        Route::post('/kyc/submit', [AuthController::class, 'submitKyc']);
    });

    // Two-factor authentication routes (more restrictive rate limiting)
    Route::middleware(['rate_limit:2fa,10,1'])->group(function () {
        Route::post('/setup-2fa', [AuthController::class, 'setup2FA']);
        Route::post('/confirm-2fa', [AuthController::class, 'confirm2FA']);
        Route::post('/setup-email-2fa', [AuthController::class, 'setupEmail2FA']);
        Route::post('/confirm-email-2fa', [AuthController::class, 'confirmEmail2FA']);
        Route::post('/skip-2fa', [AuthController::class, 'skip2FA']);
        Route::post('/verify-2fa', [AuthController::class, 'verify2FA']);
        Route::post('/disable-2fa', [AuthController::class, 'disable2FA']);
    });

    // Products routes with permissions
    Route::get('/products', [ProductController::class, 'index']); // Public viewing
    Route::get('/products/user', [ProductController::class, 'getUserProducts']);
    Route::get('/products/trending', [ProductController::class, 'getTrending']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/products/{product}/related', [ProductController::class, 'getRelated']);
    
    // Product management (requires permissions)
    Route::middleware('permission:products.create')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
    });
    Route::middleware('permission:products.edit')->group(function () {
        Route::put('/products/{product}', [ProductController::class, 'update']);
    });
    Route::middleware('permission:products.delete')->group(function () {
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    });

    // Businesses routes with permissions
    Route::get('/businesses', [BusinessController::class, 'index']); // Public viewing
    Route::get('/businesses/user', [BusinessController::class, 'getUserBusinesses']);
    Route::get('/businesses/featured', [BusinessController::class, 'getFeatured']);
    Route::get('/businesses/trending', [BusinessController::class, 'getTrending']);
    Route::get('/businesses/{business}', [BusinessController::class, 'show']);
    Route::get('/businesses/{business}/related', [BusinessController::class, 'getRelated']);
    
    // Business management (requires permissions)
    Route::middleware('permission:businesses.create')->group(function () {
        Route::post('/businesses', [BusinessController::class, 'store']);
    });
    Route::middleware('permission:businesses.edit')->group(function () {
        Route::put('/businesses/{business}', [BusinessController::class, 'update']);
    });
    Route::middleware('permission:businesses.delete')->group(function () {
        Route::delete('/businesses/{business}', [BusinessController::class, 'destroy']);
    });

    // Investment routes with permissions
    Route::middleware('permission:investments.view')->group(function () {
        Route::get('/investments', [InvestmentController::class, 'index']);
        Route::get('/investments/{investment}', [InvestmentController::class, 'show']);
        Route::get('/businesses/{business}/investments', [InvestmentController::class, 'byBusiness']);
        Route::get('/investments/user/portfolio', [InvestmentController::class, 'getUserPortfolio']);
    });
    Route::middleware('permission:investments.create')->group(function () {
        Route::post('/investments', [InvestmentController::class, 'store']);
    });
    Route::middleware('permission:investments.approve')->group(function () {
        Route::put('/investments/{investment}/approve', [InvestmentController::class, 'approve']);
        Route::put('/investments/{investment}/reject', [InvestmentController::class, 'reject']);
    });

    // Transaction routes with permissions
    Route::middleware('permission:transactions.view')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'getUserTransactions']);
        Route::get('/transactions/{transaction}', [TransactionController::class, 'getTransaction']);
    });
    Route::middleware('permission:transactions.create')->group(function () {
        Route::post('/transactions', [TransactionController::class, 'createTransaction']);
    });
    Route::middleware('permission:transactions.cancel')->group(function () {
        Route::put('/transactions/{transaction}/cancel', [TransactionController::class, 'cancelTransaction']);
    });

    // Payment routes
    Route::middleware('permission:payments.create')->group(function () {
        Route::post('/payments/product-intent', [PaymentController::class, 'createProductPaymentIntent']);
        Route::post('/payments/investment-intent', [PaymentController::class, 'createInvestmentPaymentIntent']);
        Route::post('/payments/confirm', [PaymentController::class, 'confirmPayment']);
        
        // Paystack payment routes
        Route::post('/payments/paystack/product', [PaymentController::class, 'createPaystackProductPayment']);
        Route::post('/payments/paystack/investment', [PaymentController::class, 'createPaystackInvestmentPayment']);
        Route::post('/payments/paystack/verify', [PaymentController::class, 'verifyPaystackPayment']);
        
        // Escrow routes
        Route::post('/payments/escrow/create', [PaymentController::class, 'createEscrowTransaction']);
        Route::post('/payments/escrow/release', [PaymentController::class, 'releaseEscrowFunds']);
        Route::get('/payments/escrow/{escrow_id}', [PaymentController::class, 'getEscrowTransaction']);
    });
    Route::middleware('permission:payments.refund')->group(function () {
        Route::post('/payments/refund', [PaymentController::class, 'processRefund']);
    });
    Route::get('/payments/wallet-balance', [PaymentController::class, 'getWalletBalance']);
    Route::get('/payments/transaction-history', [PaymentController::class, 'getTransactionHistory']);
    Route::post('/payments/stripe-account', [PaymentController::class, 'createStripeAccount']);
    Route::get('/payments/stripe-account-status', [PaymentController::class, 'getStripeAccountStatus']);
    
    // Webhook routes (no authentication required)
    Route::post('/webhooks/stripe', [PaymentController::class, 'handleWebhook']);
    Route::post('/webhooks/paystack', [PaymentController::class, 'handlePaystackWebhook']);

    // Search routes
    Route::get('/search/products', [SearchController::class, 'searchProducts']);
    Route::get('/search/businesses', [SearchController::class, 'searchBusinesses']);
    Route::get('/search/suggestions', [SearchController::class, 'getSearchSuggestions']);
    Route::get('/search/filters', [SearchController::class, 'getSearchFilters']);
    Route::get('/search/trending/products', [SearchController::class, 'getTrendingProducts']);
    Route::get('/search/trending/businesses', [SearchController::class, 'getTrendingBusinesses']);
    Route::get('/search/recommendations', [SearchController::class, 'getRecommendations']);

    // Wishlist routes
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::put('/wishlist/{id}', [WishlistController::class, 'update']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);
    Route::get('/wishlist/check', [WishlistController::class, 'check']);
    Route::get('/wishlist/stats', [WishlistController::class, 'stats']);
    Route::get('/wishlist/public', [WishlistController::class, 'public']);

    // Review routes
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::post('/reviews/{id}/helpful', [ReviewController::class, 'markHelpful']);
    Route::get('/reviews/can-review', [ReviewController::class, 'canReview']);
    Route::get('/reviews/my-reviews', [ReviewController::class, 'myReviews']);

    // Comparison routes
    Route::get('/comparison', [ComparisonController::class, 'index']);
    Route::post('/comparison/add', [ComparisonController::class, 'addItem']);
    Route::delete('/comparison/remove', [ComparisonController::class, 'removeItem']);
    Route::delete('/comparison/clear', [ComparisonController::class, 'clear']);
    Route::get('/comparison/check', [ComparisonController::class, 'checkItem']);
    Route::get('/comparison/suggestions', [ComparisonController::class, 'getSuggestions']);
    Route::get('/comparison/history', [ComparisonController::class, 'getHistory']);
    Route::post('/comparison/export', [ComparisonController::class, 'export']);

    // Featured listings routes
    Route::get('/featured-listings', [FeaturedListingController::class, 'index']);
    Route::post('/featured-listings', [FeaturedListingController::class, 'store']);
    Route::get('/featured-listings/my', [FeaturedListingController::class, 'myListings']);
    Route::get('/featured-listings/{id}', [FeaturedListingController::class, 'show']);
    Route::put('/featured-listings/{id}', [FeaturedListingController::class, 'update']);
    Route::delete('/featured-listings/{id}', [FeaturedListingController::class, 'destroy']);
    Route::post('/featured-listings/{id}/toggle-status', [FeaturedListingController::class, 'toggleStatus']);
    Route::get('/featured-listings/{id}/analytics', [FeaturedListingController::class, 'analytics']);
    Route::post('/featured-listings/{id}/click', [FeaturedListingController::class, 'recordClick']);
    Route::get('/featured-listings/statistics/overview', [FeaturedListingController::class, 'statistics']);

    // Recommendation routes
    Route::get('/recommendations', [RecommendationController::class, 'index']);
    Route::post('/recommendations/generate', [RecommendationController::class, 'generate']);
    Route::post('/recommendations/interaction', [RecommendationController::class, 'recordInteraction']);
    Route::get('/recommendations/preferences', [RecommendationController::class, 'getPreferences']);
    Route::put('/recommendations/preferences', [RecommendationController::class, 'updatePreferences']);
    Route::post('/recommendations/activity', [RecommendationController::class, 'recordActivity']);
    Route::get('/recommendations/analytics', [RecommendationController::class, 'analytics']);

    // Messaging routes
    Route::get('/conversations', [MessagingController::class, 'getConversations']);
    Route::post('/conversations', [MessagingController::class, 'createConversation']);
    Route::get('/conversations/search', [MessagingController::class, 'searchConversations']);
    Route::get('/conversations/{conversationId}/messages', [MessagingController::class, 'getMessages']);
    Route::post('/messages', [MessagingController::class, 'sendMessage']);
    Route::post('/conversations/{conversationId}/mark-read', [MessagingController::class, 'markAsRead']);
    Route::get('/messages/unread-count', [MessagingController::class, 'getUnreadCount']);

    // Analytics routes with permissions
    Route::middleware('permission:analytics.view')->group(function () {
        Route::get('/analytics/seller', [AnalyticsController::class, 'getSellerAnalytics']);
        Route::get('/analytics/buyer', [AnalyticsController::class, 'getBuyerAnalytics']);
        Route::get('/analytics/overview', [AnalyticsController::class, 'getOverviewAnalytics']);
        Route::get('/analytics/realtime', [AnalyticsController::class, 'getRealTimeAnalytics']);
        Route::post('/analytics/track', [AnalyticsController::class, 'trackEvent']);
        Route::post('/analytics/reports/custom', [AnalyticsController::class, 'generateCustomReport']);
        Route::post('/analytics/export', [AnalyticsController::class, 'exportAnalytics']);
    });

    // Notifications routes
    Route::middleware('permission:notifications.view')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
        Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
    });

    // Messages routes
    Route::middleware('permission:messages.view')->group(function () {
        Route::get('/messages/conversations', [MessageController::class, 'getConversations']);
        Route::get('/messages/conversations/{userId}', [MessageController::class, 'getConversation']);
        Route::get('/messages/unread-count', [MessageController::class, 'getUnreadCount']);
        Route::put('/messages/conversations/{userId}/read', [MessageController::class, 'markConversationAsRead']);
        Route::get('/messages/search', [MessageController::class, 'searchMessages']);
    });
    Route::middleware('permission:messages.send')->group(function () {
        Route::post('/messages', [MessageController::class, 'sendMessage']);
        Route::delete('/messages/{messageId}', [MessageController::class, 'deleteMessage']);
    });

    // Admin routes with granular permissions
    Route::middleware('permission:admin.access')->group(function () {
        // Dashboard stats
        Route::get('/admin/dashboard/stats', [AdminController::class, 'getDashboardStats']);
        Route::get('/admin/dashboard/activity', [AdminController::class, 'getRecentActivity']);

        // Analytics (Admin)
        Route::middleware('permission:admin.analytics')->group(function () {
            Route::get('/admin/analytics/platform', [AnalyticsController::class, 'getPlatformAnalytics']);
        });

        // User management with specific permissions
        Route::middleware('permission:users.view')->group(function () {
            Route::get('/admin/users', [AdminController::class, 'getUsers']);
            Route::get('/admin/users/{user}', [AdminController::class, 'getUser']);
        });
        Route::middleware('permission:users.create')->group(function () {
            Route::post('/admin/users', [AdminController::class, 'createUser']);
        });
        Route::middleware('permission:users.edit')->group(function () {
            Route::put('/admin/users/{user}', [AdminController::class, 'updateUser']);
            Route::post('/admin/users/{user}/suspend', [AdminController::class, 'suspendUser']);
            Route::post('/admin/users/{user}/activate', [AdminController::class, 'activateUser']);
        });
        Route::middleware('permission:users.delete')->group(function () {
            Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser']);
        });
        Route::middleware('permission:users.impersonate')->group(function () {
            Route::post('/admin/users/{user}/login-as', [AdminController::class, 'loginAsUser']);
        });
        Route::middleware('permission:users.export')->group(function () {
            Route::post('/admin/users/export', [AdminController::class, 'exportUsers']);
        });

        // KYC management with permissions
        Route::middleware('permission:kyc.view')->group(function () {
            Route::get('/admin/kyc/applications', [AdminController::class, 'getKYCApplications']);
            Route::get('/admin/kyc/applications/{id}', [AdminController::class, 'getKYCApplication']);
            Route::get('/admin/kyc/documents/{document}', [AdminController::class, 'getKYCDocument']);
        });
        Route::middleware('permission:kyc.approve')->group(function () {
            Route::post('/admin/kyc/applications/{id}/approve', [AdminController::class, 'approveKYC']);
        });
        Route::middleware('permission:kyc.reject')->group(function () {
            Route::post('/admin/kyc/applications/{id}/reject', [AdminController::class, 'rejectKYC']);
        });

        // Transaction management with permissions
        Route::middleware('permission:admin.transactions')->group(function () {
            Route::get('/admin/transactions', [AdminController::class, 'getTransactions']);
            Route::get('/admin/transactions/{transaction}', [AdminController::class, 'getTransaction']);
            Route::post('/admin/transactions/{transaction}/refund', [AdminController::class, 'refundTransaction']);
        });

        // Reports with permissions
        Route::middleware('permission:reports.view')->group(function () {
            Route::get('/admin/reports/users', [AdminController::class, 'getUserReport']);
            Route::get('/admin/reports/transactions', [AdminController::class, 'getTransactionReport']);
            Route::get('/admin/reports/revenue', [AdminController::class, 'getRevenueReport']);
            Route::get('/admin/reports/kyc', [AdminController::class, 'getKYCReport']);
            Route::post('/admin/reports/export', [AdminController::class, 'exportReport']);
        });

        // Content moderation with permissions
        Route::middleware('permission:moderation.manage')->group(function () {
            Route::get('/admin/moderation/listings', [AdminController::class, 'getFlaggedListings']);
            Route::post('/admin/moderation/listings/{listing}/approve', [AdminController::class, 'approveListing']);
            Route::post('/admin/moderation/listings/{listing}/reject', [AdminController::class, 'rejectListing']);
            Route::get('/admin/moderation/disputes', [AdminController::class, 'getDisputes']);
            Route::post('/admin/moderation/disputes/{dispute}/resolve', [AdminController::class, 'resolveDispute']);
        });

        // System settings with permissions
        Route::middleware('permission:system.settings')->group(function () {
            Route::get('/admin/settings', [AdminController::class, 'getSettings']);
            Route::put('/admin/settings', [AdminController::class, 'updateSettings']);
            Route::get('/admin/system/health', [AdminController::class, 'getSystemHealth']);
            Route::get('/admin/system/logs', [AdminController::class, 'getSystemLogs']);
        });
    });
});

// Role and Permission Management (Super Admin only)
Route::middleware(['auth:sanctum', 'permission:super_admin.access'])->group(function () {
    Route::apiResource('roles', RoleController::class);
    Route::get('permissions', [RoleController::class, 'getPermissions']);
});

// Legacy route for compatibility
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});