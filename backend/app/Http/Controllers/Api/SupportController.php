<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupportController extends Controller
{
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