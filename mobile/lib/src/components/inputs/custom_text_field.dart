import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../core/constants/colors.dart';
import '../../core/constants/dimensions.dart';
import '../../core/constants/typography.dart';

/// Custom text field component with consistent styling
class CustomTextField extends StatefulWidget {
  const CustomTextField({
    super.key,
    required this.label,
    this.hint,
    this.initialValue,
    this.controller,
    this.keyboardType = TextInputType.text,
    this.textInputAction = TextInputAction.next,
    this.obscureText = false,
    this.enabled = true,
    this.readOnly = false,
    this.maxLines = 1,
    this.maxLength,
    this.prefixIcon,
    this.suffixIcon,
    this.validator,
    this.onChanged,
    this.onSubmitted,
    this.inputFormatters,
    this.autofocus = false,
  });

  final String label;
  final String? hint;
  final String? initialValue;
  final TextEditingController? controller;
  final TextInputType keyboardType;
  final TextInputAction textInputAction;
  final bool obscureText;
  final bool enabled;
  final bool readOnly;
  final int maxLines;
  final int? maxLength;
  final IconData? prefixIcon;
  final Widget? suffixIcon;
  final String? Function(String?)? validator;
  final void Function(String)? onChanged;
  final void Function(String)? onSubmitted;
  final List<TextInputFormatter>? inputFormatters;
  final bool autofocus;

  @override
  State<CustomTextField> createState() => _CustomTextFieldState();
}

class _CustomTextFieldState extends State<CustomTextField> {
  late final TextEditingController _controller;
  bool _isFocused = false;

  @override
  void initState() {
    super.initState();
    _controller = widget.controller ?? TextEditingController();
    if (widget.initialValue != null) {
      _controller.text = widget.initialValue!;
    }
  }

  @override
  void dispose() {
    if (widget.controller == null) {
      _controller.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          widget.label,
          style: AppTypography.labelLarge.copyWith(
            color: widget.enabled ? AppColors.textPrimary : AppColors.textDisabled,
          ),
        ),
        const SizedBox(height: AppDimensions.spacing8),
        Focus(
          onFocusChange: (hasFocus) {
            setState(() {
              _isFocused = hasFocus;
            });
          },
          child: TextFormField(
            controller: _controller,
            keyboardType: widget.keyboardType,
            textInputAction: widget.textInputAction,
            obscureText: widget.obscureText,
            enabled: widget.enabled,
            readOnly: widget.readOnly,
            maxLines: widget.maxLines,
            maxLength: widget.maxLength,
            validator: widget.validator,
            onChanged: widget.onChanged,
            onFieldSubmitted: widget.onSubmitted,
            inputFormatters: widget.inputFormatters,
            autofocus: widget.autofocus,
            style: AppTypography.bodyLarge.copyWith(
              color: widget.enabled ? AppColors.textPrimary : AppColors.textDisabled,
            ),
            decoration: InputDecoration(
              hintText: widget.hint,
              hintStyle: AppTypography.bodyLarge.copyWith(
                color: AppColors.textTertiary,
              ),
              prefixIcon: widget.prefixIcon != null
                  ? Icon(
                      widget.prefixIcon,
                      color: _isFocused 
                          ? AppColors.primaryPurple 
                          : AppColors.textTertiary,
                      size: AppDimensions.iconMedium,
                    )
                  : null,
              suffixIcon: widget.suffixIcon,
              filled: true,
              fillColor: widget.enabled 
                  ? AppColors.cardWhite 
                  : AppColors.backgroundGray,
              contentPadding: const EdgeInsets.symmetric(
                horizontal: AppDimensions.spacing16,
                vertical: AppDimensions.spacing12,
              ),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
                borderSide: const BorderSide(
                  color: AppColors.borderGray,
                  width: 1.0,
                ),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
                borderSide: const BorderSide(
                  color: AppColors.borderGray,
                  width: 1.0,
                ),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
                borderSide: const BorderSide(
                  color: AppColors.primaryPurple,
                  width: 2.0,
                ),
              ),
              errorBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
                borderSide: const BorderSide(
                  color: AppColors.errorRed,
                  width: 1.0,
                ),
              ),
              focusedErrorBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
                borderSide: const BorderSide(
                  color: AppColors.errorRed,
                  width: 2.0,
                ),
              ),
              disabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
                borderSide: const BorderSide(
                  color: AppColors.borderLight,
                  width: 1.0,
                ),
              ),
              counterStyle: AppTypography.labelSmall,
              errorStyle: AppTypography.bodySmall.copyWith(
                color: AppColors.errorRed,
              ),
            ),
          ),
        ),
      ],
    );
  }
}
