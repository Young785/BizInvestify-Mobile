import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../constants/app_colors.dart';
import '../constants/app_typography.dart';


/// BizInvestify Professional App Theme
/// Production-ready Material 3 design system with sophisticated brand identity
class AppTheme {
  // === THEME CONSTANTS ===
  
  static const double _elevationSmall = 2.0;
  static const double _elevationMedium = 4.0;
  static const double _elevationLarge = 8.0;
  static const double _elevationXLarge = 12.0;
  
  static const double _borderRadiusMedium = 12.0;
  static const double _borderRadiusLarge = 16.0;
  
  // === LIGHT THEME ===
  
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      
      // === COLOR SCHEME ===
      colorScheme: ColorScheme.light(
        brightness: Brightness.light,
        
        // Primary colors
        primary: AppColors.primary500,
        onPrimary: AppColors.textInverse,
        primaryContainer: AppColors.primary50,
        onPrimaryContainer: AppColors.primary900,
        
        // Secondary colors
        secondary: AppColors.secondary500,
        onSecondary: AppColors.textInverse,
        secondaryContainer: AppColors.secondary50,
        onSecondaryContainer: AppColors.secondary900,
        
        // Tertiary colors
        tertiary: AppColors.secondary500,
        onTertiary: AppColors.textInverse,
        tertiaryContainer: AppColors.secondary50,
        onTertiaryContainer: AppColors.accent900,
        
        // Error colors
        error: AppColors.error500,
        onError: AppColors.textInverse,
        errorContainer: AppColors.error50,
        onErrorContainer: AppColors.error600,
        
        // Surface colors
        surface: AppColors.backgroundPrimary,
        onSurface: AppColors.textPrimary,
        surfaceVariant: AppColors.backgroundSecondary,
        onSurfaceVariant: AppColors.textSecondary,
        
        // Background colors
        background: AppColors.backgroundSecondary,
        onBackground: AppColors.textPrimary,
        
        // Outline colors
        outline: AppColors.surfaceBorder,
        outlineVariant: AppColors.surfaceDivider,
        
        // Shadow and scrim
        shadow: AppColors.shadowMd,
        scrim: AppColors.backdrop,
        
        // Inverse colors
        inverseSurface: AppColors.textPrimary,
        onInverseSurface: AppColors.textInverse,
        inversePrimary: AppColors.primary200,
      ),
      
      // Typography
      textTheme: AppTypography.textTheme,
      primaryTextTheme: AppTypography.textTheme,
      
      // === APP BAR THEME ===
      appBarTheme: AppBarTheme(
        backgroundColor: AppColors.backgroundPrimary,
        foregroundColor: AppColors.textPrimary,
        surfaceTintColor: AppColors.transparent,
        elevation: 0,
        scrolledUnderElevation: _elevationSmall,
        shadowColor: AppColors.shadowSm,
        centerTitle: false,
        titleSpacing: 16.0,
        
        titleTextStyle: AppTypography.headlineSmall.copyWith(
          color: AppColors.textPrimary,
          fontWeight: AppTypography.bold,
        ),
        
        iconTheme: IconThemeData(
          color: AppColors.textSecondary,
          size: 24.0,
        ),
        
        actionsIconTheme: IconThemeData(
          color: AppColors.textSecondary,
          size: 24.0,
        ),
        
        systemOverlayStyle: SystemUiOverlayStyle.dark,
      ),
      
      // === BUTTON THEMES ===
      
      // Elevated Button
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primary500,
          foregroundColor: AppColors.textInverse,
          surfaceTintColor: AppColors.transparent,
          elevation: _elevationSmall,
          shadowColor: AppColors.shadowPrimary,
          
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(_borderRadiusMedium),
          ),
          
          padding: const EdgeInsets.symmetric(
            horizontal: 24.0,
            vertical: 16.0,
          ),
          
          minimumSize: const Size(0, 48.0),
          maximumSize: const Size(double.infinity, 56.0),
          
          textStyle: AppTypography.buttonMedium,
          
          // Interactive states
        ).copyWith(
          backgroundColor: MaterialStateProperty.resolveWith((states) {
            if (states.contains(MaterialState.pressed)) {
              return AppColors.primary700;
            }
            if (states.contains(MaterialState.hovered)) {
              return AppColors.primary600;
            }
            if (states.contains(MaterialState.disabled)) {
              return AppColors.interactiveDisabled;
            }
            return AppColors.primary500;
          }),
          
          elevation: MaterialStateProperty.resolveWith((states) {
            if (states.contains(MaterialState.pressed)) {
              return _elevationSmall;
            }
            if (states.contains(MaterialState.hovered)) {
              return _elevationMedium;
            }
            if (states.contains(MaterialState.disabled)) {
              return 0;
            }
            return _elevationSmall;
          }),
        ),
      ),
      
      // Outlined Button
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: AppColors.primary500,
          backgroundColor: AppColors.transparent,
          
          side: const BorderSide(
            color: AppColors.primary500,
            width: 1.5,
          ),
          
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(_borderRadiusMedium),
          ),
          
          padding: const EdgeInsets.symmetric(
            horizontal: 24.0,
            vertical: 16.0,
          ),
          
          minimumSize: const Size(0, 48.0),
          maximumSize: const Size(double.infinity, 56.0),
          
          textStyle: AppTypography.buttonMedium.copyWith(
            color: AppColors.primary500,
          ),
          
        ).copyWith(
          foregroundColor: MaterialStateProperty.resolveWith((states) {
            if (states.contains(MaterialState.pressed)) {
              return AppColors.primary700;
            }
            if (states.contains(MaterialState.hovered)) {
              return AppColors.primary600;
            }
            if (states.contains(MaterialState.disabled)) {
              return AppColors.textQuaternary;
            }
            return AppColors.primary500;
          }),
          
          side: MaterialStateProperty.resolveWith((states) {
            if (states.contains(MaterialState.pressed)) {
              return BorderSide(color: AppColors.primary700, width: 1.5);
            }
            if (states.contains(MaterialState.hovered)) {
              return BorderSide(color: AppColors.primary600, width: 1.5);
            }
            if (states.contains(MaterialState.disabled)) {
              return BorderSide(color: AppColors.textQuaternary, width: 1.5);
            }
            return const BorderSide(color: AppColors.primary500, width: 1.5);
          }),
        ),
      ),
      
      // Text Button
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: AppColors.primary500,
          backgroundColor: AppColors.transparent,
          
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(_borderRadiusMedium),
          ),
          
          padding: const EdgeInsets.symmetric(
            horizontal: 16.0,
            vertical: 12.0,
          ),
          
          minimumSize: const Size(0, 44.0),
          
          textStyle: AppTypography.buttonMedium.copyWith(
            color: AppColors.primary500,
          ),
          
        ).copyWith(
          foregroundColor: MaterialStateProperty.resolveWith((states) {
            if (states.contains(MaterialState.pressed)) {
              return AppColors.primary700;
            }
            if (states.contains(MaterialState.hovered)) {
              return AppColors.primary600;
            }
            if (states.contains(MaterialState.disabled)) {
              return AppColors.textQuaternary;
            }
            return AppColors.primary500;
          }),
          
          backgroundColor: MaterialStateProperty.resolveWith((states) {
            if (states.contains(MaterialState.pressed)) {
              return AppColors.primary50;
            }
            if (states.contains(MaterialState.hovered)) {
              return AppColors.primary50.withOpacity(0.5);
            }
            return AppColors.transparent;
          }),
        ),
      ),
      
      // === INPUT DECORATION THEME ===
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.backgroundSecondary,
        
        // Border styles
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(_borderRadiusMedium),
          borderSide: BorderSide(
            color: AppColors.surfaceBorder,
            width: 1.0,
          ),
        ),
        
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(_borderRadiusMedium),
          borderSide: BorderSide(
            color: AppColors.surfaceBorder,
            width: 1.0,
          ),
        ),
        
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(_borderRadiusMedium),
          borderSide: const BorderSide(
            color: AppColors.primary500,
            width: 2.0,
          ),
        ),
        
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(_borderRadiusMedium),
          borderSide: const BorderSide(
            color: AppColors.error500,
            width: 1.0,
          ),
        ),
        
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(_borderRadiusMedium),
          borderSide: const BorderSide(
            color: AppColors.error500,
            width: 2.0,
          ),
        ),
        
        disabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(_borderRadiusMedium),
          borderSide: BorderSide(
            color: AppColors.surfaceDivider,
            width: 1.0,
          ),
        ),
        
        // Content styling
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16.0,
          vertical: 16.0,
        ),
        
        // Text styles
        labelStyle: AppTypography.labelMedium.copyWith(
          color: AppColors.textTertiary,
        ),
        
        floatingLabelStyle: AppTypography.labelMedium.copyWith(
          color: AppColors.primary500,
        ),
        
        hintStyle: AppTypography.bodyMedium.copyWith(
          color: AppColors.textQuaternary,
        ),
        
        helperStyle: AppTypography.captionMedium.copyWith(
          color: AppColors.textTertiary,
        ),
        
        errorStyle: AppTypography.captionMedium.copyWith(
          color: AppColors.error500,
        ),
        
        // Icon styling
        prefixIconColor: AppColors.textTertiary,
        suffixIconColor: AppColors.textTertiary,
        
        // Behavior
        floatingLabelBehavior: FloatingLabelBehavior.auto,
        alignLabelWithHint: true,
      ),
      
      // === CARD THEME ===
      cardTheme: CardThemeData(
        elevation: _elevationSmall,
        shadowColor: AppColors.shadowSm,
        surfaceTintColor: AppColors.transparent,
        
        color: AppColors.backgroundElevated,
        
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(_borderRadiusLarge),
          side: BorderSide(
            color: AppColors.surfaceBorder.withOpacity(0.1),
            width: 0.5,
          ),
        ),
        
        margin: const EdgeInsets.symmetric(
          horizontal: 4.0,
          vertical: 4.0,
        ),
        
        clipBehavior: Clip.antiAlias,
      ),
      
      // === BOTTOM NAVIGATION THEME ===
      bottomNavigationBarTheme: BottomNavigationBarThemeData(
        backgroundColor: AppColors.backgroundElevated,
        selectedItemColor: AppColors.primary500,
        unselectedItemColor: AppColors.textTertiary,
        
        type: BottomNavigationBarType.fixed,
        elevation: _elevationMedium,
        
        selectedIconTheme: const IconThemeData(
          size: 24.0,
          color: AppColors.primary500,
        ),
        
        unselectedIconTheme: IconThemeData(
          size: 24.0,
          color: AppColors.textTertiary,
        ),
        
        selectedLabelStyle: AppTypography.labelSmall.copyWith(
          color: AppColors.primary500,
          fontWeight: AppTypography.semibold,
        ),
        
        unselectedLabelStyle: AppTypography.labelSmall.copyWith(
          color: AppColors.textTertiary,
          fontWeight: AppTypography.medium,
        ),
        
        showSelectedLabels: true,
        showUnselectedLabels: true,
        enableFeedback: true,
      ),
      
      // === FLOATING ACTION BUTTON THEME ===
      floatingActionButtonTheme: FloatingActionButtonThemeData(
        backgroundColor: AppColors.primary500,
        foregroundColor: AppColors.textInverse,
        splashColor: AppColors.primary700.withOpacity(0.3),
        
        elevation: _elevationMedium,
        focusElevation: _elevationLarge,
        hoverElevation: _elevationLarge,
        highlightElevation: _elevationXLarge,
        
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(_borderRadiusLarge),
        ),
        
        iconSize: 24.0,
        sizeConstraints: const BoxConstraints(
          minWidth: 56.0,
          minHeight: 56.0,
        ),
        
        enableFeedback: true,
      ),
      
      // === VISUAL DENSITY ===
      visualDensity: VisualDensity.standard,
      
      // === MATERIAL TAP TARGET SIZE ===
      materialTapTargetSize: MaterialTapTargetSize.padded,
      
      // === PAGE TRANSITIONS ===
      pageTransitionsTheme: const PageTransitionsTheme(
        builders: {
          TargetPlatform.android: CupertinoPageTransitionsBuilder(),
          TargetPlatform.iOS: CupertinoPageTransitionsBuilder(),
          TargetPlatform.macOS: CupertinoPageTransitionsBuilder(),
        },
      ),
      
      // === SPLASH FACTORY ===
      splashFactory: InkRipple.splashFactory,
      

    );
  }
  
  // === DARK THEME ===
  
  static ThemeData get darkTheme {
    return lightTheme.copyWith(
      brightness: Brightness.dark,
      
      // Dark Color Scheme
      colorScheme: ColorScheme.dark(
        brightness: Brightness.dark,
        
        // Primary colors
        primary: AppColors.primary400,
        onPrimary: AppColors.textPrimary,
        primaryContainer: AppColors.primary800,
        onPrimaryContainer: AppColors.primary100,
        
        // Secondary colors
        secondary: AppColors.secondary400,
        onSecondary: AppColors.textPrimary,
        secondaryContainer: AppColors.secondary800,
        onSecondaryContainer: AppColors.secondary100,
        
        // Tertiary colors
        tertiary: AppColors.secondary400,
        onTertiary: AppColors.textPrimary,
        tertiaryContainer: AppColors.accent800,
        onTertiaryContainer: AppColors.secondary100,
        
        // Error colors
        error: AppColors.error500,
        onError: AppColors.textInverse,
        errorContainer: AppColors.error50,
        onErrorContainer: AppColors.error600,
        
        // Surface colors
        surface: const Color(0xFF121212),
        onSurface: AppColors.textInverse,
        surfaceVariant: const Color(0xFF1E1E1E),
        onSurfaceVariant: const Color(0xFFE0E0E0),
        
        // Background colors
        background: const Color(0xFF0A0A0A),
        onBackground: AppColors.textInverse,
        
        // Outline colors
        outline: const Color(0xFF404040),
        outlineVariant: const Color(0xFF2A2A2A),
        
        // Shadow and scrim
        shadow: const Color(0xFF000000),
        scrim: const Color(0xDD000000),
        
        // Inverse colors
        inverseSurface: AppColors.backgroundElevated,
        onInverseSurface: AppColors.textPrimary,
        inversePrimary: AppColors.primary500,
      ),
      
      // Dark Text Theme
      textTheme: AppTypography.textTheme.apply(
        bodyColor: AppColors.textInverse,
        displayColor: AppColors.textInverse,
      ),
      primaryTextTheme: AppTypography.textTheme.apply(
        bodyColor: AppColors.textInverse,
        displayColor: AppColors.textInverse,
      ),
      
      // Override specific components for dark theme
      scaffoldBackgroundColor: const Color(0xFF0A0A0A),
      canvasColor: const Color(0xFF121212),
      cardColor: const Color(0xFF1E1E1E),
      dividerColor: const Color(0xFF2A2A2A),
      
      // Dark App Bar
      appBarTheme: lightTheme.appBarTheme.copyWith(
        backgroundColor: const Color(0xFF121212),
        foregroundColor: AppColors.textInverse,
        iconTheme: const IconThemeData(
          color: Color(0xFFE0E0E0),
          size: 24.0,
        ),
      ),
      
      // Dark Bottom Navigation
      bottomNavigationBarTheme: lightTheme.bottomNavigationBarTheme.copyWith(
        backgroundColor: const Color(0xFF1E1E1E),
        selectedItemColor: AppColors.primary400,
        unselectedItemColor: const Color(0xFF808080),
      ),
    );
  }
  
  // === THEME EXTENSIONS ===
  
  // High Contrast Theme
  static ThemeData get highContrastTheme {
    return lightTheme.copyWith(
      textTheme: AppTypography.textTheme,
      
      colorScheme: lightTheme.colorScheme.copyWith(
        primary: const Color(0xFF000000),
        onPrimary: const Color(0xFFFFFFFF),
        secondary: const Color(0xFF000000),
        onSecondary: const Color(0xFFFFFFFF),
        surface: const Color(0xFFFFFFFF),
        onSurface: const Color(0xFF000000),
        background: const Color(0xFFFFFFFF),
        onBackground: const Color(0xFF000000),
        outline: const Color(0xFF000000),
      ),
    );
  }
  
  // Get theme based on platform and brightness
  static ThemeData getTheme({
    required Brightness brightness,
    bool highContrast = false,
  }) {
    if (highContrast) {
      return highContrastTheme;
    }
    
    return brightness == Brightness.dark ? darkTheme : lightTheme;
  }
  
  // === UTILITY METHODS ===
  
  // Create custom elevated button style
  static ButtonStyle elevatedButtonStyle({
    Color? backgroundColor,
    Color? foregroundColor,
    double? elevation,
    EdgeInsetsGeometry? padding,
    Size? minimumSize,
    BorderRadius? borderRadius,
  }) {
    return ElevatedButton.styleFrom(
      backgroundColor: backgroundColor ?? AppColors.primary500,
      foregroundColor: foregroundColor ?? AppColors.textInverse,
      elevation: elevation ?? _elevationSmall,
      padding: padding ?? const EdgeInsets.symmetric(
        horizontal: 24.0,
        vertical: 16.0,
      ),
      minimumSize: minimumSize ?? const Size(0, 48.0),
      shape: RoundedRectangleBorder(
        borderRadius: borderRadius ?? BorderRadius.circular(_borderRadiusMedium),
      ),
    );
  }
  
  // Create custom card decoration
  static BoxDecoration cardDecoration({
    Color? color,
    double? elevation,
    BorderRadius? borderRadius,
    Border? border,
    List<BoxShadow>? boxShadow,
  }) {
    return BoxDecoration(
      color: color ?? AppColors.backgroundElevated,
      borderRadius: borderRadius ?? BorderRadius.circular(_borderRadiusLarge),
      border: border ?? Border.all(
        color: AppColors.surfaceBorder.withOpacity(0.1),
        width: 0.5,
      ),
      boxShadow: boxShadow ?? [
        BoxShadow(
          color: AppColors.shadowSm,
          blurRadius: 8.0,
          offset: const Offset(0, 2),
          spreadRadius: 0,
        ),
      ],
    );
  }
  
  // Create gradient decoration
  static BoxDecoration gradientDecoration({
    required Gradient gradient,
    BorderRadius? borderRadius,
    Border? border,
    List<BoxShadow>? boxShadow,
  }) {
    return BoxDecoration(
      gradient: gradient,
      borderRadius: borderRadius ?? BorderRadius.circular(_borderRadiusLarge),
      border: border,
      boxShadow: boxShadow,
    );
  }
  
  // Create professional input decoration
  static InputDecoration inputDecoration({
    String? labelText,
    String? hintText,
    String? helperText,
    Widget? prefixIcon,
    Widget? suffixIcon,
    bool enabled = true,
  }) {
    return InputDecoration(
      labelText: labelText,
      hintText: hintText,
      helperText: helperText,
      prefixIcon: prefixIcon,
      suffixIcon: suffixIcon,
      enabled: enabled,
      
      filled: true,
      fillColor: enabled 
          ? AppColors.backgroundSecondary 
          : AppColors.surfaceDivider,
      
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(_borderRadiusMedium),
        borderSide: BorderSide(
          color: AppColors.surfaceBorder,
          width: 1.0,
        ),
      ),
      
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(_borderRadiusMedium),
        borderSide: const BorderSide(
          color: AppColors.primary500,
          width: 2.0,
        ),
      ),
      
      contentPadding: const EdgeInsets.symmetric(
        horizontal: 16.0,
        vertical: 16.0,
      ),
    );
  }
}