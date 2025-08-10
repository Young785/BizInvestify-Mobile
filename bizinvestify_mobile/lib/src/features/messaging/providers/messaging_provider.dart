import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/api_service.dart';
import '../models/message_model.dart';
import '../models/conversation_model.dart';

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
      final messageData = await _apiService.sendMessage({
        'conversation_id': conversationId,
        'content': content,
      });
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
