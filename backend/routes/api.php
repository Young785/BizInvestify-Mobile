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
use App\Http\Controllers\Api\NotificationPreferencesController;
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
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\RealtimeController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\AdminWithdrawalController;

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
Route::middleware(['rate_limit:auth,10,1'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Password reset routes (public) with rate limiting
Route::middleware(['rate_limit:password_reset,3,5'])->group(function () {
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Email verification routes (public) with rate limiting
Route::middleware(['rate_limit:email_verify,20,1'])->group(function () {
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/check-email-verification-status', [AuthController::class, 'checkEmailVerificationStatus']);
    Route::post('/resend-email-verification', [AuthController::class, 'resendEmailVerification']);
    Route::post('/send-email-verification-otp', [AuthController::class, 'sendEmailVerificationOTP']);
    Route::post('/verify-email-otp', [AuthController::class, 'verifyEmailOTP']);
});

// Phone verification routes (public) with rate limiting
Route::middleware(['rate_limit:phone_verify,20,1'])->group(function () {
    Route::post('/send-phone-verification', [AuthController::class, 'sendPhoneVerification']);
    Route::post('/verify-phone', [AuthController::class, 'verifyPhone']);
});

// Contact form (public) with rate limiting
Route::middleware(['rate_limit:contact,3,5'])->group(function () {
    Route::post('/contact', [ContactController::class, 'submit']);
});

// Public marketplace routes (no authentication required)
Route::middleware(['rate_limit:public_api,200,1'])->group(function () {
    Route::get('/public/products', [ProductController::class, 'publicIndex']);
    Route::get('/public/businesses', [BusinessController::class, 'publicIndex']);
    Route::get('/public/products/{product}', [ProductController::class, 'publicShow']);
    Route::get('/public/businesses/{business}', [BusinessController::class, 'publicShow']);
    Route::get('/public/categories', [MarketplaceController::class, 'getPublicCategories']);
    Route::get('/public/featured', [MarketplaceController::class, 'getPublicFeatured']);
    Route::get('/public/trending', [MarketplaceController::class, 'getPublicTrending']);
    Route::get('/public/search', [SearchController::class, 'publicSearch']);
    Route::get('/public/marketplace/stats', [MarketplaceController::class, 'getPublicStats']);
    Route::get('/public/marketplace/recommendations', [MarketplaceController::class, 'getPublicRecommendations']);
    Route::get('/public/platform/config', [PlatformController::class, 'getPublicConfig']);
    Route::get('/public/pricing', [PlatformController::class, 'getPublicPricing']);
    Route::get('/public/currencies', [CurrencyController::class, 'getSupportedCurrencies']);
    Route::get('/public/currency/rate', [CurrencyController::class, 'getRate']);
    Route::post('/public/currency/convert', [CurrencyController::class, 'convert']);
    Route::get('/public/payment-gateways', [PlatformController::class, 'getPublicPaymentGateways']);
    Route::post('/public/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
    Route::post('/public/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe']);
    Route::get('/public/orders/{orderNumber}/tracking', [OrderController::class, 'tracking']);
});

// Payment provider redirect callbacks (no auth)
Route::get('/payments/paystack/callback', [PaymentController::class, 'paystackCallback']);
Route::get('/payments/flutterwave/callback', [PaymentController::class, 'flutterwaveCallback']);

// Payment webhooks (no authentication — called by providers)
Route::post('/webhooks/stripe', [PaymentController::class, 'handleWebhook']);
Route::post('/webhooks/paystack', [PaymentController::class, 'handlePaystackWebhook']);
Route::post('/webhooks/flutterwave', [PaymentController::class, 'handleFlutterwaveWebhook']);

// Payment routes (require authentication)
Route::middleware(['auth:sanctum', 'rate_limit:api,120,1'])->group(function () {
    Route::post('/payments/create-intent', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/payments/bank-transfer', [PaymentController::class, 'processBankTransfer']);
    Route::post('/payments/crypto', [PaymentController::class, 'processCryptoPayment']);
    Route::get('/payments/methods', [PaymentController::class, 'getPaymentMethods']);
    Route::post('/payments/methods', [PaymentController::class, 'savePaymentMethod']);
    Route::get('/payments/transactions', [PaymentController::class, 'getTransactionHistory']);
    Route::get('/payments/status/{paymentIntentId}', [PaymentController::class, 'getPaymentStatus']);
    Route::post('/payments/cancel', [PaymentController::class, 'cancelPayment']);
    Route::get('/payments/analytics', [PaymentController::class, 'getPaymentAnalytics']);
});

// Chat routes (require authentication)
Route::middleware(['auth:sanctum', 'rate_limit:chat,60,1'])->group(function () {
    Route::get('/chat/rooms', [ChatController::class, 'getRooms']);
    Route::post('/chat/rooms', [ChatController::class, 'createRoom']);
    Route::get('/chat/rooms/{room}/messages', [ChatController::class, 'getMessages']);
    Route::post('/chat/rooms/{room}/messages', [ChatController::class, 'sendMessage']);
    Route::post('/chat/rooms/{room}/read', [ChatController::class, 'markAsRead']);
    Route::delete('/chat/rooms/{room}', [ChatController::class, 'deleteRoom']);
    Route::get('/chat/search', [ChatController::class, 'searchMessages']);
    Route::get('/chat/unread-count', [ChatController::class, 'getUnreadCount']);
});

// Protected routes (require authentication)
Route::middleware(['auth:sanctum', 'rate_limit:api,120,1', \App\Http\Middleware\TwoFactorMiddleware::class])->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/profile', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::get('/user/notification-settings', [NotificationPreferencesController::class, 'show']);
    Route::put('/user/notification-settings', [NotificationPreferencesController::class, 'update']);
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
    Route::middleware(['rate_limit:2fa,30,1'])->group(function () {
        Route::post('/setup-2fa', [AuthController::class, 'setup2FA']);
        Route::post('/confirm-2fa', [AuthController::class, 'confirm2FA']);
        Route::post('/setup-email-2fa', [AuthController::class, 'setupEmail2FA']);
        Route::post('/confirm-email-2fa', [AuthController::class, 'confirmEmail2FA']);
        Route::post('/skip-2fa', [AuthController::class, 'skip2FA']);
        Route::post('/verify-2fa', [AuthController::class, 'verify2FA']);
        Route::post('/disable-2fa', [AuthController::class, 'disable2FA']);

        // Session-based 2FA verification routes
        Route::post('/verify-2fa-session', [AuthController::class, 'verify2FAForSession']);
        Route::get('/check-2fa-status', [AuthController::class, 'check2FAVerificationStatus']);
    });

    // Products routes with permissions
    Route::get('/products', [ProductController::class, 'index']); // Public viewing
    Route::get('/products/user', [ProductController::class, 'getUserProducts']);
    Route::get('/products/trending', [ProductController::class, 'getTrending']);
    Route::get('/products/{product}/manage', [ProductController::class, 'showForOwner']);
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

    // Product category routes (slug-based identifiers)
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::middleware('permission:products.create')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
    });
    Route::middleware('permission:products.edit')->group(function () {
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
    });
    Route::middleware('permission:products.delete')->group(function () {
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    });

    // Businesses routes with permissions
    Route::get('/businesses', [BusinessController::class, 'index']); // Public viewing
    Route::get('/businesses/user', [BusinessController::class, 'getUserBusinesses']);
    Route::get('/businesses/featured', [BusinessController::class, 'getFeatured']);
    Route::get('/businesses/trending', [BusinessController::class, 'getTrending']);
    Route::get('/businesses/{business}/manage', [BusinessController::class, 'showForOwner']);
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
        Route::get('/investments/my-investments', [InvestmentController::class, 'getMyInvestments']);
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
    Route::post('/transactions/export', [TransactionController::class, 'exportTransactions']);
    Route::get('/transactions/download/{filename}', [TransactionController::class, 'downloadExport']);

    // Payment routes
    Route::middleware('permission:payments.create')->group(function () {
        Route::post('/payments/product-intent', [PaymentController::class, 'createProductPaymentIntent']);
        Route::post('/payments/investment-intent', [PaymentController::class, 'createInvestmentPaymentIntent']);
        Route::post('/payments/confirm', [PaymentController::class, 'confirmPayment']);

        // Paystack payment routes
        Route::post('/payments/paystack/product', [PaymentController::class, 'createPaystackProductPayment']);
        Route::post('/payments/paystack/investment', [PaymentController::class, 'createPaystackInvestmentPayment']);
        Route::post('/payments/paystack/verify', [PaymentController::class, 'verifyPaystackPayment']);

        // Flutterwave payment routes
        Route::post('/payments/flutterwave/product', [PaymentController::class, 'createFlutterwaveProductPayment']);
        Route::post('/payments/flutterwave/investment', [PaymentController::class, 'createFlutterwaveInvestmentPayment']);
        Route::post('/payments/flutterwave/verify', [PaymentController::class, 'verifyFlutterwavePayment']);

        Route::get('/payments/available-gateways', [PaymentController::class, 'getAvailableGateways']);

        // Escrow routes
        Route::post('/payments/subscription-intent', [PaymentController::class, 'createSubscriptionPaymentIntent']);
        Route::post('/payments/featured-listing-intent', [PaymentController::class, 'createFeaturedListingPaymentIntent']);
        Route::post('/payments/escrow/create', [PaymentController::class, 'createEscrowTransaction']);
        Route::post('/payments/escrow', [PaymentController::class, 'createEscrowTransaction']);
        Route::get('/payments/escrow', [PaymentController::class, 'listEscrowTransactions']);
        Route::get('/payments/escrow/{escrow_id}', [PaymentController::class, 'getEscrowTransaction']);
        Route::patch('/payments/escrow/{escrow_id}/conditions', [PaymentController::class, 'updateEscrowConditions']);
        Route::post('/payments/escrow/release', [PaymentController::class, 'releaseEscrowFunds']);
    });
    Route::middleware('permission:payments.refund')->group(function () {
        Route::post('/payments/refund', [PaymentController::class, 'processRefund']);
    });
    Route::get('/payments/wallet-balance', [PaymentController::class, 'getWalletBalance']);
    Route::get('/payments/transaction-history', [PaymentController::class, 'getTransactionHistory']);

    // Wallet, bank accounts & withdrawals
    Route::get('/wallet/summary', [WalletController::class, 'summary']);
    Route::get('/wallet/bank-accounts', [WalletController::class, 'listBankAccounts']);
    Route::post('/wallet/bank-accounts', [WalletController::class, 'storeBankAccount']);
    Route::put('/wallet/bank-accounts/{id}', [WalletController::class, 'updateBankAccount']);
    Route::delete('/wallet/bank-accounts/{id}', [WalletController::class, 'destroyBankAccount']);
    Route::post('/wallet/bank-accounts/{id}/default', [WalletController::class, 'setDefaultBankAccount']);
    Route::get('/wallet/withdrawals', [WalletController::class, 'listWithdrawals']);
    Route::post('/wallet/withdrawals', [WalletController::class, 'requestWithdrawal']);
    Route::post('/wallet/withdrawals/{id}/cancel', [WalletController::class, 'cancelWithdrawal']);
    Route::post('/payments/stripe-account', [PaymentController::class, 'createStripeAccount']);
    Route::get('/payments/stripe-account-status', [PaymentController::class, 'getStripeAccountStatus']);
    Route::get('/payments/stripe-connect/status', [PaymentController::class, 'getStripeConnectStatus']);
    Route::post('/payments/stripe-connect/onboarding', [PaymentController::class, 'startStripeConnectOnboarding']);
    Route::post('/payments/stripe-connect/dashboard', [PaymentController::class, 'getStripeConnectDashboard']);

    // Realtime event polling (notifications, messages)
    Route::get('/realtime/poll', [RealtimeController::class, 'poll']);

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

    // Support routes
    Route::get('/support/chat/session', [SupportController::class, 'getChatSession']);
    Route::post('/support/chat/messages', [SupportController::class, 'sendChatMessage']);
    Route::get('/support/tickets', [SupportController::class, 'index']);
    Route::post('/support/tickets', [SupportController::class, 'store']);
    Route::get('/support/tickets/{ticket}', [SupportController::class, 'show']);
    Route::put('/support/tickets/{ticket}', [SupportController::class, 'update']);
    Route::delete('/support/tickets/{ticket}', [SupportController::class, 'destroy']);
    Route::get('/support/stats', [SupportController::class, 'getStats']);

    // Order routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/stats', [OrderController::class, 'getStats']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::patch('/orders/{order}/shipping', [OrderController::class, 'updateShipping']);

    // Comparison routes
    Route::get('/comparison', [ComparisonController::class, 'index']);
    Route::post('/comparison/add', [ComparisonController::class, 'addItem']);
    Route::delete('/comparison/remove', [ComparisonController::class, 'removeItem']);
    Route::delete('/comparison/clear', [ComparisonController::class, 'clear']);
    Route::get('/comparison/check', [ComparisonController::class, 'checkItem']);
    Route::get('/comparison/suggestions', [ComparisonController::class, 'getSuggestions']);
    Route::get('/comparison/history', [ComparisonController::class, 'getHistory']);
    Route::post('/comparison/export', [ComparisonController::class, 'export']);
    Route::get('/comparison/session/product', [ComparisonController::class, 'getSessionProducts']);

    // Comparison session routes
    Route::get('/comparison/session/{sessionId}', [ComparisonController::class, 'getSession']);
    Route::post('/comparison/session/{sessionId}/share', [ComparisonController::class, 'shareSession']);
    Route::get('/comparison/session/{sessionId}/export', [ComparisonController::class, 'exportSession']);
    Route::delete('/comparison/session/{sessionId}', [ComparisonController::class, 'deleteSession']);
    Route::post('/comparison/session/{sessionId}/items', [ComparisonController::class, 'addItemToSession']);
    Route::delete('/comparison/session/{sessionId}/items/{itemId}', [ComparisonController::class, 'removeItemFromSession']);

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

    // Marketplace routes
    Route::get('/marketplace/stats', [MarketplaceController::class, 'getStats']);
    Route::get('/marketplace/recommendations', [MarketplaceController::class, 'getRecommendations']);
    Route::get('/marketplace/featured', [MarketplaceController::class, 'getFeaturedListings']);
    Route::get('/marketplace/trending', [MarketplaceController::class, 'getTrending']);
    Route::get('/marketplace/categories', [MarketplaceController::class, 'getCategories']);
    Route::get('/marketplace/insights', [MarketplaceController::class, 'getInsights']);

    // Admin marketplace routes
    Route::middleware('permission:admin.analytics')->group(function () {
        Route::get('/marketplace/analytics', [MarketplaceController::class, 'getAnalytics']);
        Route::get('/marketplace/search-insights', [MarketplaceController::class, 'getSearchInsights']);
        Route::get('/marketplace/health', [MarketplaceController::class, 'getHealth']);
    });

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

    Route::middleware('permission:activity_logs.view')->group(function () {
        Route::get('/activity-logs', [NotificationController::class, 'getActivityLog']);
        Route::get('/activity-logs/recent', [NotificationController::class, 'getRecentActivities']);
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
        Route::get('/admin/analytics/platform', [AnalyticsController::class, 'getPlatformAnalytics']);

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
            Route::get('/admin/kyc/documents/view', [AdminController::class, 'getKYCDocument']);
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

        Route::middleware('permission:payments.manage')->group(function () {
            Route::get('/admin/withdrawals', [AdminWithdrawalController::class, 'index']);
            Route::get('/admin/withdrawals/{id}', [AdminWithdrawalController::class, 'show']);
            Route::post('/admin/withdrawals/{id}/approve', [AdminWithdrawalController::class, 'approve']);
            Route::post('/admin/withdrawals/{id}/reject', [AdminWithdrawalController::class, 'reject']);
            Route::post('/admin/withdrawals/{id}/complete', [AdminWithdrawalController::class, 'complete']);
        });

        // Reports with permissions
        Route::middleware('permission:reports.view')->group(function () {
            Route::get('/admin/reports/overview', [AdminController::class, 'getReportsOverview']);
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
            Route::get('/admin/payment-gateways', [AdminController::class, 'getPaymentGateways']);
            Route::put('/admin/payment-gateways', [AdminController::class, 'updatePaymentGateways']);
            Route::post('/admin/payment-gateways/{gateway}/test', [AdminController::class, 'testPaymentGateway']);
            Route::get('/admin/featured-listings/pending', [FeaturedListingController::class, 'pending']);
            Route::post('/admin/featured-listings/{id}/approve', [FeaturedListingController::class, 'approve']);
            Route::post('/admin/featured-listings/{id}/reject', [FeaturedListingController::class, 'reject']);
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