import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'colors.dart';

/// App typography constants using Inter font family
class AppTypography {
  AppTypography._();

  // Font Family
  static const String fontFamily = 'Inter';

  // Font Weights
  static const FontWeight light = FontWeight.w300;
  static const FontWeight regular = FontWeight.w400;
  static const FontWeight medium = FontWeight.w500;
  static const FontWeight semiBold = FontWeight.w600;
  static const FontWeight bold = FontWeight.w700;
  static const FontWeight extraBold = FontWeight.w800;

  // Text Styles
  static TextStyle get displayLarge => GoogleFonts.inter(
        fontSize: 32,
        fontWeight: bold,
        height: 1.25,
        color: AppColors.textPrimary,
      );

  static TextStyle get displayMedium => GoogleFonts.inter(
        fontSize: 28,
        fontWeight: bold,
        height: 1.29,
        color: AppColors.textPrimary,
      );

  static TextStyle get displaySmall => GoogleFonts.inter(
        fontSize: 24,
        fontWeight: bold,
        height: 1.33,
        color: AppColors.textPrimary,
      );

  static TextStyle get headlineLarge => GoogleFonts.inter(
        fontSize: 22,
        fontWeight: semiBold,
        height: 1.36,
        color: AppColors.textPrimary,
      );

  static TextStyle get headlineMedium => GoogleFonts.inter(
        fontSize: 20,
        fontWeight: semiBold,
        height: 1.4,
        color: AppColors.textPrimary,
      );

  static TextStyle get headlineSmall => GoogleFonts.inter(
        fontSize: 18,
        fontWeight: semiBold,
        height: 1.44,
        color: AppColors.textPrimary,
      );

  static TextStyle get titleLarge => GoogleFonts.inter(
        fontSize: 16,
        fontWeight: semiBold,
        height: 1.5,
        color: AppColors.textPrimary,
      );

  static TextStyle get titleMedium => GoogleFonts.inter(
        fontSize: 14,
        fontWeight: semiBold,
        height: 1.43,
        color: AppColors.textPrimary,
      );

  static TextStyle get titleSmall => GoogleFonts.inter(
        fontSize: 12,
        fontWeight: semiBold,
        height: 1.33,
        color: AppColors.textPrimary,
      );

  static TextStyle get bodyLarge => GoogleFonts.inter(
        fontSize: 16,
        fontWeight: regular,
        height: 1.5,
        color: AppColors.textPrimary,
      );

  static TextStyle get bodyMedium => GoogleFonts.inter(
        fontSize: 14,
        fontWeight: regular,
        height: 1.43,
        color: AppColors.textSecondary,
      );

  static TextStyle get bodySmall => GoogleFonts.inter(
        fontSize: 12,
        fontWeight: regular,
        height: 1.33,
        color: AppColors.textSecondary,
      );

  static TextStyle get labelLarge => GoogleFonts.inter(
        fontSize: 14,
        fontWeight: medium,
        height: 1.43,
        color: AppColors.textPrimary,
      );

  static TextStyle get labelMedium => GoogleFonts.inter(
        fontSize: 12,
        fontWeight: medium,
        height: 1.33,
        color: AppColors.textSecondary,
      );

  static TextStyle get labelSmall => GoogleFonts.inter(
        fontSize: 10,
        fontWeight: medium,
        height: 1.2,
        color: AppColors.textTertiary,
      );

  // Button Text Styles
  static TextStyle get buttonLarge => GoogleFonts.inter(
        fontSize: 16,
        fontWeight: semiBold,
        height: 1.5,
        color: AppColors.cardWhite,
      );

  static TextStyle get buttonMedium => GoogleFonts.inter(
        fontSize: 14,
        fontWeight: semiBold,
        height: 1.43,
        color: AppColors.cardWhite,
      );

  static TextStyle get buttonSmall => GoogleFonts.inter(
        fontSize: 12,
        fontWeight: semiBold,
        height: 1.33,
        color: AppColors.cardWhite,
      );

  // Caption Styles
  static TextStyle get caption => GoogleFonts.inter(
        fontSize: 11,
        fontWeight: regular,
        height: 1.27,
        color: AppColors.textTertiary,
      );

  static TextStyle get overline => GoogleFonts.inter(
        fontSize: 10,
        fontWeight: medium,
        height: 1.2,
        letterSpacing: 0.5,
        color: AppColors.textTertiary,
      );
}
