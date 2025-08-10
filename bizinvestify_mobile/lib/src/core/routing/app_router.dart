import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../features/splash/screens/splash_screen.dart';
import '../../features/onboarding/screens/onboarding_screen.dart';
import '../../features/auth/screens/login_screen.dart';
import '../../features/auth/screens/register_screen.dart';
import '../../features/dashboard/screens/dashboard_screen.dart';

class AppRouter {
  static const String splash = '/';
  static const String onboarding = '/onboarding';
  static const String login = '/login';
  static const String register = '/register';
  static const String dashboard = '/dashboard';

  static Route<dynamic> generateRoute(RouteSettings settings) {
    switch (settings.name) {
      case splash:
        return MaterialPageRoute(
          builder: (_) => const SplashScreen(),
        );
      
      case onboarding:
        return MaterialPageRoute(
          builder: (_) => const OnboardingScreen(),
        );
      
      case login:
        return MaterialPageRoute(
          builder: (_) => const LoginScreen(),
        );
      
      case register:
        return MaterialPageRoute(
          builder: (_) => const RegisterScreen(),
        );
      
      case dashboard:
        return MaterialPageRoute(
          builder: (_) => const DashboardScreen(),
        );
      
      default:
        return MaterialPageRoute(
          builder: (_) => Scaffold(
            body: Center(
              child: Text('No route defined for ${settings.name}'),
            ),
          ),
        );
    }
  }

  static void navigateToSplash(BuildContext context) {
    Navigator.of(context).pushNamedAndRemoveUntil(
      splash,
      (route) => false,
    );
  }

  static void navigateToOnboarding(BuildContext context) {
    Navigator.of(context).pushReplacementNamed(onboarding);
  }

  static void navigateToLogin(BuildContext context) {
    Navigator.of(context).pushReplacementNamed(login);
  }

  static void navigateToRegister(BuildContext context) {
    Navigator.of(context).pushNamed(register);
  }

  static void navigateToDashboard(BuildContext context) {
    Navigator.of(context).pushReplacementNamed(dashboard);
  }

  static void navigateBack(BuildContext context) {
    Navigator.of(context).pop();
  }
}
