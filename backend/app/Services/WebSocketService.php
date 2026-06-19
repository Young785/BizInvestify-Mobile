<?php

namespace App\Services;

use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;

class WebSocketService implements MessageComponentInterface
{
    protected $clients;
    protected $userConnections;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        Log::info("New connection! ({$conn->resourceId})");
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        
        if (!$data || !isset($data['type'])) {
            return;
        }

        switch ($data['type']) {
            case 'auth':
                $this->handleAuth($from, $data);
                break;
            case 'message':
                $this->handleMessage($from, $data);
                break;
            case 'typing':
                $this->handleTyping($from, $data);
                break;
            case 'read':
                $this->handleRead($from, $data);
                break;
            case 'join_conversation':
                $this->handleJoinConversation($from, $data);
                break;
            case 'leave_conversation':
                $this->handleLeaveConversation($from, $data);
                break;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        
        // Remove user connection mapping
        foreach ($this->userConnections as $userId => $connection) {
            if ($connection === $conn) {
                unset($this->userConnections[$userId]);
                break;
            }
        }
        
        Log::info("Connection {$conn->resourceId} has disconnected");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        Log::error("An error has occurred: {$e->getMessage()}");
        $conn->close();
    }

    protected function handleAuth($connection, $data)
    {
        if (!isset($data['token'])) {
            $connection->send(json_encode([
                'type' => 'error',
                'message' => 'Authentication token required'
            ]));
            return;
        }

        try {
            // Verify token and get user
            $user = $this->verifyToken($data['token']);
            if ($user) {
                $this->userConnections[$user->id] = $connection;
                $connection->send(json_encode([
                    'type' => 'auth_success',
                    'user_id' => $user->id
                ]));
            } else {
                $connection->send(json_encode([
                    'type' => 'auth_error',
                    'message' => 'Invalid token'
                ]));
            }
        } catch (\Exception $e) {
            Log::error("Auth error: " . $e->getMessage());
            $connection->send(json_encode([
                'type' => 'auth_error',
                'message' => 'Authentication failed'
            ]));
        }
    }

    protected function handleMessage($connection, $data)
    {
        $userId = $this->getUserIdFromConnection($connection);
        if (!$userId) {
            return;
        }

        if (!isset($data['conversation_id']) || !isset($data['content'])) {
            return;
        }

        try {
            // Save message to database
            $message = Message::create([
                'conversation_id' => $data['conversation_id'],
                'sender_id' => $userId,
                'content' => $data['content'],
                'type' => $data['type'] ?? 'text',
                'is_read' => false
            ]);

            // Get conversation participants
            $conversation = Conversation::with('participants')->find($data['conversation_id']);
            if (!$conversation) {
                return;
            }

            // Send message to all participants
            $messageData = [
                'type' => 'new_message',
                'message' => [
                    'id' => $message->id,
                    'conversation_id' => $message->conversation_id,
                    'sender_id' => $message->sender_id,
                    'content' => $message->content,
                    'type' => $message->type,
                    'created_at' => $message->created_at->toISOString()
                ]
            ];

            foreach ($conversation->participants as $participant) {
                if (isset($this->userConnections[$participant->id])) {
                    $this->userConnections[$participant->id]->send(json_encode($messageData));
                }
            }

        } catch (\Exception $e) {
            Log::error("Message handling error: " . $e->getMessage());
        }
    }

    protected function handleTyping($connection, $data)
    {
        $userId = $this->getUserIdFromConnection($connection);
        if (!$userId || !isset($data['conversation_id'])) {
            return;
        }

        $conversation = Conversation::with('participants')->find($data['conversation_id']);
        if (!$conversation) {
            return;
        }

        $typingData = [
            'type' => 'typing',
            'conversation_id' => $data['conversation_id'],
            'user_id' => $userId,
            'is_typing' => $data['is_typing'] ?? true
        ];

        foreach ($conversation->participants as $participant) {
            if ($participant->id !== $userId && isset($this->userConnections[$participant->id])) {
                $this->userConnections[$participant->id]->send(json_encode($typingData));
            }
        }
    }

    protected function handleRead($connection, $data)
    {
        $userId = $this->getUserIdFromConnection($connection);
        if (!$userId || !isset($data['conversation_id'])) {
            return;
        }

        try {
            // Mark messages as read
            Message::where('conversation_id', $data['conversation_id'])
                ->where('sender_id', '!=', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            // Notify other participants
            $conversation = Conversation::with('participants')->find($data['conversation_id']);
            if (!$conversation) {
                return;
            }

            $readData = [
                'type' => 'messages_read',
                'conversation_id' => $data['conversation_id'],
                'user_id' => $userId
            ];

            foreach ($conversation->participants as $participant) {
                if ($participant->id !== $userId && isset($this->userConnections[$participant->id])) {
                    $this->userConnections[$participant->id]->send(json_encode($readData));
                }
            }

        } catch (\Exception $e) {
            Log::error("Read handling error: " . $e->getMessage());
        }
    }

    protected function handleJoinConversation($connection, $data)
    {
        $userId = $this->getUserIdFromConnection($connection);
        if (!$userId || !isset($data['conversation_id'])) {
            return;
        }

        // Verify user is part of conversation
        $conversation = Conversation::with('participants')->find($data['conversation_id']);
        if (!$conversation || !$conversation->participants->contains('id', $userId)) {
            return;
        }

        // Send conversation history
        $messages = Message::where('conversation_id', $data['conversation_id'])
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->reverse();

        $connection->send(json_encode([
            'type' => 'conversation_history',
            'conversation_id' => $data['conversation_id'],
            'messages' => $messages
        ]));
    }

    protected function handleLeaveConversation($connection, $data)
    {
        // Handle leaving conversation (if needed)
        Log::info("User left conversation: " . ($data['conversation_id'] ?? 'unknown'));
    }

    protected function verifyToken($token)
    {
        try {
            if (! $token || ! is_string($token)) {
                return null;
            }

            $accessToken = PersonalAccessToken::findToken($token);
            if (! $accessToken) {
                return null;
            }

            $user = $accessToken->tokenable;
            return $user instanceof User ? $user : null;
        } catch (\Exception $e) {
            Log::warning('WebSocket token verification failed: '.$e->getMessage());

            return null;
        }
    }

    protected function getUserIdFromConnection($connection)
    {
        foreach ($this->userConnections as $userId => $conn) {
            if ($conn === $connection) {
                return $userId;
            }
        }
        return null;
    }

    public function broadcastToUser($userId, $data)
    {
        if (isset($this->userConnections[$userId])) {
            $this->userConnections[$userId]->send(json_encode($data));
        }
    }

    public function broadcastToUsers($userIds, $data)
    {
        foreach ($userIds as $userId) {
            $this->broadcastToUser($userId, $data);
        }
    }

    public function broadcastToAll($data)
    {
        foreach ($this->clients as $client) {
            $client->send(json_encode($data));
        }
    }
} 