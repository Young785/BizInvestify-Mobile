import 'dart:developer';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'firebase_service.dart';

/// Notification types for the BizInvestify app
enum NotificationType {
  investmentUpdate('investment_update'),
  newBusiness('new_business'),
  portfolioUpdate('portfolio_update'),
  marketUpdate('market_update'),
  systemAlert('system_alert'),
  marketingMessage('marketing_message');

  const NotificationType(this.value);
  final String value;

  static NotificationType fromString(String value) {
    return NotificationType.values.firstWhere(
      (type) => type.value == value,
      orElse: () => NotificationType.systemAlert,
    );
  }
}

/// Notification priority levels
enum NotificationPriority {
  low,
  normal,
  high,
  urgent;
}

/// Notification data model
class NotificationData {
  final String id;
  final NotificationType type;
  final String title;
  final String body;
  final Map<String, dynamic> data;
  final NotificationPriority priority;
  final DateTime timestamp;
  final bool isRead;

  const NotificationData({
    required this.id,
    required this.type,
    required this.title,
    required this.body,
    required this.data,
    this.priority = NotificationPriority.normal,
    required this.timestamp,
    this.isRead = false,
  });

  NotificationData copyWith({
    String? id,
    NotificationType? type,
    String? title,
    String? body,
    Map<String, dynamic>? data,
    NotificationPriority? priority,
    DateTime? timestamp,
    bool? isRead,
  }) {
    return NotificationData(
      id: id ?? this.id,
      type: type ?? this.type,
      title: title ?? this.title,
      body: body ?? this.body,
      data: data ?? this.data,
      priority: priority ?? this.priority,
      timestamp: timestamp ?? this.timestamp,
      isRead: isRead ?? this.isRead,
    );
  }
}

/// Notification service for handling push notifications and in-app notifications
class NotificationService {
  final FirebaseService _firebaseService;
  final List<NotificationData> _notifications = [];
  final List<VoidCallback> _listeners = [];

  NotificationService(this._firebaseService);

  /// Get all notifications
  List<NotificationData> get notifications => List.unmodifiable(_notifications);

  /// Get unread notification count
  int get unreadCount => _notifications.where((n) => !n.isRead).length;

  /// Add listener for notification updates
  void addListener(VoidCallback listener) {
    _listeners.add(listener);
  }

  /// Remove listener
  void removeListener(VoidCallback listener) {
    _listeners.remove(listener);
  }

  /// Notify listeners of changes
  void _notifyListeners() {
    for (final listener in _listeners) {
      listener();
    }
  }

  /// Initialize notification service
  Future<void> initialize() async {
    await _firebaseService.initialize();
    await _setupNotificationTopics();
    log('NotificationService initialized');
  }

  /// Set up notification topics based on user preferences
  Future<void> _setupNotificationTopics() async {
    // Subscribe to general topics
    await _firebaseService.subscribeToTopic('all_users');
    await _firebaseService.subscribeToTopic('market_updates');
    
    // TODO: Subscribe to user-specific topics based on preferences
    // await _firebaseService.subscribeToTopic('user_${userId}');
    // await _firebaseService.subscribeToTopic('industry_${userIndustry}');
  }

  /// Subscribe to user-specific topics
  Future<void> subscribeToUserTopics({
    required String userId,
    required List<String> industries,
    required List<String> interests,
  }) async {
    // User-specific topic
    await _firebaseService.subscribeToTopic('user_$userId');
    
    // Industry-specific topics
    for (final industry in industries) {
      await _firebaseService.subscribeToTopic('industry_${industry.toLowerCase()}');
    }
    
    // Interest-based topics
    for (final interest in interests) {
      await _firebaseService.subscribeToTopic('interest_${interest.toLowerCase()}');
    }
    
    log('Subscribed to user topics for $userId');
  }

  /// Unsubscribe from topics when user preferences change
  Future<void> updateTopicSubscriptions({
    required List<String> oldIndustries,
    required List<String> newIndustries,
    required List<String> oldInterests,
    required List<String> newInterests,
  }) async {
    // Unsubscribe from old industries
    for (final industry in oldIndustries) {
      if (!newIndustries.contains(industry)) {
        await _firebaseService.unsubscribeFromTopic('industry_${industry.toLowerCase()}');
      }
    }
    
    // Subscribe to new industries
    for (final industry in newIndustries) {
      if (!oldIndustries.contains(industry)) {
        await _firebaseService.subscribeToTopic('industry_${industry.toLowerCase()}');
      }
    }
    
    // Unsubscribe from old interests
    for (final interest in oldInterests) {
      if (!newInterests.contains(interest)) {
        await _firebaseService.unsubscribeFromTopic('interest_${interest.toLowerCase()}');
      }
    }
    
    // Subscribe to new interests
    for (final interest in newInterests) {
      if (!oldInterests.contains(interest)) {
        await _firebaseService.subscribeToTopic('interest_${interest.toLowerCase()}');
      }
    }
  }

  /// Add a notification to the local list
  void addNotification(NotificationData notification) {
    _notifications.insert(0, notification); // Add to beginning for newest first
    
    // Keep only last 100 notifications
    if (_notifications.length > 100) {
      _notifications.removeRange(100, _notifications.length);
    }
    
    _notifyListeners();
  }

  /// Mark notification as read
  void markAsRead(String notificationId) {
    final index = _notifications.indexWhere((n) => n.id == notificationId);
    if (index != -1) {
      _notifications[index] = _notifications[index].copyWith(isRead: true);
      _notifyListeners();
    }
  }

  /// Mark all notifications as read
  void markAllAsRead() {
    for (int i = 0; i < _notifications.length; i++) {
      _notifications[i] = _notifications[i].copyWith(isRead: true);
    }
    _notifyListeners();
  }

  /// Remove notification
  void removeNotification(String notificationId) {
    _notifications.removeWhere((n) => n.id == notificationId);
    _notifyListeners();
  }

  /// Clear all notifications
  void clearAllNotifications() {
    _notifications.clear();
    _notifyListeners();
  }

  /// Create notification for investment updates
  NotificationData createInvestmentUpdateNotification({
    required String investmentId,
    required String businessName,
    required String status,
  }) {
    String title;
    String body;
    
    switch (status.toLowerCase()) {
      case 'approved':
        title = 'Investment Approved! 🎉';
        body = 'Your investment in $businessName has been approved.';
        break;
      case 'rejected':
        title = 'Investment Update';
        body = 'Your investment proposal for $businessName was not accepted.';
        break;
      case 'completed':
        title = 'Investment Completed! ✅';
        body = 'Your investment in $businessName has been completed successfully.';
        break;
      default:
        title = 'Investment Update';
        body = 'There\'s an update on your investment in $businessName.';
    }
    
    return NotificationData(
      id: 'investment_${investmentId}_${DateTime.now().millisecondsSinceEpoch}',
      type: NotificationType.investmentUpdate,
      title: title,
      body: body,
      data: {
        'investment_id': investmentId,
        'business_name': businessName,
        'status': status,
      },
      priority: NotificationPriority.high,
      timestamp: DateTime.now(),
    );
  }

  /// Create notification for new business opportunities
  NotificationData createNewBusinessNotification({
    required String businessId,
    required String businessName,
    required String industry,
    required double valuation,
  }) {
    return NotificationData(
      id: 'business_${businessId}_${DateTime.now().millisecondsSinceEpoch}',
      type: NotificationType.newBusiness,
      title: 'New Investment Opportunity! 💼',
      body: '$businessName in $industry is seeking investment. Valued at \$${(valuation / 1000000).toStringAsFixed(1)}M.',
      data: {
        'business_id': businessId,
        'business_name': businessName,
        'industry': industry,
        'valuation': valuation,
      },
      priority: NotificationPriority.normal,
      timestamp: DateTime.now(),
    );
  }

  /// Create notification for portfolio updates
  NotificationData createPortfolioUpdateNotification({
    required String message,
    required Map<String, dynamic> portfolioData,
  }) {
    return NotificationData(
      id: 'portfolio_${DateTime.now().millisecondsSinceEpoch}',
      type: NotificationType.portfolioUpdate,
      title: 'Portfolio Update 📈',
      body: message,
      data: portfolioData,
      priority: NotificationPriority.normal,
      timestamp: DateTime.now(),
    );
  }

  /// Create notification for market updates
  NotificationData createMarketUpdateNotification({
    required String title,
    required String message,
    Map<String, dynamic>? additionalData,
  }) {
    return NotificationData(
      id: 'market_${DateTime.now().millisecondsSinceEpoch}',
      type: NotificationType.marketUpdate,
      title: title,
      body: message,
      data: additionalData ?? {},
      priority: NotificationPriority.low,
      timestamp: DateTime.now(),
    );
  }

  /// Show in-app notification
  void showInAppNotification(
    BuildContext context,
    NotificationData notification,
  ) {
    final color = _getNotificationColor(notification.type);
    final icon = _getNotificationIcon(notification.type);
    
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(icon, color: Colors.white),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    notification.title,
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                  Text(
                    notification.body,
                    style: const TextStyle(color: Colors.white),
                  ),
                ],
              ),
            ),
          ],
        ),
        backgroundColor: color,
        duration: const Duration(seconds: 4),
        action: SnackBarAction(
          label: 'View',
          textColor: Colors.white,
          onPressed: () => _handleNotificationTap(context, notification),
        ),
      ),
    );
  }

  /// Get notification color based on type
  Color _getNotificationColor(NotificationType type) {
    switch (type) {
      case NotificationType.investmentUpdate:
        return Colors.green;
      case NotificationType.newBusiness:
        return Colors.blue;
      case NotificationType.portfolioUpdate:
        return Colors.orange;
      case NotificationType.marketUpdate:
        return Colors.purple;
      case NotificationType.systemAlert:
        return Colors.red;
      case NotificationType.marketingMessage:
        return Colors.teal;
    }
  }

  /// Get notification icon based on type
  IconData _getNotificationIcon(NotificationType type) {
    switch (type) {
      case NotificationType.investmentUpdate:
        return Icons.trending_up;
      case NotificationType.newBusiness:
        return Icons.business_center;
      case NotificationType.portfolioUpdate:
        return Icons.account_balance_wallet;
      case NotificationType.marketUpdate:
        return Icons.show_chart;
      case NotificationType.systemAlert:
        return Icons.warning;
      case NotificationType.marketingMessage:
        return Icons.campaign;
    }
  }

  /// Handle notification tap
  void _handleNotificationTap(BuildContext context, NotificationData notification) {
    // Mark as read
    markAsRead(notification.id);
    
    // Navigate based on notification type
    switch (notification.type) {
      case NotificationType.investmentUpdate:
        // Navigate to investment details or portfolio
        _navigateToInvestment(context, notification.data['investment_id']);
        break;
      case NotificationType.newBusiness:
        // Navigate to business details
        _navigateToBusiness(context, notification.data['business_id']);
        break;
      case NotificationType.portfolioUpdate:
        // Navigate to portfolio
        _navigateToPortfolio(context);
        break;
      case NotificationType.marketUpdate:
        // Navigate to marketplace
        _navigateToMarketplace(context);
        break;
      case NotificationType.systemAlert:
      case NotificationType.marketingMessage:
        // Navigate to home or specific screen based on data
        _navigateToHome(context);
        break;
    }
  }

  /// Navigation methods
  void _navigateToInvestment(BuildContext context, String? investmentId) {
    if (investmentId != null) {
      context.push('/investments');
    }
  }

  void _navigateToBusiness(BuildContext context, String? businessId) {
    if (businessId != null) {
      context.push('/business/$businessId');
    }
  }

  void _navigateToPortfolio(BuildContext context) {
    context.push('/portfolio');
  }

  void _navigateToMarketplace(BuildContext context) {
    context.push('/marketplace');
  }

  void _navigateToHome(BuildContext context) {
    context.go('/home');
  }

  /// Get FCM token for this device
  Future<String?> getDeviceToken() async {
    return await _firebaseService.getToken();
  }
}

/// Riverpod providers
final notificationServiceProvider = Provider<NotificationService>((ref) {
  final firebaseService = ref.watch(firebaseServiceProvider);
  return NotificationService(firebaseService);
});

/// Provider for notification count
final notificationCountProvider = StateProvider<int>((ref) => 0);

/// Provider for notifications list
final notificationsProvider = StateProvider<List<NotificationData>>((ref) => []);
