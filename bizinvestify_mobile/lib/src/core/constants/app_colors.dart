import 'package:flutter/material.dart';

/// BizInvestify Brand Colors
/// Based on the web platform design system
class AppColors {
  // Primary Colors (Indigo scale)
  static const Color primary50 = Color(0xFFEEF2FF);
  static const Color primary100 = Color(0xFFE0E7FF);
  static const Color primary200 = Color(0xFFC7D2FE);
  static const Color primary300 = Color(0xFFA5B4FC);
  static const Color primary400 = Color(0xFF818CF8);
  static const Color primary500 = Color(0xFF6366F1); // Indigo 500
  static const Color primary600 = Color(0xFF4F46E5);
  static const Color primary700 = Color(0xFF4338CA);
  static const Color primary800 = Color(0xFF3730A3);
  static const Color primary900 = Color(0xFF312E81);

  // Accent Colors (Purple scale)
  static const Color accent50 = Color(0xFFFAF5FF);
  static const Color accent100 = Color(0xFFF3E8FF);
  static const Color accent200 = Color(0xFFE9D5FF);
  static const Color accent300 = Color(0xFFD8B4FE);
  static const Color accent400 = Color(0xFFC084FC);
  static const Color accent500 = Color(0xFFA855F7); // Purple 500
  static const Color accent600 = Color(0xFF9333EA);
  static const Color accent700 = Color(0xFF7E22CE);
  static const Color accent800 = Color(0xFF6B21A8);
  static const Color accent900 = Color(0xFF581C87);

  // Text Colors
  static const Color text50 = Color(0xFFF5F5F5);
  static const Color text100 = Color(0xFFE5E5E5);
  static const Color text200 = Color(0xFFCCCCCC);
  static const Color text300 = Color(0xFFB3B3B3);
  static const Color text400 = Color(0xFF999999);
  static const Color text500 = Color(0xFF666666);
  static const Color text600 = Color(0xFF4D4D4D);
  static const Color text700 = Color(0xFF333333);
  static const Color text800 = Color(0xFF1C1C1E); // Dark Charcoal
  static const Color text900 = Color(0xFF000000);

  // Background Colors
  static const Color background50 = Color(0xFFFFFFFF);
  static const Color background100 = Color(0xFFF8F9FA);
  static const Color background200 = Color(0xFFF9FAFB); // Softer light grey per tagged theme
  static const Color background300 = Color(0xFFE9ECEF);
  static const Color background400 = Color(0xFFDEE2E6);
  static const Color background500 = Color(0xFFCED4DA);
  static const Color background600 = Color(0xFFADB5BD);
  static const Color background700 = Color(0xFF6C757D);
  static const Color background800 = Color(0xFF495057);
  static const Color background900 = Color(0xFF343A40);

  // Status Colors
  static const Color success = Color(0xFF00C48C);
  static const Color warning = Color(0xFFFFA726);
  static const Color error = Color(0xFFEF5350);
  static const Color info = Color(0xFF42A5F5);

  // Gradient Colors
  static const LinearGradient primaryGradient = LinearGradient(
    colors: [primary500, primary600],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient accentGradient = LinearGradient(
    colors: [accent500, accent600],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient mixedGradient = LinearGradient(
    colors: [primary500, accent500],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  // Shadow Colors
  static Color shadowSoft = Colors.black.withOpacity(0.07);
  static Color shadowMedium = Colors.black.withOpacity(0.1);
  static Color shadowLarge = Colors.black.withOpacity(0.15);
  static Color shadowPrimary = primary500.withOpacity(0.3);
  static Color shadowAccent = accent500.withOpacity(0.3);

  // ---------------------------------------------------------------------------
  // Legacy/compat aliases (for older widget code still referencing old tokens)
  // ---------------------------------------------------------------------------
  // Text aliases
  static const Color textPrimary = text800;
  static const Color textSecondary = text600;
  static const Color textTertiary = text500;
  static const Color textQuaternary = text400;

  // Background aliases
  static const Color backgroundPrimary = background50;
  static const Color backgroundSecondary = background200;

  // Semantic aliases
  static const Color secondary500 = accent500; // old name → accent
  static const Color warning500 = warning;
  static const Color error500 = error;
  static const Color success500 = success;

  // Surface/border alias
  static const Color surfaceBorder = background300;
}
