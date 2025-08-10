import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_dimensions.dart';
import '../../../core/constants/app_typography.dart';

/// Primary Button Widget - Modern and Professional
/// A sophisticated button component with smooth animations and modern styling
class PrimaryButton extends StatefulWidget {
  const PrimaryButton({
    super.key,
    this.text,
    required this.onPressed,
    this.isLoading = false,
    this.isEnabled = true,
    this.width,
    this.height,
    this.backgroundColor,
    this.textColor,
    this.borderRadius,
    this.fontSize,
    this.fontWeight,
    this.prefix,
    this.suffix,
    this.padding,
    this.borderColor,
    this.elevation,
    this.gradient,
    this.style = PrimaryButtonStyle.filled,
    this.size = PrimaryButtonSize.medium,
    this.hapticFeedback = true,
    this.isFullWidth = false,
    this.child,
  });

  final String? text;
  final VoidCallback? onPressed;
  final bool isLoading;
  final bool isEnabled;
  final double? width;
  final double? height;
  final Color? backgroundColor;
  final Color? textColor;
  final double? borderRadius;
  final double? fontSize;
  final FontWeight? fontWeight;
  final Widget? prefix;
  final Widget? suffix;
  final EdgeInsetsGeometry? padding;
  final Color? borderColor;
  final double? elevation;
  final Gradient? gradient;
  final PrimaryButtonStyle style;
  final PrimaryButtonSize size;
  final bool hapticFeedback;
  final bool isFullWidth;
  final Widget? child;

  @override
  State<PrimaryButton> createState() => _PrimaryButtonState();
}

class _PrimaryButtonState extends State<PrimaryButton>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _scaleAnimation;
  bool _isPressed = false;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(milliseconds: 150),
      vsync: this,
    );
    _scaleAnimation = Tween<double>(
      begin: 1.0,
      end: 0.96,
    ).animate(CurvedAnimation(
      parent: _controller,
      curve: Curves.easeInOut,
    ));
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _handleTapDown(TapDownDetails details) {
    if (widget.isEnabled && !widget.isLoading) {
      setState(() => _isPressed = true);
      _controller.forward();
      if (widget.hapticFeedback) {
        HapticFeedback.lightImpact();
      }
    }
  }

  void _handleTapUp(TapUpDetails details) {
    _handleTapEnd();
  }

  void _handleTapCancel() {
    _handleTapEnd();
  }

  void _handleTapEnd() {
    if (mounted) {
      setState(() => _isPressed = false);
      _controller.reverse();
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDisabled = !widget.isEnabled || widget.onPressed == null;
    
    // Size configuration
    final sizeConfig = _getSizeConfig();
    final effectiveHeight = widget.height ?? sizeConfig.height;
    final effectivePadding = widget.padding ?? sizeConfig.padding;
    final effectiveBorderRadius = widget.borderRadius ?? sizeConfig.borderRadius;
    final textStyle = sizeConfig.textStyle;

    // Style configuration
    final styleConfig = _getStyleConfig(isDisabled);

    final Widget content = widget.child ?? (widget.text != null
        ? Text(
            widget.text!,
            style: textStyle.copyWith(
              color: styleConfig.textColor,
              fontSize: widget.fontSize,
              fontWeight: widget.fontWeight,
            ),
          )
        : const SizedBox.shrink());

    Widget buttonChild = Row(
      mainAxisSize: MainAxisSize.min,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        if (widget.prefix != null) ...[
          widget.prefix!,
          const SizedBox(width: AppDimensions.spacing8),
        ],
        if (widget.isLoading)
          SizedBox(
            width: sizeConfig.loadingSize,
            height: sizeConfig.loadingSize,
            child: CircularProgressIndicator(
              strokeWidth: 2,
              valueColor: AlwaysStoppedAnimation<Color>(styleConfig.textColor),
            ),
          )
        else
          content,
        if (widget.suffix != null) ...[
          const SizedBox(width: AppDimensions.spacing8),
          widget.suffix!,
        ],
      ],
    );

    return AnimatedBuilder(
      animation: _scaleAnimation,
      builder: (context, child) {
        return Transform.scale(
          scale: _scaleAnimation.value,
          child: GestureDetector(
            onTapDown: _handleTapDown,
            onTapUp: _handleTapUp,
            onTapCancel: _handleTapCancel,
            onTap: isDisabled || widget.isLoading ? null : widget.onPressed,
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              curve: Curves.easeInOut,
              width: widget.isFullWidth ? double.infinity : widget.width,
              height: effectiveHeight,
              decoration: BoxDecoration(
                gradient: widget.gradient ?? styleConfig.gradient,
                color: widget.gradient == null ? styleConfig.backgroundColor : null,
                borderRadius: BorderRadius.circular(effectiveBorderRadius),
                border: styleConfig.border,
                boxShadow: _isPressed ? [] : styleConfig.shadows,
              ),
              child: Material(
                color: Colors.transparent,
                child: Container(
                  padding: effectivePadding,
                  child: buttonChild,
                ),
              ),
            ),
          ),
        );
      },
    );
  }

  _ButtonSizeConfig _getSizeConfig() {
    switch (widget.size) {
      case PrimaryButtonSize.small:
        return _ButtonSizeConfig(
          height: AppDimensions.buttonHeightSmall,
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.buttonPaddingSmallHorizontal,
            vertical: AppDimensions.buttonPaddingSmallVertical,
          ),
          borderRadius: AppDimensions.borderRadiusSmall,
          textStyle: AppTypography.buttonSmall,
          loadingSize: 14,
        );
      case PrimaryButtonSize.medium:
        return _ButtonSizeConfig(
          height: AppDimensions.buttonHeightMedium,
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.buttonPaddingHorizontal,
            vertical: AppDimensions.buttonPaddingVertical,
          ),
          borderRadius: AppDimensions.borderRadiusMedium,
          textStyle: AppTypography.buttonMedium,
          loadingSize: 16,
        );
      case PrimaryButtonSize.large:
        return _ButtonSizeConfig(
          height: AppDimensions.buttonHeightLarge,
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.buttonPaddingLargeHorizontal,
            vertical: AppDimensions.buttonPaddingLargeVertical,
          ),
          borderRadius: AppDimensions.borderRadiusMedium,
          textStyle: AppTypography.buttonLarge,
          loadingSize: 18,
        );
    }
  }

  _ButtonStyleConfig _getStyleConfig(bool isDisabled) {
    final baseColor = widget.backgroundColor ?? AppColors.primary500;
    final baseTextColor = widget.textColor ?? AppColors.white;

    switch (widget.style) {
      case PrimaryButtonStyle.filled:
        return _ButtonStyleConfig(
          backgroundColor: isDisabled 
            ? baseColor.withOpacity(0.4)
            : baseColor,
          textColor: isDisabled 
            ? baseTextColor.withOpacity(0.6)
            : baseTextColor,
          shadows: [
            BoxShadow(
              color: AppColors.shadowPrimary,
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        );

      case PrimaryButtonStyle.outlined:
        return _ButtonStyleConfig(
          backgroundColor: Colors.transparent,
          textColor: isDisabled 
            ? baseColor.withOpacity(0.4)
            : baseColor,
          border: Border.all(
            color: isDisabled 
              ? baseColor.withOpacity(0.4)
              : baseColor,
            width: 1.5,
          ),
          shadows: [],
        );

      case PrimaryButtonStyle.ghost:
        return _ButtonStyleConfig(
          backgroundColor: isDisabled 
            ? baseColor.withOpacity(0.05)
            : baseColor.withOpacity(0.1),
          textColor: isDisabled 
            ? baseColor.withOpacity(0.4)
            : baseColor,
          shadows: [],
        );

      case PrimaryButtonStyle.gradient:
        return _ButtonStyleConfig(
          gradient: isDisabled 
            ? null
            : widget.gradient ?? AppColors.primaryGradient,
          backgroundColor: isDisabled 
            ? AppColors.neutral300
            : null,
          textColor: isDisabled 
            ? AppColors.neutral500
            : AppColors.white,
          shadows: [
            BoxShadow(
              color: AppColors.shadowPrimary,
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        );
    }
  }
}

class _ButtonSizeConfig {
  final double height;
  final EdgeInsetsGeometry padding;
  final double borderRadius;
  final TextStyle textStyle;
  final double loadingSize;

  _ButtonSizeConfig({
    required this.height,
    required this.padding,
    required this.borderRadius,
    required this.textStyle,
    required this.loadingSize,
  });
}

class _ButtonStyleConfig {
  final Color? backgroundColor;
  final Color textColor;
  final Border? border;
  final List<BoxShadow> shadows;
  final Gradient? gradient;

  _ButtonStyleConfig({
    this.backgroundColor,
    required this.textColor,
    this.border,
    required this.shadows,
    this.gradient,
  });
}

enum PrimaryButtonStyle {
  filled,
  outlined,
  ghost,
  gradient,
}

enum PrimaryButtonSize {
  small,
  medium,
  large,
}

/// Legacy constructor for backward compatibility
class LegacyPrimaryButton extends StatelessWidget {
  final VoidCallback? onPressed;
  final Widget child;
  final bool isLoading;
  final double? width;
  final double height;
  final Color? backgroundColor;
  final Color? textColor;
  final bool isFullWidth;

  const LegacyPrimaryButton({
    super.key,
    required this.onPressed,
    required this.child,
    this.isLoading = false,
    this.width,
    this.height = AppDimensions.buttonHeightMedium,
    this.backgroundColor,
    this.textColor,
    this.isFullWidth = false,
  });

  @override
  Widget build(BuildContext context) {
    return PrimaryButton(
      onPressed: onPressed,
      isLoading: isLoading,
      width: width,
      height: height,
      backgroundColor: backgroundColor,
      textColor: textColor,
      isFullWidth: isFullWidth,
      child: child,
    );
  }
}