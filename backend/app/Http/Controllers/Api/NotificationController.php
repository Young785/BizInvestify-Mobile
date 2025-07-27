<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get user's notifications (index method for API routes)
     */
    public function index(Request $request): JsonResponse
    {
        return $this->getNotifications($request);
    }

    /**
     * Get user's notifications
     */
    public function getNotifications(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);
            $type = $request->get('type');
            $priority = $request->get('priority');
            $unreadOnly = $request->get('unread_only', false);

            $query = Notification::where('user_id', $user->id);

            if ($type) {
                $query->where('type', $type);
            }

            if ($priority) {
                $query->where('priority', $priority);
            }

            if ($unreadOnly) {
                $query->where('read_at', null);
            }

            $notifications = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $notifications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications'
            ], 500);
        }
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount(): JsonResponse
    {
        try {
            $user = Auth::user();
            $count = Notification::getUnreadCount($user->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $count
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch unread count'
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $notification): JsonResponse
    {
        try {
            $user = Auth::user();
            $notificationModel = Notification::where('id', $notification)
                ->where('user_id', $user->id)
                ->first();

            if (!$notificationModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notificationModel->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $user = Auth::user();
            $count = Notification::markAllAsRead($user->id);

            return response()->json([
                'success' => true,
                'message' => $count . ' notifications marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notifications as read'
            ], 500);
        }
    }

    /**
     * Get user's activity log
     */
    public function getActivityLog(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);
            $type = $request->get('type');
            $severity = $request->get('severity');

            $query = ActivityLog::where('user_id', $user->id);

            if ($type) {
                $query->where('activity_type', $type);
            }

            if ($severity) {
                $query->where('severity', $severity);
            }

            $activities = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $activities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch activity log'
            ], 500);
        }
    }

    /**
     * Get recent activities for dashboard
     */
    public function getRecentActivities(): JsonResponse
    {
        try {
            $user = Auth::user();
            $activities = ActivityLog::getRecentActivities($user->id, 10);

            return response()->json([
                'success' => true,
                'data' => $activities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent activities'
            ], 500);
        }
    }

    /**
     * Delete notification
     */
    public function destroy($notification): JsonResponse
    {
        try {
            $user = Auth::user();
            $notificationModel = Notification::where('id', $notification)
                ->where('user_id', $user->id)
                ->first();

            if (!$notificationModel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notificationModel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification'
            ], 500);
        }
    }
} 