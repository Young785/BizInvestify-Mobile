import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/api_service.dart';

// Message Model
class Message {
  final int id;
  final int conversationId;
  final int senderId;
  final String content;
  final String messageType; // text, image, file
  final bool isRead;
  final DateTime createdAt;

  Message({
    required this.id,
    required this.conversationId,
    required this.senderId,
    required this.content,
    required this.messageType,
    required this.isRead,
    required this.createdAt,
  });

  factory Message.fromJson(Map<String, dynamic> json) {
    return Message(
      id: json['id'],
      conversationId: json['conversation_id'],
      senderId: json['sender_id'],
      content: json['content'],
      messageType: json['message_type'] ?? 'text',
      isRead: json['is_read'] ?? false,
      createdAt: DateTime.parse(json['created_at']),
    );
  }
}

// Conversation Model
class Conversation {
  final int id;
  final int participant1Id;
  final int participant2Id;
  final String participant1Name;
  final String participant2Name;
  final String? participant1Avatar;
  final String? participant2Avatar;
  final Message? lastMessage;
  final int unreadCount;
  final DateTime updatedAt;

  Conversation({
    required this.id,
    required this.participant1Id,
    required this.participant2Id,
    required this.participant1Name,
    required this.participant2Name,
    this.participant1Avatar,
    this.participant2Avatar,
    this.lastMessage,
    required this.unreadCount,
    required this.updatedAt,
  });

  String get recipientName => participant2Name;
  String? get recipientAvatar => participant2Avatar;

  factory Conversation.fromJson(Map<String, dynamic> json) {
    return Conversation(
      id: json['id'],
      participant1Id: json['participant1_id'],
      participant2Id: json['participant2_id'],
      participant1Name: json['participant1_name'],
      participant2Name: json['participant2_name'],
      participant1Avatar: json['participant1_avatar'],
      participant2Avatar: json['participant2_avatar'],
      lastMessage: json['last_message'] != null
          ? Message.fromJson(json['last_message'])
          : null,
      unreadCount: json['unread_count'] ?? 0,
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }
}

// Messaging State
class MessagingState {
  final List<Conversation> conversations;
  final List<Message> messages;
  final bool isLoading;
  final String? error;
  final int? currentConversationId;

  const MessagingState({
    this.conversations = const [],
    this.messages = const [],
    this.isLoading = false,
    this.error,
    this.currentConversationId,
  });

  MessagingState copyWith({
    List<Conversation>? conversations,
    List<Message>? messages,
    bool? isLoading,
    String? error,
    int? currentConversationId,
  }) {
    return MessagingState(
      conversations: conversations ?? this.conversations,
      messages: messages ?? this.messages,
      isLoading: isLoading ?? this.isLoading,
      error: error ?? this.error,
      currentConversationId: currentConversationId ?? this.currentConversationId,
    );
  }
}

// Messaging Notifier
class MessagingNotifier extends StateNotifier<MessagingState> {
  final ApiService _apiService;

  MessagingNotifier(this._apiService) : super(const MessagingState());

  // Load Conversations
  Future<void> loadConversations() async {
    state = state.copyWith(isLoading: true, error: null);
    
    try {
      final conversationsData = await _apiService.getConversations();
      final conversations = conversationsData.map((json) => Conversation.fromJson(json)).toList();
      
      state = state.copyWith(
        conversations: conversations,
        isLoading: false,
      );
    } catch (e) {
      state = state.copyWith(
        error: e.toString(),
        isLoading: false,
      );
    }
  }

  // Load Messages for a conversation
  Future<void> loadMessages(int conversationId) async {
    state = state.copyWith(isLoading: true, error: null, currentConversationId: conversationId);
    
    try {
      final messagesData = await _apiService.getMessages(conversationId);
      final messages = messagesData.map((json) => Message.fromJson(json)).toList();
      
      state = state.copyWith(
        messages: messages,
        isLoading: false,
      );
    } catch (e) {
      state = state.copyWith(
        error: e.toString(),
        isLoading: false,
      );
    }
  }

  // Send Message
  Future<void> sendMessage(int conversationId, String content) async {
    try {
      final messageData = await _apiService.sendMessage(conversationId, content);
      final newMessage = Message.fromJson(messageData);
      
      // Add message to current list
      final updatedMessages = [...state.messages, newMessage];
      
      state = state.copyWith(messages: updatedMessages);
      
      // Reload conversations to update last message
      await loadConversations();
    } catch (e) {
      state = state.copyWith(error: e.toString());
    }
  }

  // Clear error
  void clearError() {
    state = state.copyWith(error: null);
  }

  // Clear messages
  void clearMessages() {
    state = state.copyWith(messages: [], currentConversationId: null);
  }
}

// Providers
final messagingProvider = StateNotifierProvider<MessagingNotifier, MessagingState>((ref) {
  return MessagingNotifier(apiService);
});

final conversationsProvider = Provider<List<Conversation>>((ref) {
  return ref.watch(messagingProvider).conversations;
});

final messagesProvider = Provider<List<Message>>((ref) {
  return ref.watch(messagingProvider).messages;
});

final messagingErrorProvider = Provider<String?>((ref) {
  return ref.watch(messagingProvider).error;
});
