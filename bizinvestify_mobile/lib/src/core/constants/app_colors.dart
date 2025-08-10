import 'package:flutter/material.dart';

/// BizInvestify Professional Brand Colors
/// Modern, sophisticated color palette for production-ready mobile app
class AppColors {
  // === BRAND CORE COLORS ===
  
  // Primary Colors - Deep Professional Blue
  static const Color primary50 = Color(0xFFF0F7FF);
  static const Color primary100 = Color(0xFFD6EAFF);
  static const Color primary200 = Color(0xFFB3D9FF);
  static const Color primary300 = Color(0xFF85C2FF);
  static const Color primary400 = Color(0xFF5BA0FF);
  static const Color primary500 = Color(0xFF1A73E8); // Trust Blue - Primary Brand
  static const Color primary600 = Color(0xFF1557B8);
  static const Color primary700 = Color(0xFF0F3F88);
  static const Color primary800 = Color(0xFF0A2958);
  static const Color primary900 = Color(0xFF051428);
  
  // Secondary Colors - Vibrant Success Green
  static const Color secondary50 = Color(0xFFE6FDF4);
  static const Color secondary100 = Color(0xFFBEF9E3);
  static const Color secondary200 = Color(0xFF7DF2C7);
  static const Color secondary300 = Color(0xFF3DE5AB);
  static const Color secondary400 = Color(0xFF10D68E);
  static const Color secondary500 = Color(0xFF00C48C); // Mint Green - Success
  static const Color secondary600 = Color(0xFF00A370);
  static const Color secondary700 = Color(0xFF008054);
  static const Color secondary800 = Color(0xFF005D38);
  static const Color secondary900 = Color(0xFF003A1C);
  
  // Accent Colors - Premium Purple
  static const Color accent50 = Color(0xFFF4F1FF);
  static const Color accent100 = Color(0xFFE5DEFF);
  static const Color accent200 = Color(0xFFCBB8FF);
  static const Color accent300 = Color(0xFFB192FF);
  static const Color accent400 = Color(0xFF976CFF);
  static const Color accent500 = Color(0xFF7C3AED); // Purple - Premium
  static const Color accent600 = Color(0xFF6B21D4);
  static const Color accent700 = Color(0xFF5B1A9B);
  static const Color accent800 = Color(0xFF4B1362);
  static const Color accent900 = Color(0xFF3B0C29);
  
  // === TEXT COLORS ===
  
  // Sophisticated Text Hierarchy
  static const Color textPrimary = Color(0xFF0F172A);    // Slate 900 - Headings
  static const Color textSecondary = Color(0xFF334155);  // Slate 700 - Body
  static const Color textTertiary = Color(0xFF64748B);   // Slate 500 - Captions
  static const Color textQuaternary = Color(0xFF94A3B8); // Slate 400 - Disabled
  static const Color textInverse = Color(0xFFFFFFFF);    // White - On dark
  static const Color textMuted = Color(0xFF6B7280);     // Gray 500 - Muted
  
  // Legacy support (gradually remove)
  static const Color text800 = textPrimary;
  static const Color text700 = textSecondary;
  static const Color text600 = Color(0xFF475569);
  static const Color text500 = textTertiary;
  static const Color text400 = textQuaternary;
  
  // === BACKGROUND COLORS ===
  
  // Modern Background System
  static const Color backgroundPrimary = Color(0xFFFFFFFF);   // Pure white
  static const Color backgroundSecondary = Color(0xFFF8FAFC); // Slate 50
  static const Color backgroundTertiary = Color(0xFFF1F5F9);  // Slate 100
  static const Color backgroundElevated = Color(0xFFFFFFFF);  // Cards, modals
  static const Color backgroundOverlay = Color(0x80000000);   // Modal overlay
  
  // Legacy support
  static const Color background50 = backgroundPrimary;
  static const Color background100 = backgroundSecondary;
  static const Color background200 = backgroundTertiary;
  static const Color background300 = Color(0xFFE2E8F0);
  
  // === STATUS COLORS ===
  
  // Professional Status Palette
  static const Color success50 = Color(0xFFE6FDF4);
  static const Color success500 = Color(0xFF00C48C);
  static const Color success600 = Color(0xFF00A370);
  static const Color success = success500;
  
  static const Color warning50 = Color(0xFFFFFBEB);
  static const Color warning500 = Color(0xFFF59E0B);
  static const Color warning600 = Color(0xFFD97706);
  static const Color warning = warning500;
  
  static const Color error50 = Color(0xFFFEF2F2);
  static const Color error500 = Color(0xFFEF4444);
  static const Color error600 = Color(0xFFDC2626);
  static const Color error = error500;
  
  static const Color info50 = Color(0xFFEFF6FF);
  static const Color info500 = Color(0xFF3B82F6);
  static const Color info600 = Color(0xFF2563EB);
  static const Color info = info500;
  
  // === SURFACE COLORS ===
  
  // Professional Surface System
  static const Color surfacePrimary = Color(0xFFFFFFFF);
  static const Color surfaceSecondary = Color(0xFFF8FAFC);
  static const Color surfaceElevated = Color(0xFFFFFFFF);
  static const Color surfaceBorder = Color(0xFFE2E8F0);
  static const Color surfaceDivider = Color(0xFFF1F5F9);
  
  // === INTERACTIVE COLORS ===
  
  // Modern Interactive States
  static const Color interactivePrimary = primary500;
  static const Color interactiveHover = primary600;
  static const Color interactivePressed = primary700;
  static const Color interactiveDisabled = Color(0xFFE2E8F0);
  static const Color interactiveFocus = Color(0xFF3B82F680); // 50% opacity

  
  // === GRADIENT SYSTEM ===
  
  // Brand Gradients
  static const LinearGradient brandPrimary = LinearGradient(
    colors: [primary500, primary600],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
  
  static const LinearGradient brandSecondary = LinearGradient(
    colors: [secondary500, secondary600],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
  
  static const LinearGradient brandAccent = LinearGradient(
    colors: [accent500, accent600],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
  
  // Hero Gradients
  static const LinearGradient heroGradient = LinearGradient(
    colors: [primary500, accent500],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
  
  static const LinearGradient successGradient = LinearGradient(
    colors: [secondary400, secondary600],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
  
  static const LinearGradient premiumGradient = LinearGradient(
    colors: [accent400, accent700],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );
  
  // Surface Gradients
  static const LinearGradient cardGradient = LinearGradient(
    colors: [Color(0xFFFFFFFF), Color(0xFFF8FAFC)],
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
  );
  
  // Legacy support
  static const LinearGradient primaryGradient = brandPrimary;
  static const LinearGradient accentGradient = brandSecondary;
  static const LinearGradient mixedGradient = heroGradient;
  
  // === SHADOW SYSTEM ===
  
  // Modern Shadow Palette
  static Color shadowXs = const Color(0xFF0F172A).withOpacity(0.05);
  static Color shadowSm = const Color(0xFF0F172A).withOpacity(0.08);
  static Color shadowMd = const Color(0xFF0F172A).withOpacity(0.12);
  static Color shadowLg = const Color(0xFF0F172A).withOpacity(0.16);
  static Color shadowXl = const Color(0xFF0F172A).withOpacity(0.24);
  
  // Brand Shadows
  static Color shadowPrimary = primary500.withOpacity(0.25);
  static Color shadowSecondary = secondary500.withOpacity(0.25);
  static Color shadowAccent = accent500.withOpacity(0.25);
  
  // Legacy support
  static Color shadowSoft = shadowSm;
  static Color shadowMedium = shadowMd;
  static Color shadowLarge = shadowLg;
  
  // === UTILITY COLORS ===
  
  // Modern Utility Palette
  static const Color overlay = Color(0x80000000);
  static const Color backdrop = Color(0xCC000000);
  static const Color transparent = Color(0x00000000);
  
  // Brand Utilities
  static const Color brandOverlay = Color(0x1A1A73E8);
  static const Color successOverlay = Color(0x1A00C48C);
  static const Color warningOverlay = Color(0x1AF59E0B);
  static const Color errorOverlay = Color(0x1AEF4444);
  
  // Investment Categories
  static const Color techBlue = Color(0xFF2563EB);
  static const Color financeGreen = Color(0xFF059669);
  static const Color realEstateOrange = Color(0xFFEA580C);
  static const Color healthcarePurple = Color(0xFF7C3AED);
  static const Color retailPink = Color(0xFFEC4899);
  static const Color energyYellow = Color(0xFFF59E0B);
}
