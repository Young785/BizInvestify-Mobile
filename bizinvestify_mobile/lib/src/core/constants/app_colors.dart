import 'package:flutter/material.dart';

/// BizInvestify Professional Color System
/// Modern, sophisticated palette for production fintech app
class AppColors {
  // Primary Blue - Modern and Professional
  static const Color primary50 = Color(0xFFEFF6FF);
  static const Color primary100 = Color(0xFFDBEAFE);
  static const Color primary200 = Color(0xFFBFDBFE);
  static const Color primary300 = Color(0xFF93C5FD);
  static const Color primary400 = Color(0xFF60A5FA);
  // Brand Primary Blue (#1A73E8)
  static const Color primary500 = Color(0xFF1A73E8); // Main Brand Blue
  static const Color primary600 = Color(0xFF1558B0);
  static const Color primary700 = Color(0xFF0039A6);
  static const Color primary800 = Color(0xFF1E3A8A);
  static const Color primary900 = Color(0xFF1E293B);

  // Success Green - Modern and Vibrant
  static const Color success50 = Color(0xFFF0FDF4);
  static const Color success100 = Color(0xFFDCFCE7);
  static const Color success200 = Color(0xFFBBF7D0);
  static const Color success300 = Color(0xFF86EFAC);
  static const Color success400 = Color(0xFF4ADE80);
  // Brand Accent Green (#00C48C)
  static const Color success500 = Color(0xFF00C48C); // Main Accent/Success
  static const Color success600 = Color(0xFF00A97A);
  static const Color success700 = Color(0xFF15803D);
  static const Color success800 = Color(0xFF166534);
  static const Color success900 = Color(0xFF14532D);

  // Premium Purple - Accent Color
  static const Color purple50 = Color(0xFFFAF5FF);
  static const Color purple100 = Color(0xFFF3E8FF);
  static const Color purple200 = Color(0xFFE9D5FF);
  static const Color purple300 = Color(0xFFD8B4FE);
  static const Color purple400 = Color(0xFFC084FC);
  static const Color purple500 = Color(0xFF8B5CF6);
  static const Color purple600 = Color(0xFF7C3AED);
  static const Color purple700 = Color(0xFF6D28D9);
  static const Color purple800 = Color(0xFF5B21B6);
  static const Color purple900 = Color(0xFF4C1D95);

  // Neutral Gray Scale - Modern and Clean
  static const Color neutral50 = Color(0xFFFAFAFA);
  static const Color neutral100 = Color(0xFFF5F5F5);
  static const Color neutral200 = Color(0xFFE5E5E5);
  static const Color neutral300 = Color(0xFFD4D4D4);
  static const Color neutral400 = Color(0xFFA3A3A3);
  static const Color neutral500 = Color(0xFF737373);
  static const Color neutral600 = Color(0xFF525252);
  static const Color neutral700 = Color(0xFF404040);
  static const Color neutral800 = Color(0xFF262626);
  static const Color neutral900 = Color(0xFF171717);

  // Semantic Colors
  static const Color white = Color(0xFFFFFFFF);
  static const Color black = Color(0xFF000000);
  
  // Status Colors
  static const Color error = Color(0xFFEF4444);
  static const Color warning = Color(0xFFF59E0B);
  static const Color info = Color(0xFF3B82F6);

  // Background Colors
  static const Color backgroundPrimary = neutral50;
  static const Color backgroundSecondary = white;
  static const Color backgroundTertiary = neutral100;

  // Backward-compat background aliases (for existing widgets)
  static const Color background50 = neutral50;
  static const Color background100 = neutral100;
  static const Color background200 = neutral200;
  static const Color background300 = neutral300;

  // Text Colors
  static const Color textPrimary = neutral900;
  static const Color textSecondary = neutral700;
  static const Color textTertiary = neutral500;
  static const Color textMuted = neutral400;

  // Backward-compat text aliases (for existing widgets)
  static const Color text300 = neutral300;
  static const Color text400 = neutral400;
  static const Color text500 = neutral500;
  static const Color text600 = neutral600;
  static const Color text700 = neutral700;
  static const Color text800 = neutral800;

  // Border Colors
  static const Color borderPrimary = neutral200;
  static const Color borderSecondary = neutral300;
  static const Color borderFocus = primary500;

  // Backward-compat accent aliases (map to success palette by default)
  static const Color accent50 = success50;
  static const Color accent100 = success100;
  static const Color accent200 = success200;
  static const Color accent300 = success300;
  static const Color accent400 = success400;
  static const Color accent500 = success500;
  static const Color accent600 = success600;
  static const Color accent700 = success700;
  static const Color accent800 = success800;
  static const Color accent900 = success900;

  // Modern Gradients
  static const LinearGradient primaryGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [primary500, primary600],
    stops: [0.0, 1.0],
  );

  static const LinearGradient successGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [success500, success600],
    stops: [0.0, 1.0],
  );

  static const LinearGradient purpleGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [purple500, purple600],
    stops: [0.0, 1.0],
  );

  static const LinearGradient shimmerGradient = LinearGradient(
    begin: Alignment(-1.0, -1.0),
    end: Alignment(1.0, 1.0),
    colors: [neutral100, neutral200, neutral100],
    stops: [0.0, 0.5, 1.0],
  );

  // Modern Shadow Colors
  static Color shadowPrimary = primary500.withOpacity(0.15);
  static Color shadowSuccess = success500.withOpacity(0.15);
  static Color shadowPurple = purple500.withOpacity(0.15);
  static Color shadowNeutral = neutral900.withOpacity(0.08);
  static Color shadowSoft = neutral900.withOpacity(0.04);
  static Color shadowMedium = neutral900.withOpacity(0.08);
  static Color shadowLarge = neutral900.withOpacity(0.12);

  // Glass Effect Colors
  static Color glassPrimary = white.withOpacity(0.8);
  static Color glassSecondary = white.withOpacity(0.6);
  static Color glassTertiary = white.withOpacity(0.4);

  // Overlay Colors
  static Color overlayLight = black.withOpacity(0.4);
  static Color overlayMedium = black.withOpacity(0.6);
  static Color overlayDark = black.withOpacity(0.8);

  // Chart Colors
  static const List<Color> chartColors = [
    primary500,
    success500,
    purple500,
    warning,
    error,
    info,
  ];

  // Investment Status Colors
  static const Color investmentProfit = success500;
  static const Color investmentLoss = error;
  static const Color investmentNeutral = neutral500;
  static const Color investmentPending = warning;

  // KYC Status Colors
  static const Color kycApproved = success500;
  static const Color kycPending = warning;
  static const Color kycRejected = error;
  static const Color kycNotStarted = neutral400;
}
