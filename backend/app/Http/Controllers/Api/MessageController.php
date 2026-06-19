<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Get user's conversations
     */
    public function getConversations(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 15);

            // Get unique conversations with latest message
            $conversations = Message::where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)
                        ->orWhere('receiver_id', $user->id);
                })
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function ($message) use ($user) {
                    return $message->sender_id == $user->id 
                        ? $message->receiver_id 
                        : $message->sender_id;
                })
                ->map(function ($messages) use ($user) {
                    $latestMessage = $messages->first();
                    $otherUserId = $latestMessage->sender_id == $user->id 
                        ? $latestMessage->receiver_id 
                        : $latestMessage->sender_id;
                    
                    $unreadCount = Message::where('sender_id', $otherUserId)
                        ->where('receiver_id', $user->id)
                        ->where('is_read', false)
                        ->count();
                    
                    $latestMessage->unread_count = $unreadCount;
                    return $latestMessage;
                })
                ->values()
                ->take($perPage);

            return response()->json([
                'success' => true,
                'data' => $conversations
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch conversations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch conversations'
            ], 500);
        }
    }

    /**
     * Get messages in a conversation
     */
    public function getConversation(Request $request, $userId): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 50);

            // Validate that the other user exists
            $otherUser = User::find($userId);
            if (!$otherUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $messages = Message::conversation($user->id, $userId)
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Mark messages as read
            Message::where('sender_id', $userId)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'data' => $messages,
                'other_user' => $otherUser
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch conversation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch conversation'
            ], 500);
        }
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required|exists:users,id',
                'message' => 'required|string|max:1000',
                'listing_id' => 'nullable|integer',
                'listing_type' => 'nullable|string|in:product,business'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            // Check if user is trying to message themselves
            if ($user->id == $request->receiver_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot send message to yourself'
                ], 400);
            }

            // Security: strip HTML tags from message content
            $sanitizedMessage = trim(strip_tags($request->message));

            DB::beginTransaction();

            $message = Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $request->receiver_id,
                'message' => $sanitizedMessage,
                'listing_id' => $request->listing_id,
                'listing_type' => $request->listing_type,
                'is_read' => false
            ]);

            $message->load(['sender', 'receiver']);

            // Create notification for receiver
            Notification::createNotification(
                (int) $request->receiver_id,
                'message',
                'New Message',
                'You have a new message from ' . $user->first_name . ' ' . $user->last_name,
                [
                    'sender_id' => $user->id,
                    'sender_name' => $user->first_name . ' ' . $user->last_name,
                    'message_id' => $message->id,
                    'action_url' => '/dashboard/messages',
                ],
                'medium'
            );

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'activity_type' => 'message_sent',
                'description' => 'Sent a message to ' . $message->receiver->first_name . ' ' . $message->receiver->last_name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'severity' => 'low'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $message,
                'message' => 'Message sent successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to send message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message'
            ], 500);
        }
    }

    /**
     * Get unread messages count
     */
    public function getUnreadCount(): JsonResponse
    {
        try {
            $user = Auth::user();
            $count = Message::where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $count
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch unread count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch unread count'
            ], 500);
        }
    }

    /**
     * Mark conversation as read
     */
    public function markConversationAsRead(Request $request, $userId): JsonResponse
    {
        try {
            $user = Auth::user();

            $count = Message::where('sender_id', $userId)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => $count . ' messages marked as read'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to mark messages as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark messages as read'
            ], 500);
        }
    }

    /**
     * Delete a message
     */
    public function deleteMessage(Request $request, $messageId): JsonResponse
    {
        try {
            $user = Auth::user();

            $message = Message::where('id', $messageId)
                ->where('sender_id', $user->id)
                ->first();

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found or unauthorized'
                ], 404);
            }

            $message->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => $user->id,
                'activity_type' => 'message_deleted',
                'description' => 'Deleted a message',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'severity' => 'low'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to delete message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete message'
            ], 500);
        }
    }

    /**
     * Search messages
     */
    public function searchMessages(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = $request->get('query');
            $perPage = $request->get('per_page', 20);

            if (!$query || strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query must be at least 2 characters'
                ], 400);
            }

            // Security: Sanitize search query
            $sanitizedQuery = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');

            $messages = Message::where(function ($q) use ($user) {
                    $q->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
                })
                ->where('message', 'LIKE', '%' . $sanitizedQuery . '%')
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to search messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search messages'
            ], 500);
        }
    }
}
