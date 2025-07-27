<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MessagingController extends Controller
{
    /**
     * Get user's conversations
     */
    public function getConversations(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $conversations = Conversation::forUser($user)
                ->with(['participants', 'latestMessage.sender'])
                ->orderBy('last_message_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $conversations
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get conversations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load conversations'
            ], 500);
        }
    }

    /**
     * Get messages for a conversation
     */
    public function getMessages(Request $request, $conversationId): JsonResponse
    {
        try {
            $user = $request->user();
            $conversation = Conversation::findOrFail($conversationId);

            // Check if user is participant
            if (!$conversation->hasParticipant($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $messages = $conversation->messages()
                ->with('sender')
                ->orderBy('created_at', 'desc')
                ->paginate(50);

            // Mark messages as read
            $conversation->markAsRead($user);

            return response()->json([
                'success' => true,
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load messages'
            ], 500);
        }
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'required|string|max:1000',
            'type' => 'nullable|string|in:text,image,file,system'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $conversation = Conversation::findOrFail($request->conversation_id);

            // Check if user is participant
            if (!$conversation->hasParticipant($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'content' => $request->content,
                'type' => $request->type ?? 'text',
                'is_read' => false
            ]);

            // Update conversation's last message timestamp
            $conversation->update(['last_message_at' => now()]);

            // Load sender relationship
            $message->load('sender');

            return response()->json([
                'success' => true,
                'data' => $message,
                'message' => 'Message sent successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message'
            ], 500);
        }
    }

    /**
     * Create a new conversation
     */
    public function createConversation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:direct,group',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'exists:users,id',
            'title' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $participantIds = $request->participant_ids;

            // For direct conversations, ensure only 2 participants
            if ($request->type === 'direct' && count($participantIds) !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Direct conversations must have exactly one other participant'
                ], 422);
            }

            // Check if direct conversation already exists
            if ($request->type === 'direct') {
                $existingConversation = Conversation::direct()
                    ->whereHas('participants', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->whereHas('participants', function ($query) use ($participantIds) {
                        $query->where('user_id', $participantIds[0]);
                    })
                    ->first();

                if ($existingConversation) {
                    return response()->json([
                        'success' => true,
                        'data' => $existingConversation->load(['participants', 'latestMessage.sender']),
                        'message' => 'Existing conversation found'
                    ]);
                }
            }

            // Create new conversation
            $conversation = Conversation::create([
                'type' => $request->type,
                'title' => $request->title
            ]);

            // Add participants
            $conversation->addParticipant($user);
            foreach ($participantIds as $participantId) {
                $participant = User::find($participantId);
                $conversation->addParticipant($participant);
            }

            $conversation->load(['participants', 'latestMessage.sender']);

            return response()->json([
                'success' => true,
                'data' => $conversation,
                'message' => 'Conversation created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create conversation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create conversation'
            ], 500);
        }
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request, $conversationId): JsonResponse
    {
        try {
            $user = $request->user();
            $conversation = Conversation::findOrFail($conversationId);

            // Check if user is participant
            if (!$conversation->hasParticipant($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $conversation->markAsRead($user);

            return response()->json([
                'success' => true,
                'message' => 'Messages marked as read'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark messages as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark messages as read'
            ], 500);
        }
    }

    /**
     * Get unread message count
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $conversations = Conversation::forUser($user)->get();
            
            $totalUnread = 0;
            $conversationCounts = [];

            foreach ($conversations as $conversation) {
                $unreadCount = $conversation->getUnreadCount($user);
                $totalUnread += $unreadCount;
                $conversationCounts[$conversation->id] = $unreadCount;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_unread' => $totalUnread,
                    'conversation_counts' => $conversationCounts
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get unread count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread count'
            ], 500);
        }
    }

    /**
     * Search conversations
     */
    public function searchConversations(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $query = $request->query;

            $conversations = Conversation::forUser($user)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhereHas('participants', function ($participantQuery) use ($query) {
                          $participantQuery->where('first_name', 'like', "%{$query}%")
                                           ->orWhere('last_name', 'like', "%{$query}%")
                                           ->orWhere('email', 'like', "%{$query}%");
                      });
                })
                ->with(['participants', 'latestMessage.sender'])
                ->orderBy('last_message_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $conversations
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to search conversations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search conversations'
            ], 500);
        }
    }
} 