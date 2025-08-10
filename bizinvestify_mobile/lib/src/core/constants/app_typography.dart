import 'package:flutter/material.dart';
import 'app_colors.dart';
import 'package:google_fonts/google_fonts.dart';

/// BizInvestify Professional Typography System
/// Modern font hierarchy for production fintech app
class AppTypography {
  // Font Family - Inter as primary, platform fallbacks
  static String getFontFamily() {
    // Use GoogleFonts Inter
    return GoogleFonts.inter().fontFamily ?? 'Inter';
  }

  static String get fontFamily => getFontFamily();
  static const String fontFamilyFallback = 'SF Pro Display'; // iOS fallback
  static const String fontFamilyAndroid = 'Roboto'; // Android fallback

  // Font Weights
  static const FontWeight light = FontWeight.w300;
  static const FontWeight regular = FontWeight.w400;
  static const FontWeight medium = FontWeight.w500;
  static const FontWeight semibold = FontWeight.w600;
  static const FontWeight bold = FontWeight.w700;
  static const FontWeight extrabold = FontWeight.w800;

  // Display Text Styles - Hero text
  static const TextStyle displayLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 64,
    fontWeight: extrabold,
    height: 1.1,
    letterSpacing: -2.0,
    color: AppColors.textPrimary,
  );

  static const TextStyle displayMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 48,
    fontWeight: extrabold,
    height: 1.15,
    letterSpacing: -1.5,
    color: AppColors.textPrimary,
  );

  static const TextStyle displaySmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 40,
    fontWeight: bold,
    height: 1.2,
    letterSpacing: -1.0,
    color: AppColors.textPrimary,
  );

  // Headline Text Styles - Section headers
  static const TextStyle headlineLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 32,
    fontWeight: bold,
    height: 1.25,
    letterSpacing: -0.8,
    color: AppColors.textPrimary,
  );

  static const TextStyle headlineMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 28,
    fontWeight: bold,
    height: 1.3,
    letterSpacing: -0.6,
    color: AppColors.textPrimary,
  );

  static const TextStyle headlineSmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 24,
    fontWeight: bold,
    height: 1.3,
    letterSpacing: -0.4,
    color: AppColors.textPrimary,
  );

  // Title Text Styles - Card headers
  static const TextStyle titleLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 22,
    fontWeight: semibold,
    height: 1.35,
    letterSpacing: -0.2,
    color: AppColors.textPrimary,
  );

  static const TextStyle titleMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 18,
    fontWeight: semibold,
    height: 1.4,
    letterSpacing: 0.0,
    color: AppColors.textPrimary,
  );

  static const TextStyle titleSmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 16,
    fontWeight: semibold,
    height: 1.4,
    letterSpacing: 0.1,
    color: AppColors.textPrimary,
  );

  // Body Text Styles - Main content
  static const TextStyle bodyLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 16,
    fontWeight: regular,
    height: 1.5,
    letterSpacing: 0.2,
    color: AppColors.textSecondary,
  );

  static const TextStyle bodyMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 14,
    fontWeight: regular,
    height: 1.5,
    letterSpacing: 0.2,
    color: AppColors.textSecondary,
  );

  static const TextStyle bodySmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 12,
    fontWeight: regular,
    height: 1.5,
    letterSpacing: 0.3,
    color: AppColors.textTertiary,
  );

  // Label Text Styles - Buttons and small text
  static const TextStyle labelLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 14,
    fontWeight: semibold,
    height: 1.4,
    letterSpacing: 0.3,
    color: AppColors.textSecondary,
  );

  static const TextStyle labelMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 12,
    fontWeight: semibold,
    height: 1.4,
    letterSpacing: 0.4,
    color: AppColors.textTertiary,
  );

  static const TextStyle labelSmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 10,
    fontWeight: semibold,
    height: 1.4,
    letterSpacing: 0.5,
    color: AppColors.textMuted,
  );

  // Caption Text Styles
  static const TextStyle captionLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 14,
    fontWeight: regular,
    height: 1.4,
    letterSpacing: 0.3,
    color: AppColors.textTertiary,
  );

  static const TextStyle captionMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 12,
    fontWeight: regular,
    height: 1.4,
    letterSpacing: 0.4,
    color: AppColors.textTertiary,
  );

  static const TextStyle captionSmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 10,
    fontWeight: regular,
    height: 1.4,
    letterSpacing: 0.5,
    color: AppColors.textMuted,
  );

  // Button Text Styles
  static const TextStyle buttonLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 18,
    fontWeight: semibold,
    height: 1.2,
    letterSpacing: 0.2,
    color: AppColors.white,
  );

  static const TextStyle buttonMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 16,
    fontWeight: semibold,
    height: 1.2,
    letterSpacing: 0.3,
    color: AppColors.white,
  );

  static const TextStyle buttonSmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 14,
    fontWeight: semibold,
    height: 1.2,
    letterSpacing: 0.4,
    color: AppColors.white,
  );

  // Financial Text Styles
  static const TextStyle financialLarge = TextStyle(
    fontFamily: fontFamily,
    fontSize: 32,
    fontWeight: bold,
    height: 1.2,
    letterSpacing: -0.5,
    color: AppColors.textPrimary,
  );

  static const TextStyle financialMedium = TextStyle(
    fontFamily: fontFamily,
    fontSize: 24,
    fontWeight: semibold,
    height: 1.3,
    letterSpacing: -0.2,
    color: AppColors.textPrimary,
  );

  static const TextStyle financialSmall = TextStyle(
    fontFamily: fontFamily,
    fontSize: 18,
    fontWeight: medium,
    height: 1.4,
    letterSpacing: 0.0,
    color: AppColors.textSecondary,
  );

  // Gradient Text Styles
  static TextStyle gradientText(TextStyle baseStyle) {
    return baseStyle.copyWith(
      foreground: Paint()
        ..shader = AppColors.primaryGradient.createShader(
          const Rect.fromLTWH(0, 0, 200, 70),
        ),
    );
  }

  static TextStyle successGradientText(TextStyle baseStyle) {
    return baseStyle.copyWith(
      foreground: Paint()
        ..shader = AppColors.successGradient.createShader(
          const Rect.fromLTWH(0, 0, 200, 70),
        ),
    );
  }

  static TextStyle purpleGradientText(TextStyle baseStyle) {
    return baseStyle.copyWith(
      foreground: Paint()
        ..shader = AppColors.purpleGradient.createShader(
          const Rect.fromLTWH(0, 0, 200, 70),
        ),
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

  // Special Text Styles for specific use cases
  static const TextStyle appBarTitle = TextStyle(
    fontFamily: fontFamily,
    fontSize: 20,
    fontWeight: bold,
    height: 1.3,
    letterSpacing: -0.3,
    color: AppColors.textPrimary,
  );

  static const TextStyle cardTitle = TextStyle(
    fontFamily: fontFamily,
    fontSize: 18,
    fontWeight: semibold,
    height: 1.4,
    letterSpacing: 0.0,
    color: AppColors.textPrimary,
  );

  static const TextStyle cardSubtitle = TextStyle(
    fontFamily: fontFamily,
    fontSize: 14,
    fontWeight: regular,
    height: 1.4,
    letterSpacing: 0.1,
    color: AppColors.textTertiary,
  );

  static const TextStyle bottomNavLabel = TextStyle(
    fontFamily: fontFamily,
    fontSize: 12,
    fontWeight: medium,
    height: 1.3,
    letterSpacing: 0.5,
  );

  static const TextStyle inputLabel = TextStyle(
    fontFamily: fontFamily,
    fontSize: 16,
    fontWeight: medium,
    height: 1.4,
    letterSpacing: 0.1,
    color: AppColors.textSecondary,
  );

  static const TextStyle inputHint = TextStyle(
    fontFamily: fontFamily,
    fontSize: 16,
    fontWeight: regular,
    height: 1.4,
    letterSpacing: 0.1,
    color: AppColors.textMuted,
  );

  static const TextStyle errorText = TextStyle(
    fontFamily: fontFamily,
    fontSize: 12,
    fontWeight: medium,
    height: 1.4,
    letterSpacing: 0.3,
    color: AppColors.error,
  );
}
