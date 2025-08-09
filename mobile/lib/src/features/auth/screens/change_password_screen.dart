import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants/colors.dart';
import '../../../core/constants/dimensions.dart';
import '../../../core/constants/typography.dart';
import '../../../core/constants/routes.dart';
import '../../../components/buttons/primary_button.dart';
import '../../../components/inputs/password_field.dart';
import '../../../core/utils/toast.dart';
import '../../../core/utils/feedback.dart';

class ChangePasswordScreen extends StatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  State<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  // Removed unused field

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundLight,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, color: AppColors.textPrimary),
          onPressed: () => context.pop(),
        ),
        title: Text('Change password', style: AppTypography.headlineSmall),
      ),
      body: Padding(
        padding: const EdgeInsets.all(AppDimensions.spacing24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Type your new password', style: AppTypography.bodyLarge),
            const SizedBox(height: AppDimensions.spacing24),
            PasswordField(
              label: 'New Password',
              controller: _newPasswordController,
            ),
            const SizedBox(height: AppDimensions.spacing20),
            PasswordField(
              label: 'Confirm password',
              controller: _confirmPasswordController,
            ),
            const SizedBox(height: AppDimensions.spacing32),
            PrimaryButton(
              text: 'Change password',
              onPressed: () {
                if (_newPasswordController.text.isEmpty ||
                    _confirmPasswordController.text.isEmpty) {
                  AppToast.error('Please fill in both password fields');
                  Haptics.error();
                  return;
                }
                if (_newPasswordController.text != _confirmPasswordController.text) {
                  AppToast.error('Passwords do not match');
                  Haptics.error();
                  return;
                }
                AppToast.success('Password changed');
                Haptics.success();
                context.go(AppRoutes.login);
              },
              width: double.infinity,
              size: ButtonSize.large,
            ),
          ],
        ),
      ),
    );
  }
}
