import 'package:flutter/material.dart';
import '../../core/constants/colors.dart';
import '../../core/constants/dimensions.dart';
import '../../core/constants/typography.dart';
import 'primary_button.dart';

/// Secondary button component (outlined style)
class SecondaryButton extends StatelessWidget {
  const SecondaryButton({
    super.key,
    required this.text,
    required this.onPressed,
    this.isLoading = false,
    this.isEnabled = true,
    this.icon,
    this.size = ButtonSize.medium,
    this.width,
  });

  final String text;
  final VoidCallback? onPressed;
  final bool isLoading;
  final bool isEnabled;
  final IconData? icon;
  final ButtonSize size;
  final double? width;

  @override
  Widget build(BuildContext context) {
    final isDisabled = !isEnabled || isLoading || onPressed == null;
    
    return SizedBox(
      width: width,
      height: _getHeight(),
      child: OutlinedButton(
        onPressed: isDisabled ? null : onPressed,
        style: OutlinedButton.styleFrom(
          foregroundColor: isDisabled 
              ? AppColors.textDisabled 
              : AppColors.primaryPurple,
          side: BorderSide(
            color: isDisabled 
                ? AppColors.textDisabled 
                : AppColors.primaryPurple,
            width: 1.5,
          ),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
          ),
          padding: _getPadding(),
        ),
        child: isLoading
            ? SizedBox(
                width: _getIconSize(),
                height: _getIconSize(),
                child: CircularProgressIndicator(
                  strokeWidth: 2.0,
                  valueColor: AlwaysStoppedAnimation<Color>(
                    isDisabled 
                        ? AppColors.textDisabled 
                        : AppColors.primaryPurple,
                  ),
                ),
              )
            : Row(
                mainAxisSize: MainAxisSize.min,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  if (icon != null) ...[
                    Icon(
                      icon,
                      size: _getIconSize(),
                    ),
                    const SizedBox(width: AppDimensions.spacing8),
                  ],
                  Text(
                    text,
                    style: _getTextStyle().copyWith(
                      color: isDisabled 
                          ? AppColors.textDisabled 
                          : AppColors.primaryPurple,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
      ),
    );
  }

  double _getHeight() {
    switch (size) {
      case ButtonSize.small:
        return AppDimensions.buttonSmall;
      case ButtonSize.medium:
        return AppDimensions.buttonMedium;
      case ButtonSize.large:
        return AppDimensions.buttonLarge;
    }
  }

  EdgeInsets _getPadding() {
    switch (size) {
      case ButtonSize.small:
        return const EdgeInsets.symmetric(
          horizontal: AppDimensions.spacing12,
          vertical: AppDimensions.spacing8,
        );
      case ButtonSize.medium:
        return const EdgeInsets.symmetric(
          horizontal: AppDimensions.spacing16,
          vertical: AppDimensions.spacing12,
        );
      case ButtonSize.large:
        return const EdgeInsets.symmetric(
          horizontal: AppDimensions.spacing20,
          vertical: AppDimensions.spacing16,
        );
    }
  }

  TextStyle _getTextStyle() {
    switch (size) {
      case ButtonSize.small:
        return AppTypography.buttonSmall;
      case ButtonSize.medium:
        return AppTypography.buttonMedium;
      case ButtonSize.large:
        return AppTypography.buttonLarge;
    }
  }

  double _getIconSize() {
    switch (size) {
      case ButtonSize.small:
        return AppDimensions.iconSmall;
      case ButtonSize.medium:
        return AppDimensions.iconMedium;
      case ButtonSize.large:
        return AppDimensions.iconLarge;
    }
  }
}
