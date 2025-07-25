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
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::get('/stats', [AuthController::class, 'getStats']);

    // KYC and verification routes (more restrictive rate limiting)
    Route::middleware(['rate_limit:kyc,3,10'])->group(function () {
        Route::post('/kyc-upload', [AuthController::class, 'uploadKyc']);
    });

    // Two-factor authentication routes (more restrictive rate limiting)
    Route::middleware(['rate_limit:2fa,10,1'])->group(function () {
        Route::post('/setup-2fa', [AuthController::class, 'setup2FA']);
        Route::post('/confirm-2fa', [AuthController::class, 'confirm2FA']);
        Route::post('/setup-email-2fa', [AuthController::class, 'setupEmail2FA']);
        Route::post('/confirm-email-2fa', [AuthController::class, 'confirmEmail2FA']);
        Route::post('/skip-2fa', [AuthController::class, 'skip2FA']);
    });

    // Products routes
    Route::get('/products/user', [ProductController::class, 'getUserProducts']);
    Route::get('/products/trending', [ProductController::class, 'getTrending']);
    Route::get('/products/{product}/related', [ProductController::class, 'getRelated']);
    Route::apiResource('products', ProductController::class);
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/products/category/{category}', [ProductController::class, 'byCategory']);

    // Businesses routes
    Route::get('/businesses/user', [BusinessController::class, 'getUserBusinesses']);
    Route::get('/businesses/featured', [BusinessController::class, 'getFeatured']);
    Route::get('/businesses/trending', [BusinessController::class, 'getTrending']);
    Route::get('/businesses/{business}/related', [BusinessController::class, 'getRelated']);
    Route::apiResource('businesses', BusinessController::class);
    Route::get('/businesses/search', [BusinessController::class, 'search']);
    Route::get('/businesses/industry/{industry}', [BusinessController::class, 'byIndustry']);

    // Investments routes
    Route::apiResource('investments', InvestmentController::class);
    Route::get('/businesses/{business}/investments', [InvestmentController::class, 'byBusiness']);
    Route::get('/investments/user/portfolio', [InvestmentController::class, 'getUserPortfolio']);
    Route::put('/investments/{investment}/approve', [InvestmentController::class, 'approve']);
    Route::put('/investments/{investment}/reject', [InvestmentController::class, 'reject']);

    // Messages routes
    Route::apiResource('messages', MessageController::class);
    Route::get('/messages/conversations', [MessageController::class, 'conversations']);
    Route::put('/messages/{message}/read', [MessageController::class, 'markAsRead']);
    Route::get('/messages/unread', [MessageController::class, 'unread']);

    // Notifications and Activity Logs routes
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'deleteNotification']);
    Route::get('/activity-logs', [NotificationController::class, 'getActivityLog']);
    Route::get('/activity-logs/recent', [NotificationController::class, 'getRecentActivities']);

    // Admin routes (only for admin users)
    Route::middleware('admin')->group(function () {
        // Dashboard stats
        Route::get('/admin/dashboard/stats', [AdminController::class, 'getDashboardStats']);
        Route::get('/admin/dashboard/activity', [AdminController::class, 'getRecentActivity']);

        // User management
        Route::get('/admin/users', [AdminController::class, 'getUsers']);
        Route::post('/admin/users', [AdminController::class, 'createUser']);
        Route::post('/admin/users/export', [AdminController::class, 'exportUsers']);
        Route::get('/admin/users/{user}', [AdminController::class, 'getUser']);
        Route::put('/admin/users/{user}', [AdminController::class, 'updateUser']);
        Route::delete('/admin/users/{user}', [AdminController::class, 'deleteUser']);
        Route::post('/admin/users/{user}/suspend', [AdminController::class, 'suspendUser']);
        Route::post('/admin/users/{user}/activate', [AdminController::class, 'activateUser']);
        Route::post('/admin/users/{user}/login-as', [AdminController::class, 'loginAsUser']);

        // KYC management
        Route::get('/admin/kyc/applications', [AdminController::class, 'getKYCApplications']);
        Route::get('/admin/kyc/applications/{application}', [AdminController::class, 'getKYCApplication']);
        Route::post('/admin/kyc/applications/{application}/approve', [AdminController::class, 'approveKYC']);
        Route::post('/admin/kyc/applications/{application}/reject', [AdminController::class, 'rejectKYC']);
        Route::get('/admin/kyc/documents/{document}', [AdminController::class, 'getKYCDocument']);

        // Transaction management
        Route::get('/admin/transactions', [AdminController::class, 'getTransactions']);
        Route::get('/admin/transactions/{transaction}', [AdminController::class, 'getTransaction']);
        Route::post('/admin/transactions/{transaction}/refund', [AdminController::class, 'refundTransaction']);

        // Reports
        Route::get('/admin/reports/users', [AdminController::class, 'getUserReport']);
        Route::get('/admin/reports/transactions', [AdminController::class, 'getTransactionReport']);
        Route::get('/admin/reports/revenue', [AdminController::class, 'getRevenueReport']);
        Route::get('/admin/reports/kyc', [AdminController::class, 'getKYCReport']);
        Route::post('/admin/reports/export', [AdminController::class, 'exportReport']);

        // Content moderation
        Route::get('/admin/moderation/listings', [AdminController::class, 'getFlaggedListings']);
        Route::post('/admin/moderation/listings/{listing}/approve', [AdminController::class, 'approveListing']);
        Route::post('/admin/moderation/listings/{listing}/reject', [AdminController::class, 'rejectListing']);
        Route::get('/admin/moderation/disputes', [AdminController::class, 'getDisputes']);
        Route::post('/admin/moderation/disputes/{dispute}/resolve', [AdminController::class, 'resolveDispute']);

        // System settings
        Route::get('/admin/settings', [AdminController::class, 'getSettings']);
        Route::put('/admin/settings', [AdminController::class, 'updateSettings']);
        Route::get('/admin/system/health', [AdminController::class, 'getSystemHealth']);
        Route::get('/admin/system/logs', [AdminController::class, 'getSystemLogs']);
    });
});

// Legacy route for compatibility
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); 