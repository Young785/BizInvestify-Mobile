import 'package:flutter/material.dart';
import '../../core/constants/colors.dart';
import '../../core/constants/dimensions.dart';
import 'custom_text_field.dart';

/// Password input field with visibility toggle
class PasswordField extends StatefulWidget {
  const PasswordField({
    super.key,
    required this.label,
    this.hint,
    this.controller,
    this.validator,
    this.onChanged,
    this.onSubmitted,
    this.textInputAction = TextInputAction.done,
    this.enabled = true,
    this.autofocus = false,
  });

  final String label;
  final String? hint;
  final TextEditingController? controller;
  final String? Function(String?)? validator;
  final void Function(String)? onChanged;
  final void Function(String)? onSubmitted;
  final TextInputAction textInputAction;
  final bool enabled;
  final bool autofocus;

  @override
  State<PasswordField> createState() => _PasswordFieldState();
}

class _PasswordFieldState extends State<PasswordField> {
  bool _obscureText = true;

  @override
  Widget build(BuildContext context) {
    return CustomTextField(
      label: widget.label,
      hint: widget.hint ?? 'Enter your password',
      controller: widget.controller,
      keyboardType: TextInputType.visiblePassword,
      textInputAction: widget.textInputAction,
      obscureText: _obscureText,
      enabled: widget.enabled,
      validator: widget.validator,
      onChanged: widget.onChanged,
      onSubmitted: widget.onSubmitted,
      autofocus: widget.autofocus,
      prefixIcon: Icons.lock_outline,
      suffixIcon: IconButton(
        icon: Icon(
          _obscureText ? Icons.visibility_outlined : Icons.visibility_off_outlined,
          color: AppColors.textTertiary,
          size: AppDimensions.iconMedium,
        ),
        onPressed: widget.enabled ? () {
          setState(() {
            _obscureText = !_obscureText;
          });
        } : null,
      ),
    );
  }
}
