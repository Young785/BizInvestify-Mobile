import 'package:flutter/material.dart';
import 'app_colors.dart';

/// BizInvestify Typography System
/// Based on the web platform design system with Inter font family
class AppTypography {
  // Font Family
  static const String fontFamily = 'Inter';

  // Font Weights
  static const FontWeight regular = FontWeight.w400;
  static const FontWeight medium = FontWeight.w500;
  static const FontWeight semibold = FontWeight.w600;
  static const FontWeight bold = FontWeight.w700;

  // Display Text Styles
  static const TextStyle displayLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 64,
    fontWeight: bold,
    height: 1.1,
    color: AppColors.text800,
  );

  static const TextStyle displayMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 48,
    fontWeight: bold,
    height: 1.2,
    color: AppColors.text800,
  );

  static const TextStyle displaySmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 40,
    fontWeight: bold,
    height: 1.2,
    color: AppColors.text800,
  );

  // Headline Text Styles
  static const TextStyle headlineLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 32,
    fontWeight: bold,
    height: 1.3,
    color: AppColors.text800,
  );

  static const TextStyle headlineMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 28,
    fontWeight: semibold,
    height: 1.3,
    color: AppColors.text800,
  );

  static const TextStyle headlineSmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 24,
    fontWeight: semibold,
    height: 1.4,
    color: AppColors.text800,
  );

  // Title Text Styles
  static const TextStyle titleLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 22,
    fontWeight: semibold,
    height: 1.4,
    color: AppColors.text800,
  );

  static const TextStyle titleMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 20,
    fontWeight: medium,
    height: 1.4,
    color: AppColors.text800,
  );

  static const TextStyle titleSmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 18,
    fontWeight: medium,
    height: 1.4,
    color: AppColors.text800,
  );

  // Body Text Styles
  static const TextStyle bodyLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 18,
    fontWeight: regular,
    height: 1.5,
    color: AppColors.text700,
  );

  static const TextStyle bodyMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 16,
    fontWeight: regular,
    height: 1.5,
    color: AppColors.text700,
  );

  static const TextStyle bodySmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 14,
    fontWeight: regular,
    height: 1.5,
    color: AppColors.text600,
  );

  // Label Text Styles
  static const TextStyle labelLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 16,
    fontWeight: medium,
    height: 1.4,
    color: AppColors.text700,
  );

  static const TextStyle labelMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 14,
    fontWeight: medium,
    height: 1.4,
    color: AppColors.text700,
  );

  static const TextStyle labelSmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 12,
    fontWeight: medium,
    height: 1.4,
    color: AppColors.text600,
  );

  // Caption Text Styles
  static const TextStyle captionLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 14,
    fontWeight: regular,
    height: 1.4,
    color: AppColors.text500,
  );

  static const TextStyle captionMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 12,
    fontWeight: regular,
    height: 1.4,
    color: AppColors.text500,
  );

  static const TextStyle captionSmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 10,
    fontWeight: regular,
    height: 1.4,
    color: AppColors.text400,
  );

  // Button Text Styles
  static const TextStyle buttonLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 18,
    fontWeight: semibold,
    height: 1.2,
    color: Colors.white,
  );

  static const TextStyle buttonMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 16,
    fontWeight: semibold,
    height: 1.2,
    color: Colors.white,
  );

  static const TextStyle buttonSmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 14,
    fontWeight: semibold,
    height: 1.2,
    color: Colors.white,
  );

  // Gradient Text Styles
  static TextStyle gradientText(TextStyle baseStyle) {
    return baseStyle.copyWith(
      foreground: Paint()
        ..shader = const LinearGradient(
          colors: [AppColors.primary500, AppColors.accent500],
        ).createShader(const Rect.fromLTWH(0, 0, 200, 70)),
    );
  }

  // Complete Text Theme
  static TextTheme get textTheme {
    return const TextTheme(
      displayLarge: displayLarge,
      displayMedium: displayMedium,
      displaySmall: displaySmall,
      headlineLarge: headlineLarge,
      headlineMedium: headlineMedium,
      headlineSmall: headlineSmall,
      titleLarge: titleLarge,
      titleMedium: titleMedium,
      titleSmall: titleSmall,
      bodyLarge: bodyLarge,
      bodyMedium: bodyMedium,
      bodySmall: bodySmall,
      labelLarge: labelLarge,
      labelMedium: labelMedium,
      labelSmall: labelSmall,
    );
  }
  
  // === UTILITY METHODS ===
  
  // Create branded text style
  static TextStyle branded(TextStyle baseStyle) {
    return baseStyle.copyWith(
      color: AppColors.primary500,
      fontWeight: semibold,
    );
  }
  
  // Create muted text style
  static TextStyle muted(TextStyle baseStyle) {
    return baseStyle.copyWith(
      color: AppColors.textMuted,
    );
  }
  
  // Create emphasized text style
  static TextStyle emphasized(TextStyle baseStyle) {
    return baseStyle.copyWith(
      fontWeight: semibold,
      color: AppColors.textPrimary,
    );
  }
  
  // Create success text style
  static TextStyle success(TextStyle baseStyle) {
    return baseStyle.copyWith(
      color: AppColors.success500,
    );
  }
  
  // Create error text style
  static TextStyle error(TextStyle baseStyle) {
    return baseStyle.copyWith(
      color: AppColors.error500,
    );
  }
  
  // Create warning text style
  static TextStyle warning(TextStyle baseStyle) {
    return baseStyle.copyWith(
      color: AppColors.warning500,
    );
  }
}
