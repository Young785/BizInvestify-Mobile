import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_dimensions.dart';
import '../providers/messaging_provider.dart';
import '../widgets/conversation_tile.dart';
import '../models/conversation_model.dart';
import 'chat_screen.dart';
import '../../auth/providers/auth_provider.dart';

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
        onPressed: () async {
          final controller = TextEditingController();
          final ok = await showDialog<bool>(
            context: context,
            builder: (context) {
              return AlertDialog(
                title: const Text('Start New Chat'),
                content: TextField(
                  controller: controller,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(
                    labelText: 'Recipient User ID',
                    hintText: 'e.g. 42',
                  ),
                ),
                actions: [
                  TextButton(onPressed: () => Navigator.of(context).pop(false), child: const Text('Cancel')),
                  ElevatedButton(onPressed: () => Navigator.of(context).pop(true), child: const Text('Create')),
                ],
              );
            },
          );
          if (ok == true) {
            final userId = int.tryParse(controller.text.trim());
            if (userId == null) return;
            try {
              await apiService.createConversation({'recipient_id': userId});
              await ref.read(messagingProvider.notifier).loadConversations();
              if (!mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Conversation created')));
            } catch (e) {
              if (!mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
            }
          }
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
