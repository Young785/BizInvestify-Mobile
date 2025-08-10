import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'src/core/theme/app_theme.dart';
import 'src/core/routing/app_router.dart';
import 'features/payments/stripe_service.dart';

const String kStripePublishableKey = String.fromEnvironment('STRIPE_PUBLISHABLE_KEY', defaultValue: '');

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  if (kStripePublishableKey.isNotEmpty) {
    await stripeService.initialize(publishableKey: kStripePublishableKey);
  }

  runApp(
    const ProviderScope(
      child: BizInvestifyApp(),
    ),
  );
}

class BizInvestifyApp extends StatelessWidget {
  const BizInvestifyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ScreenUtilInit(
      designSize: const Size(375, 812), // iPhone X design size
      minTextAdapt: true,
      splitScreenMode: true,
      builder: (context, child) {
        return MaterialApp(
          title: 'BizInvestify',
          debugShowCheckedModeBanner: false,
          theme: AppTheme.lightTheme,
          darkTheme: AppTheme.darkTheme,
          themeMode: ThemeMode.light,
          initialRoute: AppRouter.splash,
          onGenerateRoute: AppRouter.generateRoute,
        );
      },
    );
  }
}
