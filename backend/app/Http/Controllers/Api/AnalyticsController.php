<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get platform overview analytics (Admin only)
     */
    public function getPlatformAnalytics(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '30d');
            $analytics = $this->analyticsService->getPlatformOverview($period);

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get platform analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load platform analytics'
            ], 500);
        }
    }

    /**
     * Get seller analytics
     */
    public function getSellerAnalytics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $period = $request->get('period', '30d');
            
            $analytics = $this->analyticsService->getSellerAnalytics($user->id, $period);

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get seller analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to load seller analytics'
            ], 500);
        }
    }

    /**
     * Get buyer analytics
     */
    public function getBuyerAnalytics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $period = $request->get('period', '30d');
            
            $analytics = $this->analyticsService->getBuyerAnalytics($user->id, $period);

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get buyer analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load buyer analytics'
            ], 500);
        }
    }

    /**
     * Get overview analytics for all users
     */
    public function getOverviewAnalytics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $period = $request->get('period', '30d');
            
            // Get user-specific analytics based on role
            if ($user->role === 'seller') {
                $analytics = $this->analyticsService->getSellerAnalytics($user->id, $period);
            } elseif ($user->role === 'buyer') {
                $analytics = $this->analyticsService->getBuyerAnalytics($user->id, $period);
            } else {
                // Admin or other roles get platform overview
                $analytics = $this->analyticsService->getPlatformOverview($period);
            }

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get overview analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load overview analytics',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Generate custom report
     */
    public function generateCustomReport(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'event_type' => 'nullable|string',
                'user_id' => 'nullable|integer|exists:users,id',
                'category' => 'nullable|string',
                'action' => 'nullable|string'
            ]);

            $report = $this->analyticsService->generateCustomReport($filters);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate custom report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate custom report'
            ], 500);
        }
    }

    /**
     * Export analytics data
     */
    public function exportAnalytics(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'event_type' => 'nullable|string',
                'user_id' => 'nullable|integer|exists:users,id',
                'format' => 'nullable|string|in:csv,json,excel'
            ]);

            $format = $filters['format'] ?? 'csv';
            unset($filters['format']);

            $exportData = $this->analyticsService->exportAnalyticsData($filters, $format);

            return response()->json([
                'success' => true,
                'data' => [
                    'format' => $format,
                    'content' => $exportData,
                    'filename' => 'analytics_export_' . date('Y-m-d_H-i-s') . '.' . $format
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to export analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export analytics data'
            ], 500);
        }
    }

    /**
     * Track analytics event
     */
    public function trackEvent(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'event_type' => 'required|string',
                'event_category' => 'required|string',
                'event_action' => 'required|string',
                'event_label' => 'nullable|string',
                'event_data' => 'nullable|array',
                'page_url' => 'nullable|string',
                'referrer' => 'nullable|string',
                'value' => 'nullable|numeric'
            ]);

            $user = Auth::user();
            
            // Create analytics event
            $event = \App\Models\AnalyticsEvent::create([
                'user_id' => $user->id,
                'session_id' => $request->session_id ?? null,
                'event_type' => $data['event_type'],
                'event_category' => $data['event_category'],
                'event_action' => $data['event_action'],
                'event_label' => $data['event_label'] ?? null,
                'event_data' => $data['event_data'] ?? null,
                'page_url' => $data['page_url'] ?? null,
                'referrer' => $data['referrer'] ?? null,
                'user_agent' => $request->header('User-Agent'),
                'ip_address' => $request->ip(),
                'country' => $request->header('CF-IPCountry'),
                'city' => null, // Could be determined from IP
                'device_type' => $this->getDeviceType($request->header('User-Agent')),
                'browser' => $this->getBrowser($request->header('User-Agent')),
                'os' => $this->getOS($request->header('User-Agent')),
                'value' => $data['value'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event tracked successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to track event: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to track event'
            ], 500);
        }
    }

    /**
     * Get real-time analytics
     */
    public function getRealTimeAnalytics(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Get real-time data (last 24 hours)
            $startDate = now()->subDay();
            
            $realTimeData = [
                'active_users' => \App\Models\AnalyticsEvent::where('created_at', '>=', $startDate)
                    ->distinct('user_id')
                    ->count(),
                'page_views' => \App\Models\AnalyticsEvent::where('event_type', 'page_view')
                    ->where('created_at', '>=', $startDate)
                    ->count(),
                'transactions' => \App\Models\Transaction::where('status', 'completed')
                    ->where('created_at', '>=', $startDate)
                    ->count(),
                'revenue' => \App\Models\Transaction::where('status', 'completed')
                    ->where('created_at', '>=', $startDate)
                    ->sum('amount'),
                'recent_events' => \App\Models\AnalyticsEvent::with('user')
                    ->where('created_at', '>=', $startDate)
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $realTimeData
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get real-time analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load real-time analytics'
            ], 500);
        }
    }

    // Helper methods for device detection
    private function getDeviceType(string $userAgent): string
    {
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($userAgent))) {
            return 'tablet';
        }
        
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($userAgent))) {
            return 'mobile';
        }
        
        return 'desktop';
    }

    private function getBrowser(string $userAgent): string
    {
        if (preg_match('/MSIE|Trident/i', $userAgent)) {
            return 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/Opera|OPR/i', $userAgent)) {
            return 'Opera';
        } else {
            return 'Unknown';
        }
    }

    private function getOS(string $userAgent): string
    {
        if (preg_match('/windows|win32/i', $userAgent)) {
            return 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            return 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            return 'Linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            return 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            return 'iOS';
        } else {
            return 'Unknown';
        }
    }
}