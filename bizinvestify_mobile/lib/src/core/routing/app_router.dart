import 'package:flutter/material.dart';
import '../../features/splash/screens/splash_screen.dart';
import '../../features/onboarding/screens/onboarding_screen.dart';
import '../../features/auth/screens/login_screen.dart';
import '../../features/auth/screens/register_screen.dart';
import '../../features/dashboard/screens/dashboard_screen.dart';
import '../../features/marketplace/screens/product_details_screen.dart';
import '../../features/orders/screens/order_details_screen.dart';

class AppRouter {
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

  static const String splash = '/';
  static const String onboarding = '/onboarding';
  static const String login = '/login';
  static const String register = '/register';
  static const String dashboard = '/dashboard';
  static const String productDetails = '/product';
  static const String orderDetails = '/order';

  static Route<dynamic> generateRoute(RouteSettings settings) {
    switch (settings.name) {
      case splash:
        return MaterialPageRoute(builder: (_) => const SplashScreen());
      case onboarding:
        return MaterialPageRoute(builder: (_) => const OnboardingScreen());
      case login:
        return MaterialPageRoute(builder: (_) => const LoginScreen());
      case register:
        return MaterialPageRoute(builder: (_) => const RegisterScreen());
      case dashboard:
        return MaterialPageRoute(builder: (_) => const DashboardScreen());
      case productDetails:
        final args = settings.arguments as Map<String, dynamic>?;
        final id = args?['id'] as int?;
        if (id == null) {
          return _error('Missing product id');
        }
        return MaterialPageRoute(builder: (_) => ProductDetailsScreen(productId: id));
      case orderDetails:
        final args = settings.arguments as Map<String, dynamic>?;
        final id = args?['id']?.toString();
        if (id == null || id.isEmpty) return _error('Missing order id');
        return MaterialPageRoute(builder: (_) => OrderDetailsScreen(transactionId: id));
      default:
        return _error('No route defined for ${settings.name}');
    }
  }

  static MaterialPageRoute _error(String message) => MaterialPageRoute(
        builder: (_) => Scaffold(body: Center(child: Text(message))),
      );

  static Future<dynamic>? pushNamed(String route, {Object? arguments}) =>
      navigatorKey.currentState?.pushNamed(route, arguments: arguments);

  static Future<dynamic>? pushReplacementNamed(String route, {Object? arguments}) =>
      navigatorKey.currentState?.pushReplacementNamed(route, arguments: arguments);
}
