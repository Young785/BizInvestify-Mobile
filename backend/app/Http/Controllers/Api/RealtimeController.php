<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RealtimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RealtimeController extends Controller
{
    public function poll(Request $request, RealtimeService $realtime): JsonResponse
    {
        try {
            $sinceId = (int) $request->get('since_id', 0);
            $channels = $request->get('channels');

            if (is_string($channels) && $channels !== '') {
                $channels = array_filter(array_map('trim', explode(',', $channels)));
            } elseif (! is_array($channels)) {
                $channels = null;
            }

            $result = $realtime->poll($request->user(), $sinceId, $channels);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Realtime poll failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch realtime events',
            ], 500);
        }
    }
}
