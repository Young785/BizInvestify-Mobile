import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';

/// Professional Custom Text Field for BizInvestify
/// Follows Material 3 design guidelines with brand styling
class CustomTextField extends StatefulWidget {
  final String? labelText;
  final String? hintText;
  final String? errorText;
  final TextEditingController? controller;
  final bool obscureText;
  final TextInputType keyboardType;
  final Widget? prefixIcon;
  final Widget? suffixIcon;
  final Function(String)? onChanged;
  final String? Function(String?)? validator;
  final bool enabled;
  final int? maxLines;
  final int? minLines;
  final FocusNode? focusNode;
  final TextInputAction? textInputAction;
  final Function(String)? onSubmitted;
  final bool autofocus;
  final String? helperText;
  final EdgeInsetsGeometry? contentPadding;

  const CustomTextField({
    super.key,
    this.labelText,
    this.hintText,
    this.errorText,
    this.controller,
    this.obscureText = false,
    this.keyboardType = TextInputType.text,
    this.prefixIcon,
    this.suffixIcon,
    this.onChanged,
    this.validator,
    this.enabled = true,
    this.maxLines = 1,
    this.minLines,
    this.focusNode,
    this.textInputAction,
    this.onSubmitted,
    this.autofocus = false,
    this.helperText,
    this.contentPadding,
  });

  @override
  State<CustomTextField> createState() => _CustomTextFieldState();
}

class _CustomTextFieldState extends State<CustomTextField> {
  late bool _obscureText;
  late FocusNode _focusNode;

  @override
  void initState() {
    super.initState();
    _obscureText = widget.obscureText;
    _focusNode = widget.focusNode ?? FocusNode();
  }

  @override
  void dispose() {
    if (widget.focusNode == null) {
      _focusNode.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (widget.labelText != null) ...[
          Text(
            widget.labelText!,
            style: AppTypography.labelMedium.copyWith(
              color: AppColors.textSecondary,
              fontWeight: AppTypography.medium,
            ),
          ),
          const SizedBox(height: 8.0),
        ],
        
        TextFormField(
          controller: widget.controller,
          obscureText: _obscureText,
          keyboardType: widget.keyboardType,
          onChanged: widget.onChanged,
          validator: widget.validator,
          enabled: widget.enabled,
          maxLines: widget.maxLines,
          minLines: widget.minLines,
          focusNode: _focusNode,
          textInputAction: widget.textInputAction,
          onFieldSubmitted: widget.onSubmitted,
          autofocus: widget.autofocus,
          
          style: AppTypography.bodyMedium.copyWith(
            color: widget.enabled 
                ? AppColors.textPrimary 
                : AppColors.textQuaternary,
          ),
          
          decoration: InputDecoration(
            hintText: widget.hintText,
            errorText: widget.errorText,
            helperText: widget.helperText,
            
            filled: true,
            fillColor: widget.enabled 
                ? AppColors.backgroundSecondary 
                : AppColors.surfaceDivider,
            
            prefixIcon: widget.prefixIcon,
            suffixIcon: widget.obscureText 
                ? IconButton(
                    icon: Icon(
                      _obscureText ? Icons.visibility_off : Icons.visibility,
                      color: AppColors.textTertiary,
                    ),
                    onPressed: () {
                      setState(() {
                        _obscureText = !_obscureText;
                      });
                    },
                  )
                : widget.suffixIcon,
            
            // Border styles
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12.0),
              borderSide: BorderSide(
                color: AppColors.surfaceBorder,
                width: 1.0,
              ),
            ),
            
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12.0),
              borderSide: BorderSide(
                color: AppColors.surfaceBorder,
                width: 1.0,
              ),
            ),
            
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12.0),
              borderSide: const BorderSide(
                color: AppColors.primary500,
                width: 2.0,
              ),
            ),
            
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12.0),
              borderSide: const BorderSide(
                color: AppColors.error500,
                width: 1.0,
              ),
            ),
            
            focusedErrorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12.0),
              borderSide: const BorderSide(
                color: AppColors.error500,
                width: 2.0,
              ),
            ),
            
            disabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12.0),
              borderSide: BorderSide(
                color: AppColors.surfaceDivider,
                width: 1.0,
              ),
            ),
            
            contentPadding: widget.contentPadding ?? const EdgeInsets.symmetric(
              horizontal: 16.0,
              vertical: 16.0,
            ),
            
            // Text styles
            hintStyle: AppTypography.bodyMedium.copyWith(
              color: AppColors.textQuaternary,
            ),
            
            errorStyle: AppTypography.captionMedium.copyWith(
              color: AppColors.error500,
            ),
            
            helperStyle: AppTypography.captionMedium.copyWith(
              color: AppColors.textTertiary,
            ),
            
            // Icon styling
            prefixIconColor: AppColors.textTertiary,
            suffixIconColor: AppColors.textTertiary,
          ),
        ),
      ],
    );
  }
}

/// Specialized Email Input Field
class EmailTextField extends StatelessWidget {
  final String? labelText;
  final String? hintText;
  final TextEditingController? controller;
  final Function(String)? onChanged;
  final String? Function(String?)? validator;
  final FocusNode? focusNode;
  final TextInputAction? textInputAction;
  final Function(String)? onSubmitted;

  const EmailTextField({
    super.key,
    this.labelText,
    this.hintText = 'Enter your email address',
    this.controller,
    this.onChanged,
    this.validator,
    this.focusNode,
    this.textInputAction,
    this.onSubmitted,
  });

  @override
  Widget build(BuildContext context) {
    return CustomTextField(
      labelText: labelText ?? 'Email Address',
      hintText: hintText,
      controller: controller,
      keyboardType: TextInputType.emailAddress,
      prefixIcon: const Icon(Icons.email_outlined),
      onChanged: onChanged,
      validator: validator ?? _defaultEmailValidator,
      focusNode: focusNode,
      textInputAction: textInputAction ?? TextInputAction.next,
      onSubmitted: onSubmitted,
    );
  }

  String? _defaultEmailValidator(String? value) {
    if (value == null || value.isEmpty) {
      return 'Email address is required';
    }
    
    if (!RegExp(r'^[^@]+@[^@]+\.[^@]+').hasMatch(value)) {
      return 'Please enter a valid email address';
    }
    
    return null;
  }
}

/// Specialized Password Input Field
class PasswordTextField extends StatelessWidget {
  final String? labelText;
  final String? hintText;
  final TextEditingController? controller;
  final Function(String)? onChanged;
  final String? Function(String?)? validator;
  final FocusNode? focusNode;
  final TextInputAction? textInputAction;
  final Function(String)? onSubmitted;

  const PasswordTextField({
    super.key,
    this.labelText,
    this.hintText = 'Enter your password',
    this.controller,
    this.onChanged,
    this.validator,
    this.focusNode,
    this.textInputAction,
    this.onSubmitted,
  });

  @override
  Widget build(BuildContext context) {
    return CustomTextField(
      labelText: labelText ?? 'Password',
      hintText: hintText,
      controller: controller,
      obscureText: true,
      prefixIcon: const Icon(Icons.lock_outline),
      onChanged: onChanged,
      validator: validator ?? _defaultPasswordValidator,
      focusNode: focusNode,
      textInputAction: textInputAction ?? TextInputAction.done,
      onSubmitted: onSubmitted,
    );
  }

  String? _defaultPasswordValidator(String? value) {
    if (value == null || value.isEmpty) {
      return 'Password is required';
    }
    
    if (value.length < 8) {
      return 'Password must be at least 8 characters long';
    }
    
    return null;
  }
}