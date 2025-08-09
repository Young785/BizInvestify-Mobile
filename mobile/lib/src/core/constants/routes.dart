/// App route constants
class AppRoutes {
  AppRoutes._();

  // Onboarding & Auth
  static const String onboarding = '/onboarding';
  static const String login = '/login';
  static const String register = '/register';
  static const String forgotPassword = '/forgot-password';
  static const String changePassword = '/change-password';
  static const String verifyEmail = '/verify-email';
  static const String verifyPhone = '/verify-phone';
  static const String setup2FA = '/setup-2fa';

  // Main App
  static const String home = '/home';
  static const String todayTasks = '/tasks/today';
  static const String profile = '/profile';
  static const String notifications = '/notifications';
  static const String settings = '/settings';

  // Tasks
  static const String createTask = '/tasks/create';
  static const String editTask = '/tasks/edit';
  static const String taskDetail = '/tasks/detail';

  // Business & Marketplace
  static const String marketplace = '/marketplace';
  static const String businessDetail = '/business/detail';
  static const String productDetail = '/product/detail';
  static const String createBusiness = '/business/create';
  static const String createProduct = '/product/create';

  // Payments
  static const String payments = '/payments';
  static const String paymentMethods = '/payment-methods';
  static const String transactions = '/transactions';

  // Support
  static const String support = '/support';
  static const String help = '/help';
  static const String about = '/about';
}
