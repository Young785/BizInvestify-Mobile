import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../constants/routes.dart';
import '../../features/auth/providers/auth_provider.dart';
import '../../features/auth/screens/login_screen.dart';
import '../../features/auth/screens/register_screen.dart';
import '../../features/auth/screens/verify_email_screen.dart';
import '../../features/auth/screens/verify_phone_screen.dart';
import '../../features/auth/screens/setup_2fa_screen.dart';
import '../../features/auth/screens/forgot_password_screen.dart';
import '../../features/auth/screens/change_password_screen.dart';
import '../../features/home/screens/home_screen.dart';
import '../../features/auth/screens/onboarding_screen.dart';
import '../../features/marketplace/screens/marketplace_screen.dart';
import '../../features/marketplace/screens/business_detail_screen.dart';
import '../../features/marketplace/screens/investment_screen.dart';
import '../../features/marketplace/screens/portfolio_screen.dart';

/// Global navigator key for showing overlays/toasts without BuildContext
final GlobalKey<NavigatorState> rootNavigatorKey = GlobalKey<NavigatorState>();

/// App router configuration with authentication guards
final appRouterProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authProvider);
  
  return GoRouter(
    navigatorKey: rootNavigatorKey,
    initialLocation: AppRoutes.onboarding,
    debugLogDiagnostics: true,
    redirect: (context, state) {
      final isAuthenticated = authState.isAuthenticated;
      final isOnboarding = state.matchedLocation == AppRoutes.onboarding;
      final isAuthRoute = _isAuthRoute(state.matchedLocation);
      
      // If not authenticated and not on auth/onboarding routes, redirect to onboarding
      if (!isAuthenticated && !isAuthRoute && !isOnboarding) {
        return AppRoutes.onboarding;
      }
      
      // If authenticated and on auth/onboarding routes, redirect to home
      if (isAuthenticated && (isAuthRoute || isOnboarding)) {
        return AppRoutes.home;
      }
      
      return null; // No redirect needed
    },
    routes: [
      // Onboarding
      GoRoute(
        path: AppRoutes.onboarding,
        builder: (context, state) => const OnboardingScreen(),
      ),
      
      // Authentication routes
      GoRoute(
        path: AppRoutes.login,
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: AppRoutes.register,
        builder: (context, state) => const RegisterScreen(),
      ),
      GoRoute(
        path: AppRoutes.forgotPassword,
        builder: (context, state) => const ForgotPasswordScreen(),
      ),
      GoRoute(
        path: AppRoutes.changePassword,
        builder: (context, state) => const ChangePasswordScreen(),
      ),
      
      // Verification routes
      GoRoute(
        path: AppRoutes.verifyEmail,
        builder: (context, state) {
          final email = state.uri.queryParameters['email'];
          final isWelcome = state.uri.queryParameters['welcome'] == 'true';
          return VerifyEmailScreen(
            email: email,
            isWelcome: isWelcome,
          );
        },
      ),
      GoRoute(
        path: AppRoutes.verifyPhone,
        builder: (context, state) {
          final email = state.uri.queryParameters['email'];
          final phone = state.uri.queryParameters['phone'];
          return VerifyPhoneScreen(
            email: email,
            phone: phone,
          );
        },
      ),
      GoRoute(
        path: AppRoutes.setup2FA,
        builder: (context, state) => const Setup2FAScreen(),
      ),
      
      // Main app routes
      GoRoute(
        path: AppRoutes.home,
        builder: (context, state) => const HomeScreen(),
      ),
      
      // Marketplace routes
      GoRoute(
        path: '/marketplace',
        builder: (context, state) => const MarketplaceScreen(),
      ),
      GoRoute(
        path: '/business/:businessId',
        builder: (context, state) {
          final businessId = state.pathParameters['businessId']!;
          return BusinessDetailScreen(businessId: businessId);
        },
      ),
      GoRoute(
        path: '/investments',
        builder: (context, state) => const InvestmentScreen(),
      ),
      GoRoute(
        path: '/portfolio',
        builder: (context, state) => const PortfolioScreen(),
      ),
    ],
    
    // Error handling
    errorBuilder: (context, state) => Scaffold(
      appBar: AppBar(title: const Text('Error')),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 64, color: Colors.red),
            const SizedBox(height: 16),
            Text(
              'Page not found: ${state.matchedLocation}',
              style: const TextStyle(fontSize: 18),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () => context.go(AppRoutes.home),
              child: const Text('Go Home'),
            ),
          ],
        ),
      ),
    ),
  );
});

/// Check if the current route is an authentication route
bool _isAuthRoute(String location) {
  const authRoutes = [
    AppRoutes.login,
    AppRoutes.register,
    AppRoutes.forgotPassword,
    AppRoutes.changePassword,
    AppRoutes.verifyEmail,
    AppRoutes.verifyPhone,
    AppRoutes.setup2FA,
  ];
  
  return authRoutes.any((route) => location.startsWith(route));
}

/// Navigation extensions for easier routing
extension AppRouterExtension on GoRouter {
  /// Navigate to login screen
  void goToLogin() => go(AppRoutes.login);
  
  /// Navigate to register screen
  void goToRegister() => go(AppRoutes.register);
  
  /// Navigate to home screen
  void goToHome() => go(AppRoutes.home);
  
  /// Navigate to email verification with parameters
  void goToEmailVerification({String? email, bool isWelcome = false}) {
    final params = <String, String>{};
    if (email != null) params['email'] = email;
    if (isWelcome) params['welcome'] = 'true';
    
    final uri = Uri(path: AppRoutes.verifyEmail, queryParameters: params);
    go(uri.toString());
  }
  
  /// Navigate to phone verification with parameters
  void goToPhoneVerification({String? email, String? phone}) {
    final params = <String, String>{};
    if (email != null) params['email'] = email;
    if (phone != null) params['phone'] = phone;
    
    final uri = Uri(path: AppRoutes.verifyPhone, queryParameters: params);
    go(uri.toString());
  }
}

/// Auth guard widget for protecting routes
class AuthGuard extends ConsumerWidget {
  final Widget child;
  
  const AuthGuard({
    super.key,
    required this.child,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authProvider);
    
    if (authState.isLoading) {
      return const Scaffold(
        body: Center(
          child: CircularProgressIndicator(),
        ),
      );
    }
    
    if (!authState.isAuthenticated) {
      // Redirect to login if not authenticated
      WidgetsBinding.instance.addPostFrameCallback((_) {
        context.go(AppRoutes.login);
      });
      return const SizedBox.shrink();
    }
    
    return child;
  }
}

/// Verification guard widget for checking verification status
class VerificationGuard extends ConsumerWidget {
  final Widget child;
  
  const VerificationGuard({
    super.key,
    required this.child,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authProvider);
    final verificationStatus = authState.verificationStatus;
    
    if (authState.isLoading) {
      return const Scaffold(
        body: Center(
          child: CircularProgressIndicator(),
        ),
      );
    }
    
    if (verificationStatus != null) {
      // Check verification requirements and redirect if needed
      if (!verificationStatus.emailVerified) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          context.go(AppRoutes.verifyEmail);
        });
        return const SizedBox.shrink();
      }
      
      if (!verificationStatus.phoneVerified) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          context.go(AppRoutes.verifyPhone);
        });
        return const SizedBox.shrink();
      }
      
      if (!verificationStatus.twoFactorEnabled) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          context.go(AppRoutes.setup2FA);
        });
        return const SizedBox.shrink();
      }
    }
    
    return child;
  }
}
