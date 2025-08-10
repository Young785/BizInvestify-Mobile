import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/constants/app_dimensions.dart';

/// Custom Text Field Widget - Modern and Professional
/// A sophisticated text field component with smooth animations and modern styling
class CustomTextField extends StatefulWidget {
  const CustomTextField({
    super.key,
    this.controller,
    this.labelText,
    this.hintText,
    this.helperText,
    this.errorText,
    this.prefixIcon,
    this.suffixIcon,
    this.prefixText,
    this.suffixText,
    this.onChanged,
    this.onSubmitted,
    this.onTap,
    this.validator,
    this.inputFormatters,
    this.keyboardType,
    this.textInputAction,
    this.textCapitalization = TextCapitalization.none,
    this.obscureText = false,
    this.enabled = true,
    this.readOnly = false,
    this.autofocus = false,
    this.maxLines = 1,
    this.minLines,
    this.maxLength,
    this.expands = false,
    this.focusNode,
    this.textAlign = TextAlign.start,
    this.style,
    this.decoration,
    this.fillColor,
    this.borderColor,
    this.focusedBorderColor,
    this.borderRadius,
    this.contentPadding,
    this.floatingLabel = true,
    this.showPasswordToggle = false,
    this.fieldStyle = CustomTextFieldStyle.outlined,
    // Legacy compatibility
    this.label,
    this.hint,
  });

  final TextEditingController? controller;
  final String? labelText;
  final String? hintText;
  final String? helperText;
  final String? errorText;
  final Widget? prefixIcon;
  final Widget? suffixIcon;
  final String? prefixText;
  final String? suffixText;
  final ValueChanged<String>? onChanged;
  final ValueChanged<String>? onSubmitted;
  final GestureTapCallback? onTap;
  final FormFieldValidator<String>? validator;
  final List<TextInputFormatter>? inputFormatters;
  final TextInputType? keyboardType;
  final TextInputAction? textInputAction;
  final TextCapitalization textCapitalization;
  final bool obscureText;
  final bool enabled;
  final bool readOnly;
  final bool autofocus;
  final int? maxLines;
  final int? minLines;
  final int? maxLength;
  final bool expands;
  final FocusNode? focusNode;
  final TextAlign textAlign;
  final TextStyle? style;
  final InputDecoration? decoration;
  final Color? fillColor;
  final Color? borderColor;
  final Color? focusedBorderColor;
  final double? borderRadius;
  final EdgeInsetsGeometry? contentPadding;
  final bool floatingLabel;
  final bool showPasswordToggle;
  final CustomTextFieldStyle fieldStyle;
  
  // Legacy compatibility
  final String? label;
  final String? hint;

  @override
  State<CustomTextField> createState() => _CustomTextFieldState();
}

class _CustomTextFieldState extends State<CustomTextField>
    with SingleTickerProviderStateMixin {
  late FocusNode _focusNode;
  late AnimationController _animationController;
  late Animation<double> _borderAnimation;
  late Animation<Color?> _colorAnimation;
  
  bool _isFocused = false;
  bool _hasContent = false;
  bool _obscureText = false;

  @override
  void initState() {
    super.initState();
    _focusNode = widget.focusNode ?? FocusNode();
    _focusNode.addListener(_onFocusChange);
    _obscureText = widget.obscureText;
    
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 200),
      vsync: this,
    );
    
    _borderAnimation = Tween<double>(
      begin: 1.0,
      end: 2.0,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeInOut,
    ));
    
    _colorAnimation = ColorTween(
      begin: AppColors.borderPrimary,
      end: AppColors.borderFocus,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeInOut,
    ));
    
    // Check initial content
    if (widget.controller != null) {
      _hasContent = widget.controller!.text.isNotEmpty;
      widget.controller!.addListener(_onTextChange);
    }
  }

  @override
  void dispose() {
    _animationController.dispose();
    if (widget.focusNode == null) {
      _focusNode.dispose();
    }
    if (widget.controller != null) {
      widget.controller!.removeListener(_onTextChange);
    }
    super.dispose();
  }

  void _onFocusChange() {
    setState(() {
      _isFocused = _focusNode.hasFocus;
    });
    
    if (_isFocused) {
      _animationController.forward();
    } else {
      _animationController.reverse();
    }
  }

  void _onTextChange() {
    final hasContent = widget.controller!.text.isNotEmpty;
    if (hasContent != _hasContent) {
      setState(() {
        _hasContent = hasContent;
      });
    }
  }

  void _toggleObscureText() {
    setState(() {
      _obscureText = !_obscureText;
    });
  }

  @override
  Widget build(BuildContext context) {
    // Legacy compatibility
    final effectiveLabelText = widget.labelText ?? widget.label;
    final effectiveHintText = widget.hintText ?? widget.hint;
    
    final styleConfig = _getStyleConfig();
    
    Widget? effectiveSuffixIcon = widget.suffixIcon;
    if (widget.showPasswordToggle && widget.obscureText) {
      effectiveSuffixIcon = IconButton(
        icon: Icon(
          _obscureText ? Icons.visibility_outlined : Icons.visibility_off_outlined,
          color: _isFocused ? AppColors.borderFocus : AppColors.textTertiary,
          size: AppDimensions.iconSizeMedium,
        ),
        onPressed: _toggleObscureText,
      );
    }

    return AnimatedBuilder(
      animation: _animationController,
      builder: (context, child) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Label (for non-floating labels)
            if (effectiveLabelText != null && !widget.floatingLabel) ...[
              Padding(
                padding: const EdgeInsets.only(bottom: AppDimensions.spacing8),
                child: Text(
                  effectiveLabelText,
                  style: AppTypography.inputLabel.copyWith(
                    color: widget.errorText != null
                        ? AppColors.error
                        : AppColors.textSecondary,
                  ),
                ),
              ),
            ],
            
            // Text Field Container
            Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(styleConfig.borderRadius),
                boxShadow: _isFocused && widget.fieldStyle == CustomTextFieldStyle.elevated
                    ? [
                        BoxShadow(
                          color: AppColors.shadowPrimary,
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ]
                    : [],
              ),
              child: TextFormField(
                controller: widget.controller,
                focusNode: _focusNode,
                onChanged: widget.onChanged,
                onFieldSubmitted: widget.onSubmitted,
                onTap: widget.onTap,
                validator: widget.validator,
                inputFormatters: widget.inputFormatters,
                keyboardType: widget.keyboardType,
                textInputAction: widget.textInputAction,
                textCapitalization: widget.textCapitalization,
                obscureText: _obscureText,
                enabled: widget.enabled,
                readOnly: widget.readOnly,
                autofocus: widget.autofocus,
                maxLines: widget.maxLines,
                minLines: widget.minLines,
                maxLength: widget.maxLength,
                expands: widget.expands,
                textAlign: widget.textAlign,
                style: widget.style ?? AppTypography.bodyLarge.copyWith(
                  color: widget.enabled ? AppColors.textPrimary : AppColors.textMuted,
                ),
                decoration: widget.decoration ?? InputDecoration(
                  labelText: widget.floatingLabel ? effectiveLabelText : null,
                  hintText: effectiveHintText,
                  prefixIcon: widget.prefixIcon != null
                      ? Container(
                          margin: const EdgeInsets.only(
                            left: AppDimensions.spacing4,
                            right: AppDimensions.spacing8,
                          ),
                          child: widget.prefixIcon,
                        )
                      : null,
                  suffixIcon: effectiveSuffixIcon != null
                      ? Container(
                          margin: const EdgeInsets.only(
                            left: AppDimensions.spacing8,
                            right: AppDimensions.spacing4,
                          ),
                          child: effectiveSuffixIcon,
                        )
                      : null,
                  prefixText: widget.prefixText,
                  suffixText: widget.suffixText,
                  filled: true,
                  fillColor: styleConfig.fillColor,
                  contentPadding: styleConfig.contentPadding,
                  
                  // Border styles
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(styleConfig.borderRadius),
                    borderSide: BorderSide(
                      color: styleConfig.borderColor,
                      width: 1,
                    ),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(styleConfig.borderRadius),
                    borderSide: BorderSide(
                      color: widget.errorText != null
                          ? AppColors.error
                          : styleConfig.borderColor,
                      width: 1,
                    ),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(styleConfig.borderRadius),
                    borderSide: BorderSide(
                      color: widget.errorText != null
                          ? AppColors.error
                          : (_colorAnimation.value ?? AppColors.borderFocus),
                      width: _borderAnimation.value,
                    ),
                  ),
                  errorBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(styleConfig.borderRadius),
                    borderSide: const BorderSide(
                      color: AppColors.error,
                      width: 1,
                    ),
                  ),
                  focusedErrorBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(styleConfig.borderRadius),
                    borderSide: BorderSide(
                      color: AppColors.error,
                      width: _borderAnimation.value,
                    ),
                  ),
                  disabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(styleConfig.borderRadius),
                    borderSide: const BorderSide(
                      color: AppColors.neutral300,
                      width: 1,
                    ),
                  ),
                  
                  // Text styles
                  labelStyle: AppTypography.inputLabel.copyWith(
                    color: _isFocused
                        ? AppColors.borderFocus
                        : AppColors.textTertiary,
                  ),
                  floatingLabelStyle: AppTypography.inputLabel.copyWith(
                    color: widget.errorText != null
                        ? AppColors.error
                        : AppColors.borderFocus,
                  ),
                  hintStyle: AppTypography.inputHint,
                  prefixStyle: AppTypography.bodyMedium.copyWith(
                    color: AppColors.textSecondary,
                  ),
                  suffixStyle: AppTypography.bodyMedium.copyWith(
                    color: AppColors.textSecondary,
                  ),
                ),
              ),
            ),
            
            // Helper/Error Text
            if (widget.helperText != null || widget.errorText != null) ...[
              const SizedBox(height: AppDimensions.spacing8),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: AppDimensions.spacing4),
                child: Text(
                  widget.errorText ?? widget.helperText!,
                  style: widget.errorText != null
                      ? AppTypography.errorText
                      : AppTypography.bodySmall.copyWith(
                          color: AppColors.textTertiary,
                        ),
                ),
              ),
            ],
          ],
        );
      },
    );
  }

  _TextFieldStyleConfig _getStyleConfig() {
    final effectiveBorderRadius = widget.borderRadius ?? AppDimensions.borderRadiusMedium;
    final effectiveContentPadding = widget.contentPadding ?? const EdgeInsets.symmetric(
      horizontal: AppDimensions.spacing20,
      vertical: AppDimensions.spacing16,
    );

    switch (widget.fieldStyle) {
      case CustomTextFieldStyle.outlined:
        return _TextFieldStyleConfig(
          fillColor: widget.fillColor ?? AppColors.backgroundSecondary,
          borderColor: widget.borderColor ?? AppColors.borderPrimary,
          borderRadius: effectiveBorderRadius,
          contentPadding: effectiveContentPadding,
        );

      case CustomTextFieldStyle.filled:
        return _TextFieldStyleConfig(
          fillColor: widget.fillColor ?? AppColors.backgroundTertiary,
          borderColor: Colors.transparent,
          borderRadius: effectiveBorderRadius,
          contentPadding: effectiveContentPadding,
        );

      case CustomTextFieldStyle.elevated:
        return _TextFieldStyleConfig(
          fillColor: widget.fillColor ?? AppColors.backgroundSecondary,
          borderColor: widget.borderColor ?? AppColors.borderPrimary,
          borderRadius: effectiveBorderRadius,
          contentPadding: effectiveContentPadding,
        );
    }
  }
}

class _TextFieldStyleConfig {
  final Color fillColor;
  final Color borderColor;
  final double borderRadius;
  final EdgeInsetsGeometry contentPadding;

  _TextFieldStyleConfig({
    required this.fillColor,
    required this.borderColor,
    required this.borderRadius,
    required this.contentPadding,
  });
}

enum CustomTextFieldStyle {
  outlined,
  filled,
  elevated,
}