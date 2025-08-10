import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../constants/app_colors.dart';
import '../constants/app_typography.dart';
import '../constants/app_dimensions.dart';

/// BizInvestify Professional App Theme
/// Modern Material 3 design system for production fintech app
class AppTheme {
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      fontFamily: AppTypography.fontFamily,
      
      // Modern Color Scheme (non-const to allow dynamic shadows)
      colorScheme: ColorScheme.light(
        brightness: Brightness.light,
        primary: AppColors.primary500,
        onPrimary: AppColors.white,
        primaryContainer: AppColors.primary100,
        onPrimaryContainer: AppColors.primary900,
        secondary: AppColors.success500,
        onSecondary: AppColors.white,
        secondaryContainer: AppColors.success100,
        onSecondaryContainer: AppColors.success900,
        tertiary: AppColors.purple500,
        onTertiary: AppColors.white,
        tertiaryContainer: AppColors.purple100,
        onTertiaryContainer: AppColors.purple900,
        error: AppColors.error,
        onError: AppColors.white,
        errorContainer: Color(0xFFFFEBEE),
        onErrorContainer: Color(0xFFBA1A1A),
        background: AppColors.backgroundPrimary,
        onBackground: AppColors.textPrimary,
        surface: AppColors.backgroundSecondary,
        onSurface: AppColors.textPrimary,
        surfaceVariant: AppColors.backgroundTertiary,
        onSurfaceVariant: AppColors.textSecondary,
        outline: AppColors.borderPrimary,
        outlineVariant: AppColors.borderSecondary,
        shadow: AppColors.shadowNeutral,
        scrim: AppColors.overlayMedium,
        inverseSurface: AppColors.neutral800,
        onInverseSurface: AppColors.neutral100,
        inversePrimary: AppColors.primary300,
        surfaceTint: AppColors.primary500,
      ),
      
      scaffoldBackgroundColor: AppColors.backgroundPrimary,
      textTheme: AppTypography.textTheme,
      
      // App Bar Theme - Modern and clean
      appBarTheme: AppBarTheme(
        backgroundColor: AppColors.backgroundSecondary,
        foregroundColor: AppColors.textPrimary,
        elevation: 0,
        scrolledUnderElevation: 1,
        surfaceTintColor: Colors.transparent,
        shadowColor: AppColors.shadowSoft,
        centerTitle: false,
        titleSpacing: AppDimensions.containerPaddingMobile,
        systemOverlayStyle: const SystemUiOverlayStyle(
          statusBarColor: Colors.transparent,
          statusBarIconBrightness: Brightness.dark,
          statusBarBrightness: Brightness.light,
        ),
        titleTextStyle: AppTypography.appBarTitle,
        iconTheme: IconThemeData(
          color: AppColors.textSecondary,
          size: AppDimensions.iconSizeLarge,
        ),
        actionsIconTheme: IconThemeData(
          color: AppColors.textSecondary,
          size: AppDimensions.iconSizeLarge,
        ),
      ),
      
      // Elevated Button Theme - Modern and professional
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primary500,
          foregroundColor: AppColors.white,
          elevation: 0,
          shadowColor: Colors.transparent,
          surfaceTintColor: Colors.transparent,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          ),
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.buttonPaddingHorizontal,
            vertical: AppDimensions.buttonPaddingVertical,
          ),
          minimumSize: Size(0, AppDimensions.buttonHeightMedium),
          textStyle: AppTypography.buttonMedium,
        ).copyWith(
          overlayColor: MaterialStateProperty.resolveWith<Color?>(
            (Set<MaterialState> states) {
              if (states.contains(MaterialState.pressed)) {
                return AppColors.white.withOpacity(0.12);
              }
              if (states.contains(MaterialState.hovered)) {
                return AppColors.white.withOpacity(0.08);
              }
              if (states.contains(MaterialState.focused)) {
                return AppColors.white.withOpacity(0.12);
              }
              return null;
            },
          ),
        ),
      ),
      
      // Outlined Button Theme - Professional styling
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: AppColors.primary500,
          backgroundColor: Colors.transparent,
          side: const BorderSide(
            color: AppColors.primary500,
            width: 1.5,
          ),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          ),
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.buttonPaddingHorizontal,
            vertical: AppDimensions.buttonPaddingVertical,
          ),
          minimumSize: Size(0, AppDimensions.buttonHeightMedium),
          textStyle: AppTypography.buttonMedium.copyWith(
            color: AppColors.primary500,
          ),
        ).copyWith(
          overlayColor: MaterialStateProperty.resolveWith<Color?>(
            (Set<MaterialState> states) {
              if (states.contains(MaterialState.pressed)) {
                return AppColors.primary500.withOpacity(0.08);
              }
              if (states.contains(MaterialState.hovered)) {
                return AppColors.primary500.withOpacity(0.04);
              }
              return null;
            },
          ),
        ),
      ),
      
      // Text Button Theme - Clean and minimal
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: AppColors.primary500,
          backgroundColor: Colors.transparent,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppDimensions.borderRadiusSmall),
          ),
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.spacing20,
            vertical: AppDimensions.spacing12,
          ),
          minimumSize: Size(0, AppDimensions.buttonHeightSmall),
          textStyle: AppTypography.buttonMedium.copyWith(
            color: AppColors.primary500,
          ),
        ).copyWith(
          overlayColor: MaterialStateProperty.resolveWith<Color?>(
            (Set<MaterialState> states) {
              if (states.contains(MaterialState.pressed)) {
                return AppColors.primary500.withOpacity(0.08);
              }
              if (states.contains(MaterialState.hovered)) {
                return AppColors.primary500.withOpacity(0.04);
              }
              return null;
            },
          ),
        ),
      ),
      
      // Input Decoration Theme - Modern and professional
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.backgroundSecondary,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: AppDimensions.spacing20,
          vertical: AppDimensions.spacing16,
        ),
        
        // Border styles
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          borderSide: const BorderSide(
            color: AppColors.borderPrimary,
            width: 1,
          ),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          borderSide: const BorderSide(
            color: AppColors.borderPrimary,
            width: 1,
          ),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          borderSide: const BorderSide(
            color: AppColors.borderFocus,
            width: 2,
          ),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          borderSide: const BorderSide(
            color: AppColors.error,
            width: 1,
          ),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          borderSide: const BorderSide(
            color: AppColors.error,
            width: 2,
          ),
        ),
        disabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
          borderSide: const BorderSide(
            color: AppColors.neutral300,
            width: 1,
          ),
        ),
        
        // Text styles
        labelStyle: AppTypography.inputLabel,
        floatingLabelStyle: AppTypography.inputLabel.copyWith(
          color: AppColors.borderFocus,
        ),
        hintStyle: AppTypography.inputHint,
        helperStyle: AppTypography.bodySmall.copyWith(
          color: AppColors.textTertiary,
        ),
        errorStyle: AppTypography.errorText,
      ),
      
      // Card Theme - Modern with subtle borders
      cardTheme: CardThemeData(
        elevation: 0,
        shadowColor: Colors.transparent,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusLarge),
          side: const BorderSide(
            color: AppColors.borderPrimary,
            width: 1,
          ),
        ),
        color: AppColors.backgroundSecondary,
        margin: const EdgeInsets.symmetric(
          horizontal: AppDimensions.containerPaddingMobile,
          vertical: AppDimensions.spacing8,
        ),
      ),
      
      // Bottom Navigation Bar Theme - Modern and clean
      bottomNavigationBarTheme: BottomNavigationBarThemeData(
        backgroundColor: AppColors.backgroundSecondary,
        selectedItemColor: AppColors.primary500,
        unselectedItemColor: AppColors.textTertiary,
        type: BottomNavigationBarType.fixed,
        elevation: 8,
        selectedLabelStyle: AppTypography.bottomNavLabel.copyWith(
          color: AppColors.primary500,
          fontWeight: AppTypography.semibold,
        ),
        unselectedLabelStyle: AppTypography.bottomNavLabel.copyWith(
          color: AppColors.textTertiary,
          fontWeight: AppTypography.medium,
        ),
        selectedIconTheme: IconThemeData(
          color: AppColors.primary500,
          size: AppDimensions.iconSizeLarge,
        ),
        unselectedIconTheme: IconThemeData(
          color: AppColors.textTertiary,
          size: AppDimensions.iconSizeLarge,
        ),
      ),
      
      // Floating Action Button Theme - Modern gradient
      floatingActionButtonTheme: FloatingActionButtonThemeData(
        backgroundColor: AppColors.primary500,
        foregroundColor: AppColors.white,
        elevation: 6,
        highlightElevation: 8,
        shape: const CircleBorder(),
        sizeConstraints: const BoxConstraints.tightFor(
          width: AppDimensions.spacing64,
          height: AppDimensions.spacing64,
        ),
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
        labelColor: AppColors.primary500,
        unselectedLabelColor: AppColors.text500,
        indicatorColor: AppColors.primary500,
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
            return AppColors.primary500;
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
            return AppColors.primary500;
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
            return AppColors.primary500;
          }
          return Colors.grey[400];
        }),
      ),
      
      // Slider Theme
      sliderTheme: SliderThemeData(
        activeTrackColor: AppColors.primary500,
        inactiveTrackColor: Colors.grey[300],
        thumbColor: AppColors.primary500,
        overlayColor: AppColors.primary100,
        valueIndicatorColor: AppColors.primary500,
        valueIndicatorTextStyle: AppTypography.labelMedium.copyWith(
          color: Colors.white,
        ),
      ),
      
      // Progress Indicator Theme
      progressIndicatorTheme: const ProgressIndicatorThemeData(
        color: AppColors.primary500,
        linearTrackColor: AppColors.background300,
        circularTrackColor: AppColors.background300,
      ),
    );
  }

  static ThemeData get darkTheme {
    return ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: AppColors.primary500,
        brightness: Brightness.dark,
        primary: AppColors.primary400,
        secondary: AppColors.accent400,
        surface: const Color(0xFF1E1E1E),
        background: const Color(0xFF121212),
        error: AppColors.error,
        onPrimary: Colors.white,
        onSecondary: Colors.white,
        onSurface: Colors.white,
        onBackground: Colors.white,
        onError: Colors.white,
      ),
      textTheme: AppTypography.textTheme.apply(
        bodyColor: Colors.white,
        displayColor: Colors.white,
      ),
      
      // Similar theme customization for dark mode
      // (Implementation would be similar to light theme but with dark colors)
    );
  }
}
