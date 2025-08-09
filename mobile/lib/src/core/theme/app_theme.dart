import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../constants/colors.dart';
import '../constants/dimensions.dart';
import '../constants/typography.dart';

/// App theme configuration
class AppTheme {
  AppTheme._();

  /// Light theme configuration
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.light,
      
      // Color Scheme
      colorScheme: ColorScheme.fromSeed(
        seedColor: AppColors.primaryPurple,
        brightness: Brightness.light,
        primary: AppColors.primaryPurple,
        onPrimary: AppColors.cardWhite,
        secondary: AppColors.lightPurple,
        onSecondary: AppColors.cardWhite,
        tertiary: AppColors.accentOrange,
        surface: AppColors.cardWhite,
        onSurface: AppColors.textPrimary,
        error: AppColors.errorRed,
        onError: AppColors.cardWhite,
        outline: AppColors.borderGray,
        outlineVariant: AppColors.borderLight,
      ),

      // Scaffold
      scaffoldBackgroundColor: AppColors.backgroundLight,

      // App Bar Theme
      appBarTheme: AppBarTheme(
        backgroundColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: false,
        titleTextStyle: AppTypography.headlineSmall,
        iconTheme: const IconThemeData(
          color: AppColors.textPrimary,
          size: AppDimensions.iconLarge,
        ),
        systemOverlayStyle: SystemUiOverlayStyle.dark,
      ),

      // Text Theme
      textTheme: TextTheme(
        displayLarge: AppTypography.displayLarge,
        displayMedium: AppTypography.displayMedium,
        displaySmall: AppTypography.displaySmall,
        headlineLarge: AppTypography.headlineLarge,
        headlineMedium: AppTypography.headlineMedium,
        headlineSmall: AppTypography.headlineSmall,
        titleLarge: AppTypography.titleLarge,
        titleMedium: AppTypography.titleMedium,
        titleSmall: AppTypography.titleSmall,
        bodyLarge: AppTypography.bodyLarge,
        bodyMedium: AppTypography.bodyMedium,
        bodySmall: AppTypography.bodySmall,
        labelLarge: AppTypography.labelLarge,
        labelMedium: AppTypography.labelMedium,
        labelSmall: AppTypography.labelSmall,
      ),

      // Button Themes
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primaryPurple,
          foregroundColor: AppColors.cardWhite,
          elevation: AppDimensions.elevationSmall,
          shadowColor: AppColors.primaryPurple.withValues(alpha: 0.25),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
          ),
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.spacing20,
            vertical: AppDimensions.spacing12,
          ),
          minimumSize: const Size(0, AppDimensions.buttonMedium),
          textStyle: AppTypography.buttonMedium,
        ),
      ),

      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: AppColors.primaryPurple,
          foregroundColor: AppColors.cardWhite,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
          ),
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.spacing20,
            vertical: AppDimensions.spacing12,
          ),
          minimumSize: const Size(0, AppDimensions.buttonMedium),
          textStyle: AppTypography.buttonMedium,
        ),
      ),

      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: AppColors.primaryPurple,
          side: const BorderSide(
            color: AppColors.primaryPurple,
            width: 1.5,
          ),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
          ),
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.spacing20,
            vertical: AppDimensions.spacing12,
          ),
          minimumSize: const Size(0, AppDimensions.buttonMedium),
          textStyle: AppTypography.buttonMedium.copyWith(
            color: AppColors.primaryPurple,
          ),
        ),
      ),

      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: AppColors.primaryPurple,
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.spacing16,
            vertical: AppDimensions.spacing8,
          ),
          textStyle: AppTypography.buttonMedium.copyWith(
            color: AppColors.primaryPurple,
          ),
        ),
      ),

      // Input Decoration Theme
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.cardWhite,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: AppDimensions.spacing16,
          vertical: AppDimensions.spacing12,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
          borderSide: const BorderSide(
            color: AppColors.borderGray,
            width: 1.0,
          ),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
          borderSide: const BorderSide(
            color: AppColors.borderGray,
            width: 1.0,
          ),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
          borderSide: const BorderSide(
            color: AppColors.primaryPurple,
            width: 2.0,
          ),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
          borderSide: const BorderSide(
            color: AppColors.errorRed,
            width: 1.0,
          ),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
          borderSide: const BorderSide(
            color: AppColors.errorRed,
            width: 2.0,
          ),
        ),
        labelStyle: AppTypography.bodyMedium,
        hintStyle: AppTypography.bodyMedium.copyWith(
          color: AppColors.textTertiary,
        ),
        errorStyle: AppTypography.bodySmall.copyWith(
          color: AppColors.errorRed,
        ),
      ),

      // Card Theme
      cardTheme: CardThemeData(
        color: AppColors.cardWhite,
        elevation: AppDimensions.elevationSmall,
        shadowColor: AppColors.textPrimary.withValues(alpha: 0.08),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppDimensions.radiusLarge),
        ),
        margin: const EdgeInsets.all(AppDimensions.spacing8),
      ),

      // Chip Theme
      chipTheme: ChipThemeData(
        backgroundColor: AppColors.backgroundGray,
        selectedColor: AppColors.primaryPurple,
        labelStyle: AppTypography.labelMedium,
        padding: const EdgeInsets.symmetric(
          horizontal: AppDimensions.spacing12,
          vertical: AppDimensions.spacing4,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppDimensions.radiusRound),
        ),
      ),

      // Floating Action Button Theme
      floatingActionButtonTheme: const FloatingActionButtonThemeData(
        backgroundColor: AppColors.primaryPurple,
        foregroundColor: AppColors.cardWhite,
        elevation: AppDimensions.elevationMedium,
        shape: CircleBorder(),
      ),

      // Bottom Navigation Bar Theme
      bottomNavigationBarTheme: BottomNavigationBarThemeData(
        backgroundColor: AppColors.cardWhite,
        selectedItemColor: AppColors.primaryPurple,
        unselectedItemColor: AppColors.textTertiary,
        selectedLabelStyle: AppTypography.labelSmall.copyWith(
          color: AppColors.primaryPurple,
          fontWeight: AppTypography.semiBold,
        ),
        unselectedLabelStyle: AppTypography.labelSmall,
        type: BottomNavigationBarType.fixed,
        elevation: AppDimensions.elevationMedium,
      ),

      // Progress Indicator Theme
      progressIndicatorTheme: const ProgressIndicatorThemeData(
        color: AppColors.primaryPurple,
        linearTrackColor: AppColors.borderLight,
        circularTrackColor: AppColors.borderLight,
      ),

      // Divider Theme
      dividerTheme: const DividerThemeData(
        color: AppColors.borderLight,
        thickness: 1.0,
        space: 1.0,
      ),

      // Icon Theme
      iconTheme: const IconThemeData(
        color: AppColors.textSecondary,
        size: AppDimensions.iconMedium,
      ),

      // Primary Icon Theme
      primaryIconTheme: const IconThemeData(
        color: AppColors.primaryPurple,
        size: AppDimensions.iconMedium,
      ),
    );
  }

  /// Dark theme configuration (optional - can be implemented later)
  static ThemeData get darkTheme {
    // For now, return light theme - can be implemented later
    return lightTheme;
  }
}
