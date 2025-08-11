import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../constants/app_typography.dart';
import '../constants/app_dimensions.dart';

/// BizInvestify App Theme
/// Material 3 design system with custom brand colors
class AppTheme {
  static ThemeData get lightTheme {
    // Strictly follow provided light color scheme
    const Color kPrimary = Color(0xFF1E88E5);
    const Color kSecondary = Color(0xFF00BFA5);
    const Color kBackground = Color(0xFFF9FAFB);
    const Color kSurface = Colors.white;
    const Color kOnSurface = Color(0xFF1A1A1A);
    const Color kError = Color(0xFFD32F2F);

    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.light,
      visualDensity: VisualDensity.adaptivePlatformDensity,
      primaryColor: kPrimary,
      scaffoldBackgroundColor: kBackground,
      cardColor: kSurface,
      fontFamily: 'Inter',
      colorScheme: const ColorScheme.light(
        primary: kPrimary,
        onPrimary: Colors.white,
        secondary: kSecondary,
        background: kBackground,
        surface: kSurface,
        onSurface: kOnSurface,
        error: kError,
      ),
      textTheme: AppTypography.textTheme,

      // App Bar Theme
      appBarTheme: AppBarTheme(
        backgroundColor: kSurface,
        foregroundColor: kOnSurface,
        elevation: 0,
        centerTitle: false,
        titleTextStyle: AppTypography.titleMedium.copyWith(
           color: kOnSurface,
          fontWeight: AppTypography.semibold,
        ),
        iconTheme: const IconThemeData(
          color: AppColors.text700,
          size: AppDimensions.iconSizeMedium,
        ),
      ),

      // Elevated Button Theme
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
           backgroundColor: kPrimary,
          foregroundColor: Colors.white,
          elevation: AppDimensions.cardElevationSmall,
          shadowColor: AppColors.shadowPrimary,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          ),
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.buttonPaddingHorizontal,
            vertical: AppDimensions.buttonPaddingVertical,
          ),
          minimumSize: Size(0, AppDimensions.buttonHeightMedium),
           textStyle: AppTypography.buttonMedium,
        ),
      ),

      // Outlined Button Theme
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: kPrimary,
          side: const BorderSide(color: kPrimary),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          ),
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.buttonPaddingHorizontal,
            vertical: AppDimensions.buttonPaddingVertical,
          ),
          minimumSize: Size(0, AppDimensions.buttonHeightMedium),
           textStyle: AppTypography.buttonMedium.copyWith(color: kPrimary),
        ),
      ),

      // Text Button Theme
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: kPrimary,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          ),
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.buttonPaddingHorizontal,
            vertical: AppDimensions.buttonPaddingVertical,
          ),
          minimumSize: Size(0, AppDimensions.buttonHeightMedium),
           textStyle: AppTypography.buttonMedium.copyWith(color: kPrimary),
        ),
      ),

      // Input Decoration Theme
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          borderSide: BorderSide(color: Colors.grey[300]!),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
           borderSide: const BorderSide(color: kPrimary, width: AppDimensions.borderWidthMedium),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          borderSide: const BorderSide(color: AppColors.error),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          borderSide: const BorderSide(
            color: AppColors.error,
            width: AppDimensions.borderWidthMedium,
          ),
        ),
        contentPadding: const EdgeInsets.symmetric(
          horizontal: AppDimensions.spacing16,
          vertical: AppDimensions.spacing16,
        ),
        labelStyle: AppTypography.labelMedium.copyWith(
          color: AppColors.text500,
        ),
        hintStyle: AppTypography.bodyMedium.copyWith(
          color: AppColors.text400,
        ),
        errorStyle: AppTypography.captionMedium.copyWith(
          color: AppColors.error,
        ),
      ),

      // Card Theme
      cardTheme: CardThemeData(
        elevation: AppDimensions.cardElevationMedium,
         shadowColor: AppColors.shadowMedium,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusLarge),
        ),
        color: Colors.white,
        margin: const EdgeInsets.all(AppDimensions.spacing8),
      ),

      // Bottom Navigation Bar Theme
       bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: kSurface,
        selectedItemColor: kPrimary,
        unselectedItemColor: AppColors.text400,
        type: BottomNavigationBarType.fixed,
        elevation: AppDimensions.cardElevationLarge,
        selectedLabelStyle: TextStyle(
          fontSize: 12,
          fontWeight: AppTypography.medium,
        ),
        unselectedLabelStyle: TextStyle(
          fontSize: 12,
          fontWeight: AppTypography.regular,
        ),
      ),

      // Floating Action Button Theme
       floatingActionButtonTheme: const FloatingActionButtonThemeData(
        backgroundColor: kPrimary,
        foregroundColor: Colors.white,
        elevation: AppDimensions.cardElevationLarge,
        shape: CircleBorder(),
      ),

      // Chip Theme
      chipTheme: ChipThemeData(
        backgroundColor: AppColors.background100,
        selectedColor: AppColors.primary100,
        disabledColor: AppColors.background200,
        labelStyle: AppTypography.labelSmall,
        secondaryLabelStyle: AppTypography.labelSmall.copyWith(
          color: AppColors.primary500,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusSmall),
        ),
        padding: const EdgeInsets.symmetric(
          horizontal: AppDimensions.spacing8,
          vertical: AppDimensions.spacing4,
        ),
      ),

      // Divider Theme
      dividerTheme: const DividerThemeData(
        color: AppColors.background300,
        thickness: AppDimensions.borderWidthThin,
        space: AppDimensions.spacing16,
      ),

      // Icon Theme
      iconTheme: const IconThemeData(
        color: AppColors.text700,
        size: AppDimensions.iconSizeMedium,
      ),

      // Primary Icon Theme
      primaryIconTheme: const IconThemeData(
        color: AppColors.primary500,
        size: AppDimensions.iconSizeMedium,
      ),

      // Snack Bar Theme
      snackBarTheme: SnackBarThemeData(
        backgroundColor: AppColors.text800,
        contentTextStyle: AppTypography.bodyMedium.copyWith(
          color: Colors.white,
        ),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
        ),
        behavior: SnackBarBehavior.floating,
        elevation: AppDimensions.cardElevationLarge,
      ),

      // Dialog Theme
      dialogTheme: DialogThemeData(
        backgroundColor: Colors.white,
        elevation: AppDimensions.cardElevationLarge,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusLarge),
        ),
        titleTextStyle: AppTypography.titleLarge,
        contentTextStyle: AppTypography.bodyMedium,
      ),

      // Bottom Sheet Theme
      bottomSheetTheme: const BottomSheetThemeData(
        backgroundColor: Colors.white,
        elevation: AppDimensions.cardElevationLarge,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(
            top: Radius.circular(AppDimensions.borderRadiusLarge),
          ),
        ),
      ),

      // Tab Bar Theme
       tabBarTheme: const TabBarThemeData(
        labelColor: kPrimary,
        unselectedLabelColor: AppColors.text500,
        indicatorColor: kPrimary,
        labelStyle: TextStyle(
          fontSize: 14,
          fontWeight: AppTypography.medium,
        ),
        unselectedLabelStyle: TextStyle(
          fontSize: 14,
          fontWeight: AppTypography.regular,
        ),
      ),

      // Switch Theme
      switchTheme: SwitchThemeData(
         thumbColor: MaterialStateProperty.resolveWith((states) {
          if (states.contains(MaterialState.selected)) {
             return kPrimary;
          }
          return Colors.grey[400];
        }),
         trackColor: MaterialStateProperty.resolveWith((states) {
          if (states.contains(MaterialState.selected)) {
             return AppColors.primary100;
          }
          return Colors.grey[300];
        }),
      ),

      // Checkbox Theme
      checkboxTheme: CheckboxThemeData(
         fillColor: MaterialStateProperty.resolveWith((states) {
          if (states.contains(MaterialState.selected)) {
             return kPrimary;
          }
          return Colors.transparent;
        }),
        checkColor: MaterialStateProperty.all(Colors.white),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusSmall),
        ),
      ),

      // Radio Theme
       radioTheme: RadioThemeData(
        fillColor: MaterialStateProperty.resolveWith((states) {
          if (states.contains(MaterialState.selected)) {
            return kPrimary;
          }
          return Colors.grey[400];
        }),
      ),

      // Slider Theme
       sliderTheme: SliderThemeData(
        activeTrackColor: kPrimary,
        inactiveTrackColor: Colors.grey[300],
        thumbColor: kPrimary,
        overlayColor: AppColors.primary100,
        valueIndicatorColor: kPrimary,
        valueIndicatorTextStyle: AppTypography.labelMedium.copyWith(
          color: Colors.white,
        ),
      ),

      // Progress Indicator Theme
       progressIndicatorTheme: const ProgressIndicatorThemeData(
        color: kPrimary,
        linearTrackColor: AppColors.background300,
        circularTrackColor: AppColors.background300,
      ),
    );
  }

  static ThemeData get darkTheme {
    // Strictly follow provided dark color scheme
    const Color kPrimary = Color(0xFF90CAF9);
    const Color kSecondary = Color(0xFF80CBC4);
    const Color kBackground = Color(0xFF121212);
    const Color kSurface = Color(0xFF1E1E1E);
    const Color kOnSurface = Color(0xFFEAEAEA);
    const Color kOnPrimary = Color(0xFF0D47A1);
    const Color kError = Color(0xFFEF9A9A);

    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      primaryColor: kPrimary,
      scaffoldBackgroundColor: kBackground,
      cardColor: kSurface,
      fontFamily: 'Inter',
      colorScheme: const ColorScheme.dark(
        primary: kPrimary,
        onPrimary: kOnPrimary,
        secondary: kSecondary,
        background: kBackground,
        surface: kSurface,
        onSurface: kOnSurface,
        error: kError,
      ),
      textTheme: AppTypography.textTheme.apply(bodyColor: Colors.white, displayColor: Colors.white),
    );
  }
}
