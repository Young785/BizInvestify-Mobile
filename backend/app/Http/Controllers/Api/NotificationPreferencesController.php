<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationPreferencesController extends Controller
{
    public function show(Request $request, NotificationService $notifications): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $notifications->preferencesForUser($request->user()),
        ]);
    }

    public function update(Request $request, NotificationService $notifications): JsonResponse
    {
        $validated = $request->validate([
            'email_notifications' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean',
            'sms_notifications' => 'sometimes|boolean',
            'marketing_emails' => 'sometimes|boolean',
            'security_alerts' => 'sometimes|boolean',
            'kyc_updates' => 'sometimes|boolean',
            'transaction_alerts' => 'sometimes|boolean',
            'investment_updates' => 'sometimes|boolean',
            'listing_updates' => 'sometimes|boolean',
            'message_notifications' => 'sometimes|boolean',
            'frequency' => 'sometimes|in:immediate,daily,weekly',
            'quiet_hours_start' => 'sometimes|nullable|date_format:H:i',
            'quiet_hours_end' => 'sometimes|nullable|date_format:H:i',
        ]);

        try {
            $prefs = $notifications->updatePreferences($request->user(), $validated);

            return response()->json([
                'success' => true,
                'message' => 'Notification preferences updated',
                'data' => $prefs,
            ]);
        } catch (\Exception $e) {
            Log::error('Notification preferences update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification preferences',
            ], 500);
        }
    }
}
