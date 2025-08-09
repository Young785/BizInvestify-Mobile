import 'dart:developer';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:firebase_analytics/firebase_analytics.dart';
import 'package:firebase_crashlytics/firebase_crashlytics.dart';
import 'package:firebase_performance/firebase_performance.dart';
import 'package:flutter/foundation.dart';
import '../utils/toast.dart';
import '../utils/feedback.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Firebase service for analytics, messaging, and performance monitoring
class FirebaseService {
  static final FirebaseService _instance = FirebaseService._internal();
  factory FirebaseService() => _instance;
  FirebaseService._internal();

  late FirebaseMessaging _messaging;
  late FirebaseAnalytics _analytics;
  late FirebaseCrashlytics _crashlytics;
  late FirebasePerformance _performance;

  bool _initialized = false;

  /// Initialize Firebase services
  Future<void> initialize() async {
    if (_initialized) return;

    try {
      // Initialize Firebase
      await Firebase.initializeApp();

      // Initialize services
      _messaging = FirebaseMessaging.instance;
      _analytics = FirebaseAnalytics.instance;
      _crashlytics = FirebaseCrashlytics.instance;
      _performance = FirebasePerformance.instance;

      // Set up crash reporting
      if (!kDebugMode) {
        FlutterError.onError = _crashlytics.recordFlutterFatalError;
        PlatformDispatcher.instance.onError = (error, stack) {
          _crashlytics.recordError(error, stack, fatal: true);
          return true;
        };
      }

      // Request notification permissions
      await _requestNotificationPermissions();

      // Set up message handlers
      await _setupMessageHandlers();

      _initialized = true;
      log('Firebase services initialized successfully');
    } catch (e, stackTrace) {
      log('Failed to initialize Firebase services: $e');
      if (!kDebugMode) {
        await _crashlytics.recordError(e, stackTrace);
      }
    }
  }

  /// Request notification permissions
  Future<void> _requestNotificationPermissions() async {
    final settings = await _messaging.requestPermission(
      alert: true,
      announcement: false,
      badge: true,
      carPlay: false,
      criticalAlert: false,
      provisional: false,
      sound: true,
    );

    log('Notification permission status: ${settings.authorizationStatus}');
  }

  /// Set up message handlers
  Future<void> _setupMessageHandlers() async {
    // Handle foreground messages
    FirebaseMessaging.onMessage.listen(_handleForegroundMessage);

    // Handle background messages
    FirebaseMessaging.onBackgroundMessage(_handleBackgroundMessage);

    // Handle message taps when app is terminated
    FirebaseMessaging.onMessageOpenedApp.listen(_handleMessageTap);

    // Check for initial message when app is launched from terminated state
    final initialMessage = await _messaging.getInitialMessage();
    if (initialMessage != null) {
      _handleMessageTap(initialMessage);
    }
  }

  /// Handle foreground messages
  void _handleForegroundMessage(RemoteMessage message) {
    log('Received foreground message: ${message.messageId}');
    
    // Track analytics event
    _analytics.logEvent(
      name: 'notification_received_foreground',
      parameters: <String, Object>{
        'message_id': message.messageId ?? '',
        'notification_title': message.notification?.title ?? '',
        'notification_body': message.notification?.body ?? '',
      },
    );

    // Show in-app notification or update UI
    _showInAppNotification(message);
  }

  /// Handle message taps
  void _handleMessageTap(RemoteMessage message) {
    log('User tapped notification: ${message.messageId}');
    
    // Track analytics event
    _analytics.logEvent(
      name: 'notification_tapped',
      parameters: <String, Object>{
        'message_id': message.messageId ?? '',
        'notification_title': message.notification?.title ?? '',
        'data': message.data.toString(),
      },
    );

    // Navigate to appropriate screen based on message data
    _handleNotificationNavigation(message);
  }

  /// Show in-app notification
  void _showInAppNotification(RemoteMessage message) {
    final title = message.notification?.title ?? 'Notification';
    final body = message.notification?.body ?? '';
    AppToast.info(body.isEmpty ? title : body, title: body.isEmpty ? null : title);
    Haptics.light();
  }

  /// Handle notification navigation
  void _handleNotificationNavigation(RemoteMessage message) {
    final data = message.data;
    
    switch (data['type']) {
      case 'investment_update':
        // Navigate to investment details
        _navigateToInvestment(data['investment_id']);
        break;
      case 'new_business':
        // Navigate to business details
        _navigateToBusiness(data['business_id']);
        break;
      case 'portfolio_update':
        // Navigate to portfolio
        _navigateToPortfolio();
        break;
      case 'market_update':
        // Navigate to marketplace
        _navigateToMarketplace();
        break;
      default:
        // Navigate to home
        _navigateToHome();
    }
  }

  /// Navigation methods
  void _navigateToInvestment(String? investmentId) {
    // TODO: Implement navigation to investment details
    log('Navigate to investment: $investmentId');
  }

  void _navigateToBusiness(String? businessId) {
    // TODO: Implement navigation to business details
    log('Navigate to business: $businessId');
  }

  void _navigateToPortfolio() {
    // TODO: Implement navigation to portfolio
    log('Navigate to portfolio');
  }

  void _navigateToMarketplace() {
    // TODO: Implement navigation to marketplace
    log('Navigate to marketplace');
  }

  void _navigateToHome() {
    // TODO: Implement navigation to home
    log('Navigate to home');
  }

  /// Get FCM token
  Future<String?> getToken() async {
    try {
      return await _messaging.getToken();
    } catch (e) {
      log('Failed to get FCM token: $e');
      return null;
    }
  }

  /// Subscribe to topic
  Future<void> subscribeToTopic(String topic) async {
    try {
      await _messaging.subscribeToTopic(topic);
      log('Subscribed to topic: $topic');
    } catch (e) {
      log('Failed to subscribe to topic $topic: $e');
    }
  }

  /// Unsubscribe from topic
  Future<void> unsubscribeFromTopic(String topic) async {
    try {
      await _messaging.unsubscribeFromTopic(topic);
      log('Unsubscribed from topic: $topic');
    } catch (e) {
      log('Failed to unsubscribe from topic $topic: $e');
    }
  }

  /// Analytics methods
  Future<void> logEvent({
    required String name,
    Map<String, Object>? parameters,
  }) async {
    try {
      await _analytics.logEvent(
        name: name,
        parameters: parameters,
      );
    } catch (e) {
      log('Failed to log analytics event $name: $e');
    }
  }

  Future<void> setUserId(String userId) async {
    try {
      await _analytics.setUserId(id: userId);
    } catch (e) {
      log('Failed to set user ID: $e');
    }
  }

  Future<void> setUserProperty({
    required String name,
    required String value,
  }) async {
    try {
      await _analytics.setUserProperty(name: name, value: value);
    } catch (e) {
      log('Failed to set user property $name: $e');
    }
  }

  /// Performance monitoring
  Trace startTrace(String name) {
    return _performance.newTrace(name);
  }

  /// Crashlytics methods
  Future<void> recordError(
    dynamic exception,
    StackTrace? stackTrace, {
    bool fatal = false,
  }) async {
    if (!kDebugMode) {
      try {
        await _crashlytics.recordError(
          exception,
          stackTrace,
          fatal: fatal,
        );
      } catch (e) {
        log('Failed to record error: $e');
      }
    }
  }

  Future<void> setCustomKey(String key, dynamic value) async {
    if (!kDebugMode) {
      try {
        await _crashlytics.setCustomKey(key, value);
      } catch (e) {
        log('Failed to set custom key $key: $e');
      }
    }
  }

  Future<void> setUserIdentifier(String identifier) async {
    if (!kDebugMode) {
      try {
        await _crashlytics.setUserIdentifier(identifier);
      } catch (e) {
        log('Failed to set user identifier: $e');
      }
    }
  }

  /// Business-specific analytics events
  Future<void> trackBusinessViewed(String businessId) async {
    await logEvent(
      name: 'business_viewed',
      parameters: <String, Object>{
        'business_id': businessId,
        'timestamp': DateTime.now().millisecondsSinceEpoch,
      },
    );
  }

  Future<void> trackInvestmentCreated({
    required String businessId,
    required double amount,
    required double equityPercentage,
  }) async {
    await logEvent(
      name: 'investment_created',
      parameters: <String, Object>{
        'business_id': businessId,
        'investment_amount': amount,
        'equity_percentage': equityPercentage,
        'timestamp': DateTime.now().millisecondsSinceEpoch,
      },
    );
  }

  Future<void> trackSearchPerformed(String query) async {
    await logEvent(
      name: 'search_performed',
      parameters: <String, Object>{
        'search_query': query,
        'timestamp': DateTime.now().millisecondsSinceEpoch,
      },
    );
  }

  Future<void> trackFilterApplied(Map<String, dynamic> filters) async {
    await logEvent(
      name: 'filter_applied',
      parameters: <String, Object>{
        'filters': filters.toString(),
        'timestamp': DateTime.now().millisecondsSinceEpoch,
      },
    );
  }

  Future<void> trackUserRegistration(String method) async {
    await logEvent(
      name: 'user_registration',
      parameters: <String, Object>{
        'method': method,
        'timestamp': DateTime.now().millisecondsSinceEpoch,
      },
    );
  }

  Future<void> trackUserLogin(String method) async {
    await logEvent(
      name: 'user_login',
      parameters: <String, Object>{
        'method': method,
        'timestamp': DateTime.now().millisecondsSinceEpoch,
      },
    );
  }

  Future<void> trackRevenue({
    required double amount,
    required String source,
    String? transactionId,
  }) async {
    await logEvent(
      name: 'revenue_generated',
      parameters: <String, Object>{
        'amount': amount,
        'source': source,
        'transaction_id': transactionId ?? '',
        'timestamp': DateTime.now().millisecondsSinceEpoch,
      },
    );
  }
}

/// Background message handler (must be top-level function)
@pragma('vm:entry-point')
Future<void> _handleBackgroundMessage(RemoteMessage message) async {
  await Firebase.initializeApp();
  
  log('Received background message: ${message.messageId}');
  
  // Track analytics event
  FirebaseAnalytics.instance.logEvent(
    name: 'notification_received_background',
  parameters: <String, Object>{
      'message_id': message.messageId ?? '',
      'notification_title': message.notification?.title ?? '',
      'notification_body': message.notification?.body ?? '',
    },
  );
}

/// Riverpod provider for FirebaseService
final firebaseServiceProvider = Provider<FirebaseService>((ref) {
  return FirebaseService();
});
