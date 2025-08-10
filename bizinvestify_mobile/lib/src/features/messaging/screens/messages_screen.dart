import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/constants/app_dimensions.dart';
import '../providers/messaging_provider.dart';
import '../widgets/conversation_tile.dart';

class MessagesScreen extends ConsumerStatefulWidget {
  const MessagesScreen({super.key});

  @override
  ConsumerState<MessagesScreen> createState() => _MessagesScreenState();
}

class _MessagesScreenState extends ConsumerState<MessagesScreen> {
  @override
  void initState() {
    super.initState();
    _loadConversations();
  }

  void _loadConversations() {
    ref.read(messagingProvider.notifier).loadConversations();
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
          IconButton(
            icon: const Icon(Icons.more_vert),
            onPressed: () {
              // TODO: Show more options
            },
          ),
        ],
      ),
      body: messagingState.isLoading
          ? const Center(child: CircularProgressIndicator())
          : messagingState.conversations.isEmpty
              ? _buildEmptyState()
              : RefreshIndicator(
                  onRefresh: () async {
                    _loadConversations();
                  },
                  child: ListView.builder(
                    padding: const EdgeInsets.all(AppDimensions.spacing16),
                    itemCount: messagingState.conversations.length,
                    itemBuilder: (context, index) {
                      final conversation = messagingState.conversations[index];
                      return Padding(
                        padding: const EdgeInsets.only(bottom: AppDimensions.spacing12),
                        child: ConversationTile(
                          conversation: conversation,
                          onTap: () {
                            // Navigate to chat screen
                            _navigateToChat(conversation);
                          },
                        ),
                      );
                    },
                  ),
                ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // TODO: Navigate to new conversation
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
            size: 64,
            color: AppColors.text400,
          ),
          const SizedBox(height: AppDimensions.spacing16),
          Text(
            'No Messages Yet',
            style: AppTypography.titleLarge.copyWith(
              color: AppColors.text600,
              fontWeight: AppTypography.semibold,
            ),
          ),
          const SizedBox(height: AppDimensions.spacing8),
          Text(
            'Start a conversation with sellers or investors',
            style: AppTypography.bodyMedium.copyWith(
              color: AppColors.text500,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: AppDimensions.spacing24),
          ElevatedButton.icon(
            onPressed: () {
              // TODO: Navigate to marketplace to start conversation
            },
            icon: const Icon(Icons.explore),
            label: const Text('Browse Marketplace'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary500,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(
                horizontal: AppDimensions.spacing24,
                vertical: AppDimensions.spacing12,
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _navigateToChat(dynamic conversation) {
    // TODO: Navigate to chat screen
    // Navigator.of(context).push(
    //   MaterialPageRoute(
    //     builder: (context) => ChatScreen(
    //       conversationId: conversation.id,
    //       recipientName: conversation.recipientName,
    //     ),
    //   ),
    // );
  }
}
