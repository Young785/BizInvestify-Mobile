import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import '../routing/app_router.dart';

class NotificationService {
  static final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();
        // static final FirebaseMessaging _firebaseMessaging = FirebaseMessaging.instance;
  // Do NOT eagerly access FirebaseMessaging on web. Use lazily within guarded code paths.
  static const FlutterSecureStorage _storage = FlutterSecureStorage();

  // Notification channels
  static const AndroidNotificationChannel _generalChannel = AndroidNotificationChannel(
    'bizinvestify_general',
    'General Notifications',
    description: 'General app notifications',
    importance: Importance.high,
  );

  static const AndroidNotificationChannel _chatChannel = AndroidNotificationChannel(
    'bizinvestify_chat',
    'Chat Messages',
    description: 'Chat and messaging notifications',
    importance: Importance.high,
    playSound: true,
    enableVibration: true,
  );

  static const AndroidNotificationChannel _transactionChannel = AndroidNotificationChannel(
    'bizinvestify_transactions',
    'Transactions',
    description: 'Payment and transaction notifications',
    importance: Importance.high,
    playSound: true,
    enableVibration: true,
  );

  // Initialize notification service
  static Future<void> initialize() async {
    // Web guard: flutter_local_notifications and firebase_messaging are not supported on web
    if (kIsWeb) {
      // Optionally, implement a web-specific notifications flow later (Browser Notifications API)
      return;
    }

    // Initialize local notifications
    const initializationSettings = InitializationSettings(
      android: AndroidInitializationSettings('@mipmap/ic_launcher'),
      iOS: DarwinInitializationSettings(
        requestAlertPermission: true,
        requestBadgePermission: true,
        requestSoundPermission: true,
      ),
    );

    await _localNotifications.initialize(
      initializationSettings,
      onDidReceiveNotificationResponse: _onNotificationTapped,
    );

    // Create notification channels
    await _localNotifications
        .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(_generalChannel);

    await _localNotifications
        .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(_chatChannel);

    await _localNotifications
        .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(_transactionChannel);

    // Request permission for push notifications
    final messaging = FirebaseMessaging.instance;
    final settings = await messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
      provisional: false,
    );

    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      // Get FCM token
      final token = await messaging.getToken();
      if (token != null) {
        await _storage.write(key: 'fcm_token', value: token);
        print('FCM Token: $token');
      }

      // Handle background messages
      FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

      // Handle foreground messages
      FirebaseMessaging.onMessage.listen(_handleForegroundMessage);

      // Handle notification taps
      FirebaseMessaging.onMessageOpenedApp.listen(_handleNotificationTap);

      // Handle initial notification
      final initialMessage = await messaging.getInitialMessage();
      if (initialMessage != null) {
        _handleNotificationTap(initialMessage);
      }
    }
  }

  // Show local notification
  static Future<void> showNotification({
    required String title,
    required String body,
    String? payload,
    NotificationType type = NotificationType.general,
  }) async {
    const notificationDetails = NotificationDetails(
      android: AndroidNotificationDetails(
        'bizinvestify_general',
        'General Notifications',
        channelDescription: 'General app notifications',
        importance: Importance.high,
        priority: Priority.high,
        icon: '@mipmap/ic_launcher',
      ),
      iOS: DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
      ),
    );

    await _localNotifications.show(
      DateTime.now().millisecondsSinceEpoch.remainder(100000),
      title,
      body,
      notificationDetails,
      payload: payload,
    );
  }

  // Show chat notification
  static Future<void> showChatNotification({
    required String senderName,
    required String message,
    required int conversationId,
  }) async {
    const notificationDetails = NotificationDetails(
      android: AndroidNotificationDetails(
        'bizinvestify_chat',
        'Chat Messages',
        channelDescription: 'Chat and messaging notifications',
        importance: Importance.high,
        priority: Priority.high,
        icon: '@mipmap/ic_launcher',
        sound: RawResourceAndroidNotificationSound('notification_sound'),
      ),
      iOS: DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
        sound: 'notification_sound.aiff',
      ),
    );

    await _localNotifications.show(
      conversationId,
      senderName,
      message,
      notificationDetails,
      payload: 'chat:$conversationId',
    );
  }

  // Show transaction notification
  static Future<void> showTransactionNotification({
    required String title,
    required String body,
    required String transactionId,
  }) async {
    const notificationDetails = NotificationDetails(
      android: AndroidNotificationDetails(
        'bizinvestify_transactions',
        'Transactions',
        channelDescription: 'Payment and transaction notifications',
        importance: Importance.high,
        priority: Priority.high,
        icon: '@mipmap/ic_launcher',
        sound: RawResourceAndroidNotificationSound('transaction_sound'),
      ),
      iOS: DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
        sound: 'transaction_sound.aiff',
      ),
    );

    await _localNotifications.show(
      DateTime.now().millisecondsSinceEpoch.remainder(100000),
      title,
      body,
      notificationDetails,
      payload: 'transaction:$transactionId',
    );
  }

  // Handle notification tap
  static void _onNotificationTapped(NotificationResponse response) {
    final payload = response.payload;
    if (payload != null) {
      _handleNotificationPayload(payload);
    }
  }

  // Handle notification payload
  static void _handleNotificationPayload(String payload) {
    if (payload.startsWith('chat:')) {
      final String conversationId = payload.split(':')[1];
      // TODO: Navigate to chat screen - not implemented here
      if (conversationId.isNotEmpty) {
        // placeholder side-effect to mark variable as used
        print('chat conversation: $conversationId');
      }
    } else if (payload.startsWith('transaction:')) {
      final transactionId = payload.split(':')[1];
      AppRouter.pushNamed(AppRouter.orderDetails, arguments: {'id': transactionId});
    } else if (payload.startsWith('product:')) {
      final idStr = payload.split(':')[1];
      final id = int.tryParse(idStr);
      if (id != null) {
        AppRouter.pushNamed(AppRouter.productDetails, arguments: {'id': id});
      }
    } else if (payload.startsWith('business:')) {
      // TODO: business details screen route when available
    }
  }

  // Handle foreground message
  static void _handleForegroundMessage(RemoteMessage message) {
    print('Handling foreground message: ${message.messageId}');
    
    final data = message.data;
    final notification = message.notification;

    if (notification != null) {
      switch (data['type']) {
        case 'chat':
          showChatNotification(
            senderName: data['sender_name'] ?? 'Unknown',
            message: notification.body ?? 'New message',
            conversationId: int.tryParse(data['conversation_id'] ?? '0') ?? 0,
          );
          break;
        case 'transaction':
          showTransactionNotification(
            title: notification.title ?? 'Transaction Update',
            body: notification.body ?? 'Your transaction has been updated',
            transactionId: data['transaction_id'] ?? '',
          );
          break;
        default:
          showNotification(
            title: notification.title ?? 'BizInvestify',
            body: notification.body ?? 'You have a new notification',
            payload: data['payload'],
          );
      }
    }
  }

  // Handle notification tap
  static void _handleNotificationTap(RemoteMessage message) {
    final data = message.data;
    final payload = data['payload'] ?? data['type'];
    
    if (payload != null) {
      _handleNotificationPayload(payload);
    }
  }

  // Get FCM token
  static Future<String?> getFCMToken() async {
    return await _storage.read(key: 'fcm_token');
  }

  // Clear all notifications
  static Future<void> clearAllNotifications() async {
    await _localNotifications.cancelAll();
  }

  // Clear specific notification
  static Future<void> clearNotification(int id) async {
    await _localNotifications.cancel(id);
  }

  // Get notification count
  static Future<int> getNotificationCount() async {
    // This would typically be managed by the app's notification state
    return 0;
  }

  // Subscribe to topic
  static Future<void> subscribeToTopic(String topic) async {
    if (kIsWeb) return;
    await FirebaseMessaging.instance.subscribeToTopic(topic);
  }

  // Unsubscribe from topic
  static Future<void> unsubscribeFromTopic(String topic) async {
    if (kIsWeb) return;
    await FirebaseMessaging.instance.unsubscribeFromTopic(topic);
  }
}

// Background message handler
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  print('Handling background message: ${message.messageId}');
  
  // Initialize Firebase if needed
  // await Firebase.initializeApp();
  
  // Handle background message
  final data = message.data;
  print('Background message data: $data');
}

// Notification types
enum NotificationType {
  general,
  chat,
  transaction,
  product,
  business,
  system,
}

// Provider for notification service
final notificationServiceProvider = Provider<NotificationService>((ref) {
  return NotificationService();
});
