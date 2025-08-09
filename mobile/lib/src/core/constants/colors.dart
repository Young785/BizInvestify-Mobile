import 'package:flutter/material.dart';

/// App color constants following the design system
class AppColors {
  AppColors._();

  // Primary Colors
  static const Color primaryPurple = Color(0xFF4C2CE0);
  static const Color lightPurple = Color(0xFF7F56D9);
  static const Color darkPurple = Color(0xFF3B1DB3);
  static const Color purpleGradientStart = Color(0xFF6366F1);
  static const Color purpleGradientEnd = Color(0xFF8B5CF6);

  // Accent Colors
  static const Color accentOrange = Color(0xFFFF8F6B);
  static const Color successGreen = Color(0xFF22C55E);
  static const Color infoBlue = Color(0xFF60A5FA);
  static const Color warningYellow = Color(0xFFF59E0B);
  static const Color errorRed = Color(0xFFEF4444);

  // Neutral Colors
  static const Color backgroundLight = Color(0xFFF8FAFC);
  static const Color backgroundGray = Color(0xFFF1F5F9);
  static const Color cardWhite = Color(0xFFFFFFFF);
  static const Color borderGray = Color(0xFFE5E7EB);
  static const Color borderLight = Color(0xFFF3F4F6);
  
  // Text Colors
  static const Color textPrimary = Color(0xFF111827);
  static const Color textSecondary = Color(0xFF667085);
  static const Color textTertiary = Color(0xFF9CA3AF);
  static const Color textDisabled = Color(0xFFD1D5DB);

  // Status Colors
  static const Color statusDone = Color(0xFF10B981);
  static const Color statusInProgress = Color(0xFFF59E0B);
  static const Color statusTodo = Color(0xFF6B7280);
  static const Color statusOverdue = Color(0xFFEF4444);

  // Gradient Colors
  static const LinearGradient primaryGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [purpleGradientStart, purpleGradientEnd],
  );

  static const LinearGradient backgroundGradient = LinearGradient(
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
    colors: [Color(0xFFF8FAFC), Color(0xFFFFFFFF)],
  );

  static const LinearGradient cardGradient = LinearGradient(
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
    colors: [Color(0xFFFFFFFF), Color(0xFFFAFBFC)],
  );
}
