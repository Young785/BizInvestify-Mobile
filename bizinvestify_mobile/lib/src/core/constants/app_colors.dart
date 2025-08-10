import 'package:flutter/material.dart';

/// BizInvestify Brand Colors
/// Based on the web platform design system
class AppColors {
  // Primary Colors
  static const Color primary50 = Color(0xFFE8F2FF);
  static const Color primary100 = Color(0xFFC7E0FF);
  static const Color primary200 = Color(0xFFA3CCFF);
  static const Color primary300 = Color(0xFF7FB8FF);
  static const Color primary400 = Color(0xFF5BA4FF);
  static const Color primary500 = Color(0xFF1A73E8); // Trust Blue - Main
  static const Color primary600 = Color(0xFF1565C0);
  static const Color primary700 = Color(0xFF0D47A1);
  static const Color primary800 = Color(0xFF0A3D8F);
  static const Color primary900 = Color(0xFF072E6B);

  // Accent Colors
  static const Color accent50 = Color(0xFFE6F7F0);
  static const Color accent100 = Color(0xFFCCEFE1);
  static const Color accent200 = Color(0xFF99DFC3);
  static const Color accent300 = Color(0xFF66CFA5);
  static const Color accent400 = Color(0xFF33BF87);
  static const Color accent500 = Color(0xFF00C48C); // Mint Green - Main
  static const Color accent600 = Color(0xFF009D6F);
  static const Color accent700 = Color(0xFF007652);
  static const Color accent800 = Color(0xFF004F35);
  static const Color accent900 = Color(0xFF002719);

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
  static const Color background200 = Color(0xFFF5F7FA); // Soft Light Grey
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
}
