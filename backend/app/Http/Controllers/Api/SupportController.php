<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupportController extends Controller
{
    private const LIVE_CHAT_SUBJECT = 'Live Chat Support';

    /**
     * Get or prepare the user's active live chat support session.
     */
    public function getChatSession(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $ticket = SupportTicket::where('user_id', $user->id)
                ->where('source', 'live_chat')
                ->whereIn('status', ['open', 'in_progress'])
                ->with(['threadMessages.user'])
                ->latest()
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'ticket' => $ticket,
                    'messages' => $this->buildChatMessages($ticket),
                    'support_available' => true,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get support chat session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load support chat',
            ], 500);
        }
    }

    /**
     * Send a message in the live support chat.
     */
    public function sendChatMessage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:2000',
                'ticket_id' => 'nullable|integer|exists:support_tickets,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = Auth::user();
            $content = trim(strip_tags($request->message));

            if ($content === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Message cannot be empty',
                ], 422);
            }

            $ticket = $this->resolveLiveChatTicket($user, $request->ticket_id);

            if (!$ticket) {
                $ticket = SupportTicket::create([
                    'user_id' => $user->id,
                    'ticket_number' => 'SUP-' . strtoupper(uniqid()),
                    'subject' => self::LIVE_CHAT_SUBJECT,
                    'message' => $content,
                    'priority' => 'medium',
                    'category' => 'general',
                    'source' => 'live_chat',
                    'status' => 'open',
                ]);

                $this->seedWelcomeMessage($ticket);
            }

            if (in_array($ticket->status, ['resolved', 'closed'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This support conversation is closed. Please start a new chat.',
                ], 409);
            }

            $message = SupportTicketMessage::create([
                'support_ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'is_staff' => false,
                'message' => $content,
            ]);

            $ticket->touch();

            return response()->json([
                'success' => true,
                'message' => 'Message sent',
                'data' => [
                    'ticket' => $ticket->fresh(),
                    'message' => $this->formatChatMessage($message),
                    'messages' => $this->buildChatMessages($ticket->fresh(['threadMessages.user'])),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to send support chat message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
            ], 500);
        }
    }

    private function resolveLiveChatTicket($user, ?int $ticketId): ?SupportTicket
    {
        if ($ticketId) {
            return SupportTicket::where('id', $ticketId)
                ->where('user_id', $user->id)
                ->where('source', 'live_chat')
                ->first();
        }

        return SupportTicket::where('user_id', $user->id)
            ->where('source', 'live_chat')
            ->whereIn('status', ['open', 'in_progress'])
            ->latest()
            ->first();
    }

    private function seedWelcomeMessage(SupportTicket $ticket): void
    {
        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => null,
            'is_staff' => true,
            'message' => 'Hi! You are connected with BizInvestify Support. How can we help you today?',
        ]);
    }

    private function buildChatMessages(?SupportTicket $ticket): array
    {
        if (!$ticket) {
            return [[
                'id' => 'welcome',
                'is_staff' => true,
                'sender_name' => 'BizInvestify Support',
                'message' => 'Hi! Welcome to BizInvestify Support. Send a message and our team will respond shortly.',
                'created_at' => now()->toISOString(),
            ]];
        }

        $messages = $ticket->threadMessages->map(fn ($msg) => $this->formatChatMessage($msg))->values();

        if ($messages->isEmpty()) {
            return [[
                'id' => 'welcome',
                'is_staff' => true,
                'sender_name' => 'BizInvestify Support',
                'message' => 'Hi! You are connected with BizInvestify Support. How can we help you today?',
                'created_at' => $ticket->created_at?->toISOString() ?? now()->toISOString(),
            ]];
        }

        return $messages->all();
    }

    private function formatChatMessage(SupportTicketMessage $message): array
    {
        $senderName = 'BizInvestify Support';

        if (!$message->is_staff && $message->user) {
            $senderName = trim($message->user->first_name . ' ' . $message->user->last_name);
        } elseif ($message->is_staff && $message->user) {
            $senderName = trim($message->user->first_name . ' ' . $message->user->last_name) . ' (Support)';
        }

        return [
            'id' => (string) $message->id,
            'is_staff' => $message->is_staff,
            'sender_name' => $senderName,
            'message' => $message->message,
            'created_at' => $message->created_at->toISOString(),
        ];
    }

    /**
     * Get user's support tickets
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = SupportTicket::where('user_id', $user->id);
            
            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter by priority
            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }
            
            // Search by subject/message
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%")
                      ->orWhere('message', 'like', "%{$search}%");
                });
            }
            
            $tickets = $query->orderBy('created_at', 'desc')
                            ->paginate($request->get('per_page', 10));
            
            return response()->json([
                'success' => true,
                'data' => $tickets
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get support tickets: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load support tickets'
            ], 500);
        }
    }

    /**
     * Create a new support ticket
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'required|string|max:255',
                'message' => 'required|string|max:2000',
                'priority' => 'required|in:low,medium,high,urgent',
                'category' => 'required|in:technical,billing,general,feature,bug'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            $ticket = SupportTicket::create([
                'user_id' => $user->id,
                'ticket_number' => 'SUP-' . strtoupper(uniqid()),
                'subject' => $request->subject,
                'message' => $request->message,
                'priority' => $request->priority,
                'category' => $request->category,
                'status' => 'open'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Support ticket created successfully',
                'data' => $ticket
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create support ticket: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create support ticket'
            ], 500);
        }
    }

    /**
     * Get a specific support ticket
     */
    public function show(SupportTicket $ticket): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user owns the ticket or is admin
            if ($ticket->user_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to ticket'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $ticket
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get support ticket: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load support ticket'
            ], 500);
        }
    }

    /**
     * Update a support ticket
     */
    public function update(Request $request, SupportTicket $ticket): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user owns the ticket or is admin
            if ($ticket->user_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to ticket'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'subject' => 'sometimes|string|max:255',
                'message' => 'sometimes|string|max:2000',
                'priority' => 'sometimes|in:low,medium,high,urgent',
                'status' => 'sometimes|in:open,in_progress,resolved,closed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $ticket->update($request->only(['subject', 'message', 'priority', 'status']));

            return response()->json([
                'success' => true,
                'message' => 'Support ticket updated successfully',
                'data' => $ticket
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update support ticket: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update support ticket'
            ], 500);
        }
    }

    /**
     * Delete a support ticket
     */
    public function destroy(SupportTicket $ticket): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user owns the ticket or is admin
            if ($ticket->user_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to ticket'
                ], 403);
            }

            $ticket->delete();

            return response()->json([
                'success' => true,
                'message' => 'Support ticket deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete support ticket: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete support ticket'
            ], 500);
        }
    }

    /**
     * Get support statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if ($user->role === 'admin') {
                // Admin sees all tickets stats
                $stats = [
                    'total' => SupportTicket::count(),
                    'open' => SupportTicket::where('status', 'open')->count(),
                    'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
                    'resolved' => SupportTicket::where('status', 'resolved')->count(),
                    'closed' => SupportTicket::where('status', 'closed')->count(),
                    'high_priority' => SupportTicket::where('priority', 'high')->count(),
                    'urgent' => SupportTicket::where('priority', 'urgent')->count()
                ];
            } else {
                // Users see only their tickets stats
                $stats = [
                    'total' => SupportTicket::where('user_id', $user->id)->count(),
                    'open' => SupportTicket::where('user_id', $user->id)->where('status', 'open')->count(),
                    'in_progress' => SupportTicket::where('user_id', $user->id)->where('status', 'in_progress')->count(),
                    'resolved' => SupportTicket::where('user_id', $user->id)->where('status', 'resolved')->count(),
                    'closed' => SupportTicket::where('user_id', $user->id)->where('status', 'closed')->count()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get support stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load support statistics'
            ], 500);
        }
    }
} 