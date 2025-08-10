import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'src/core/theme/app_theme.dart';
import 'src/core/routing/app_router.dart';
import 'src/features/payments/stripe_service.dart';
import 'src/core/services/notification_service.dart';
import 'src/core/settings/settings_controller.dart';

const String kStripePublishableKey = String.fromEnvironment('STRIPE_PUBLISHABLE_KEY', defaultValue: '');

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  if (kStripePublishableKey.isNotEmpty) {
    await stripeService.initialize(publishableKey: kStripePublishableKey);
  }
  await NotificationService.initialize();

  runApp(
    const ProviderScope(
      child: BizInvestifyApp(),
    ),
  );
}

class BizInvestifyApp extends ConsumerWidget {
  const BizInvestifyApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final settings = ref.watch(settingsProvider);
    return ScreenUtilInit(
      designSize: const Size(375, 812),
      minTextAdapt: true,
      splitScreenMode: true,
      builder: (context, child) {
        return MaterialApp(
          title: 'BizInvestify',
          debugShowCheckedModeBanner: false,
          theme: AppTheme.lightTheme,
          darkTheme: AppTheme.darkTheme,
          themeMode: settings.themeMode,
          locale: settings.locale,
          initialRoute: AppRouter.splash,
          onGenerateRoute: AppRouter.generateRoute,
        );
      },
    );
  }
}
