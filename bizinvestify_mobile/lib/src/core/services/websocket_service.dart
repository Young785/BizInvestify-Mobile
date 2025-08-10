import 'dart:convert';
import 'dart:io';
import 'package:web_socket_channel/web_socket_channel.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class WebSocketService {
  static const String _baseUrl = 'ws://127.0.0.1:6001';
  static const String _tokenKey = 'auth_token';
  
  WebSocketChannel? _channel;
  final FlutterSecureStorage _storage = const FlutterSecureStorage();
  bool _isConnected = false;
  
  // Callbacks for different events
  Function(Map<String, dynamic>)? onMessageReceived;
  Function(Map<String, dynamic>)? onNotificationReceived;
  Function(Map<String, dynamic>)? onChatMessageReceived;
  Function(Map<String, dynamic>)? onLiveUpdateReceived;
  Function()? onConnected;
  Function()? onDisconnected;
  Function(String)? onError;

  bool get isConnected => _isConnected;

  // Connect to WebSocket server
  Future<void> connect() async {
    try {
      final token = await _storage.read(key: _tokenKey);
      if (token == null) {
        throw Exception('No authentication token found');
      }

      final uri = Uri.parse('$_baseUrl?token=$token');
      _channel = WebSocketChannel.connect(uri);
      
      _channel!.stream.listen(
        (data) => _handleMessage(data),
        onError: (error) {
          _isConnected = false;
          onError?.call(error.toString());
        },
        onDone: () {
          _isConnected = false;
          onDisconnected?.call();
        },
      );

      _isConnected = true;
      onConnected?.call();
      
      // Send connection confirmation
      _sendMessage({
        'type': 'connection',
        'data': {'status': 'connected'}
      });
      
    } catch (e) {
      _isConnected = false;
      onError?.call(e.toString());
    }
  }

  // Disconnect from WebSocket server
  void disconnect() {
    _channel?.sink.close();
    _isConnected = false;
    onDisconnected?.call();
  }

  // Send message to server
  void _sendMessage(Map<String, dynamic> message) {
    if (_isConnected && _channel != null) {
      _channel!.sink.add(jsonEncode(message));
    }
  }

  // Handle incoming messages
  void _handleMessage(dynamic data) {
    try {
      final message = jsonDecode(data.toString());
      final type = message['type'];

      switch (type) {
        case 'message':
          onMessageReceived?.call(message);
          break;
        case 'notification':
          onNotificationReceived?.call(message);
          break;
        case 'chat_message':
          onChatMessageReceived?.call(message);
          break;
        case 'live_update':
          onLiveUpdateReceived?.call(message);
          break;
        case 'ping':
          // Respond to ping with pong
          _sendMessage({'type': 'pong'});
          break;
        default:
          onMessageReceived?.call(message);
      }
    } catch (e) {
      onError?.call('Error parsing message: $e');
    }
  }

  // Send chat message
  void sendChatMessage(int conversationId, String content) {
    _sendMessage({
      'type': 'chat_message',
      'data': {
        'conversation_id': conversationId,
        'content': content,
        'timestamp': DateTime.now().toIso8601String(),
      }
    });
  }

  // Join chat room
  void joinChatRoom(int conversationId) {
    _sendMessage({
      'type': 'join_room',
      'data': {'conversation_id': conversationId}
    });
  }

  // Leave chat room
  void leaveChatRoom(int conversationId) {
    _sendMessage({
      'type': 'leave_room',
      'data': {'conversation_id': conversationId}
    });
  }

  // Subscribe to notifications
  void subscribeToNotifications() {
    _sendMessage({
      'type': 'subscribe_notifications',
      'data': {'user_id': 'current_user'}
    });
  }

  // Subscribe to live updates
  void subscribeToLiveUpdates() {
    _sendMessage({
      'type': 'subscribe_updates',
      'data': {'user_id': 'current_user'}
    });
  }

  // Mark message as read
  void markMessageAsRead(int messageId) {
    _sendMessage({
      'type': 'mark_read',
      'data': {'message_id': messageId}
    });
  }

  // Typing indicator
  void sendTypingIndicator(int conversationId, bool isTyping) {
    _sendMessage({
      'type': 'typing',
      'data': {
        'conversation_id': conversationId,
        'is_typing': isTyping
      }
    });
  }

  // Online status
  void updateOnlineStatus(bool isOnline) {
    _sendMessage({
      'type': 'online_status',
      'data': {'is_online': isOnline}
    });
  }
}

// Provider for WebSocket service
final webSocketServiceProvider = Provider<WebSocketService>((ref) {
  return WebSocketService();
});

// Provider for WebSocket connection status
final webSocketConnectionProvider = StateProvider<bool>((ref) {
  return false;
});
