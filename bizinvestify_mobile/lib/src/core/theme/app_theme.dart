import 'package:flutter/material.dart';
import '../constants/app_typography.dart';

/// BizInvestify App Theme (strict palette from tagged spec)
class AppTheme {
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
        brightness: Brightness.light,
      primaryColor: const Color(0xFF1E88E5),
      scaffoldBackgroundColor: const Color(0xFFF9FAFB),
      cardColor: Colors.white,
      colorScheme: const ColorScheme.light(
        primary: Color(0xFF1E88E5),
        onPrimary: Colors.white,
        secondary: Color(0xFF00BFA5),
        background: Color(0xFFF9FAFB),
        surface: Colors.white,
        onSurface: Color(0xFF1A1A1A),
        error: Color(0xFFD32F2F),
      ),
      fontFamily: 'Inter',
      textTheme: AppTypography.textTheme,
      visualDensity: VisualDensity.adaptivePlatformDensity,
    );
  }

  static ThemeData get darkTheme {
    return ThemeData(
      useMaterial3: true,
        brightness: Brightness.dark,
      primaryColor: const Color(0xFF90CAF9),
      scaffoldBackgroundColor: const Color(0xFF121212),
      cardColor: const Color(0xFF1E1E1E),
      colorScheme: const ColorScheme.dark(
        primary: Color(0xFF90CAF9),
        onPrimary: Color(0xFF0D47A1),
        secondary: Color(0xFF80CBC4),
        background: Color(0xFF121212),
        surface: Color(0xFF1E1E1E),
        onSurface: Color(0xFFEAEAEA),
        error: Color(0xFFEF9A9A),
      ),
      fontFamily: 'Inter',
      textTheme: AppTypography.textTheme.apply(bodyColor: const Color(0xFFEAEAEA), displayColor: const Color(0xFFEAEAEA)),
    );
  }
}
