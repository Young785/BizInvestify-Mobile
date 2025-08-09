import 'package:flutter/material.dart';
import '../../core/constants/colors.dart';
import '../animations/pulse_button.dart';
import '../../core/constants/dimensions.dart';
import '../../core/constants/typography.dart';

/// Primary button component with loading and disabled states
class PrimaryButton extends StatelessWidget {
  const PrimaryButton({
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
    
    final buttonChild = FilledButton(
        onPressed: isDisabled ? null : () {
          onPressed?.call();
        },
        style: FilledButton.styleFrom(
          backgroundColor: isDisabled 
              ? AppColors.textDisabled 
              : AppColors.primaryPurple,
          foregroundColor: AppColors.cardWhite,
          elevation: isDisabled ? 0 : AppDimensions.elevationSmall,
          shadowColor: AppColors.primaryPurple.withValues(alpha: 0.25),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
          ),
          padding: _getPadding(),
        ),
        child: isLoading
            ? SizedBox(
                width: _getIconSize(),
                height: _getIconSize(),
                child: const CircularProgressIndicator(
                  strokeWidth: 2.0,
                  valueColor: AlwaysStoppedAnimation<Color>(AppColors.cardWhite),
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
                    style: _getTextStyle(),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
      );

    return SizedBox(
      width: width,
      height: _getHeight(),
      child: isDisabled
          ? buttonChild
          : PulseButton(
              scale: 0.98,
              child: buttonChild,
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

enum ButtonSize { small, medium, large }
