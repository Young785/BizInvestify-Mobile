<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Get chat rooms for the authenticated user.
     */
    public function getRooms(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Get rooms where user is a participant
            $rooms = ChatRoom::whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with(['participants', 'lastMessage'])->get();

            // Format rooms for response
            $formattedRooms = $rooms->map(function ($room) use ($user) {
                $otherParticipant = $room->participants->where('user_id', '!=', $user->id)->first();
                
                return [
                    'id' => $room->id,
                    'name' => $room->name ?? $otherParticipant->name,
                    'last_message' => $room->lastMessage?->message ?? 'No messages yet',
                    'last_message_time' => $room->lastMessage?->created_at?->toISOString() ?? $room->created_at->toISOString(),
                    'unread_count' => $room->messages()->where('sender_id', '!=', $user->id)->where('read_at', null)->count(),
                    'is_online' => $otherParticipant?->last_seen_at?->diffInMinutes(now()) < 5 ?? false,
                    'avatar' => $otherParticipant?->profile_image ?? 'https://via.placeholder.com/50',
                    'type' => $room->type,
                    'created_at' => $room->created_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedRooms
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get chat rooms: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load chat rooms'
            ], 500);
        }
    }

    /**
     * Create a new chat room.
     */
    public function createRoom(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:support,business,admin,user',
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            
            // Create chat room
            $room = ChatRoom::create([
                'name' => $request->name,
                'type' => $request->type,
                'created_by' => $user->id,
            ]);

            // Add participants
            $participantIds = array_merge($request->participant_ids, [$user->id]);
            $room->participants()->attach($participantIds);

            return response()->json([
                'success' => true,
                'message' => 'Chat room created successfully',
                'data' => [
                    'id' => $room->id,
                    'name' => $room->name,
                    'type' => $room->type,
                    'created_at' => $room->created_at->toISOString(),
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create chat room: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create chat room'
            ], 500);
        }
    }

    /**
     * Get messages for a specific room.
     */
    public function getMessages(Request $request, $roomId): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Check if user is participant in the room
            $room = ChatRoom::whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->findOrFail($roomId);

            $messages = $room->messages()
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get();

            $formattedMessages = $messages->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'room_id' => $message->room_id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'message' => $message->message,
                    'timestamp' => $message->created_at->toISOString(),
                    'is_from_me' => $message->sender_id === $user->id,
                    'attachment_url' => $message->attachment_url,
                    'attachment_type' => $message->attachment_type,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedMessages
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
     * Send a message to a room.
     */
    public function sendMessage(Request $request, $roomId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'attachment_url' => 'nullable|url',
            'attachment_type' => 'nullable|string|in:image,document,video',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            
            // Check if user is participant in the room
            $room = ChatRoom::whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->findOrFail($roomId);

            // Create message
            $message = $room->messages()->create([
                'sender_id' => $user->id,
                'message' => $request->message,
                'attachment_url' => $request->attachment_url,
                'attachment_type' => $request->attachment_type,
            ]);

            // Update room's last message
            $room->update(['last_message_id' => $message->id]);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'id' => $message->id,
                    'room_id' => $message->room_id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $user->name,
                    'message' => $message->message,
                    'timestamp' => $message->created_at->toISOString(),
                    'is_from_me' => true,
                    'attachment_url' => $message->attachment_url,
                    'attachment_type' => $message->attachment_type,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to send message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message'
            ], 500);
        }
    }

    /**
     * Mark messages in a room as read.
     */
    public function markAsRead(Request $request, $roomId): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Check if user is participant in the room
            $room = ChatRoom::whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->findOrFail($roomId);

            // Mark unread messages as read
            $room->messages()
                ->where('sender_id', '!=', $user->id)
                ->where('read_at', null)
                ->update(['read_at' => now()]);

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
     * Delete a chat room.
     */
    public function deleteRoom(Request $request, $roomId): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Check if user is participant in the room
            $room = ChatRoom::whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->findOrFail($roomId);

            // Only allow deletion if user is the creator
            if ($room->created_by !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete rooms you created'
                ], 403);
            }

            // Delete room and all associated data
            $room->delete();

            return response()->json([
                'success' => true,
                'message' => 'Chat room deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete chat room: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete chat room'
            ], 500);
        }
    }

    /**
     * Search messages.
     */
    public function searchMessages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2',
            'room_id' => 'nullable|exists:chat_rooms,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $query = $request->q;
            $roomId = $request->room_id;

            $messagesQuery = ChatMessage::whereHas('room.participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('message', 'like', "%{$query}%");

            if ($roomId) {
                $messagesQuery->where('room_id', $roomId);
            }

            $messages = $messagesQuery->with(['sender', 'room'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            $formattedMessages = $messages->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'room_id' => $message->room_id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'message' => $message->message,
                    'timestamp' => $message->created_at->toISOString(),
                    'is_from_me' => $message->sender_id === $user->id,
                    'room_name' => $message->room->name,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedMessages
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to search messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search messages'
            ], 500);
        }
    }

    /**
     * Get unread message count.
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $count = ChatMessage::whereHas('room.participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('sender_id', '!=', $user->id)
            ->where('read_at', null)
            ->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get unread count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread count'
            ], 500);
        }
    }
}
