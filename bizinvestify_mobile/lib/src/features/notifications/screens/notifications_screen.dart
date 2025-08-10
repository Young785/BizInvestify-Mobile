import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/services/api_service.dart';

class NotificationsScreen extends ConsumerStatefulWidget {
  const NotificationsScreen({super.key});

  @override
  ConsumerState<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends ConsumerState<NotificationsScreen> {
  bool _loading = false;
  String? _error;
  List<Map<String, dynamic>> _items = [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final data = await apiService.getNotifications();
      setState(() => _items = data);
    } catch (e) {
      setState(() => _error = e.toString());
    } finally {
      setState(() => _loading = false);
    }
  }

  Future<void> _markAllRead() async {
    try {
      await apiService.markAllNotificationsAsRead();
      await _load();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('All notifications marked read')));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  Future<void> _markRead(int id) async {
    try {
      await apiService.markNotificationAsRead(id);
      await _load();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background200,
      appBar: AppBar(
        title: const Text('Notifications'),
        backgroundColor: Colors.white,
        elevation: 1,
        actions: [
          IconButton(
            icon: const Icon(Icons.mark_email_read_outlined),
            onPressed: _loading ? null : _markAllRead,
            tooltip: 'Mark all as read',
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? ListView(children: [Padding(padding: const EdgeInsets.all(16), child: Text(_error!, style: const TextStyle(color: Colors.red)))])
                : _items.isEmpty
                    ? ListView(children: [
                        const SizedBox(height: 80),
                        Icon(Icons.notifications_none, size: 72, color: Colors.grey[400]),
                        const SizedBox(height: 12),
                        Center(child: Text('No notifications', style: TextStyle(color: Colors.grey[600]))),
                      ])
                    : ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemCount: _items.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 10),
                        itemBuilder: (context, index) {
                          final n = _items[index];
                          final id = int.tryParse((n['id'] ?? '').toString()) ?? 0;
                          final title = (n['title'] ?? 'Notification').toString();
                          final body = (n['body'] ?? n['message'] ?? '').toString();
                          final isRead = (n['read_at'] ?? n['is_read'] ?? false) != null && (n['read_at'] != null || n['is_read'] == true);
                          final createdAt = (n['created_at'] ?? '').toString();

                          return Container(
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: Colors.grey[200]!),
                            ),
                            child: ListTile(
                              leading: CircleAvatar(
                                backgroundColor: isRead ? Colors.grey[200] : AppColors.primary500.withOpacity(0.15),
                                child: Icon(isRead ? Icons.mark_email_read : Icons.notifications_active, color: isRead ? Colors.grey[600] : AppColors.primary500),
                              ),
                              title: Text(title, style: const TextStyle(fontWeight: FontWeight.w700)),
                              subtitle: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  if (body.isNotEmpty) Text(body),
                                  if (createdAt.isNotEmpty)
                                    Text(createdAt, style: TextStyle(color: Colors.grey[600], fontSize: 12)),
                                ],
                              ),
                              trailing: !isRead
                                  ? IconButton(
                                      icon: const Icon(Icons.done_all),
                                      onPressed: () => _markRead(id),
                                      tooltip: 'Mark as read',
                                    )
                                  : null,
                            ),
                          );
                        },
                      ),
      ),
    );
  }
}
