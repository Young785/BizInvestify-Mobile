import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_dimensions.dart';
import '../providers/messaging_provider.dart';
import '../widgets/conversation_tile.dart';
import 'chat_screen.dart';

class MessagesScreen extends ConsumerStatefulWidget {
  const MessagesScreen({super.key});

  @override
  ConsumerState<MessagesScreen> createState() => _MessagesScreenState();
}

class _MessagesScreenState extends ConsumerState<MessagesScreen> {
  @override
  void initState() {
    super.initState();
    // Load conversations when screen initializes
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(messagingProvider.notifier).loadConversations();
    });
  }

  @override
  Widget build(BuildContext context) {
    final messagingState = ref.watch(messagingProvider);
    
    return Scaffold(
      backgroundColor: AppColors.background200,
      appBar: AppBar(
        title: const Text('Messages'),
        backgroundColor: Colors.white,
        elevation: 1,
        actions: [
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: () {
              // TODO: Implement search conversations
            },
          ),
          PopupMenuButton(
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'mark_all_read',
                child: Text('Mark All as Read'),
              ),
              const PopupMenuItem(
                value: 'clear_all',
                child: Text('Clear All'),
              ),
            ],
            onSelected: (value) {
              // TODO: Handle menu selection
            },
          ),
        ],
      ),
      body: messagingState.isLoading
          ? const Center(child: CircularProgressIndicator())
          : messagingState.conversations.isEmpty
              ? _buildEmptyState()
              : _buildConversationsList(messagingState),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // TODO: Navigate to new conversation screen
        },
        backgroundColor: AppColors.primary500,
        child: const Icon(Icons.chat, color: Colors.white),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.chat_bubble_outline,
            size: 80,
            color: Colors.grey[400],
          ),
          const SizedBox(height: 16),
          Text(
            'No conversations yet',
            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
              color: Colors.grey[600],
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Start a conversation with other users',
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
              color: Colors.grey[500],
            ),
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: () {
              // TODO: Navigate to new conversation screen
            },
            icon: const Icon(Icons.add),
            label: const Text('Start New Chat'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary500,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildConversationsList(MessagingState state) {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: state.conversations.length,
      itemBuilder: (context, index) {
        final conversation = state.conversations[index];
        return Padding(
          padding: const EdgeInsets.only(bottom: 8),
          child: ConversationTile(
            conversation: conversation,
            onTap: () {
              // Navigate to chat screen
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (context) => ChatScreen(
                    conversation: conversation,
                  ),
                ),
              );
            },
          ),
        );
      },
    );
  }
}
