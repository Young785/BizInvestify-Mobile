import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants/colors.dart';
import '../../../core/constants/dimensions.dart';
import '../../../core/constants/typography.dart';
// Removed unused import: routes
import '../../../components/buttons/primary_button.dart';
import '../../../core/utils/toast.dart';
import '../../../core/utils/feedback.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _codeController = TextEditingController();
  bool _isLoading = false;

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
        title: Text('Forgot password', style: AppTypography.headlineSmall),
      ),
      body: Padding(
        padding: const EdgeInsets.all(AppDimensions.spacing24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Type a code', style: AppTypography.bodyLarge),
            const SizedBox(height: AppDimensions.spacing16),
            TextField(
              controller: _codeController,
              keyboardType: TextInputType.number,
              maxLength: 6,
              decoration: const InputDecoration(
                hintText: 'Code',
                counterText: '',
              ),
            ),
            const SizedBox(height: AppDimensions.spacing16),
            PrimaryButton(
              text: 'Resend',
              onPressed: () {
                AppToast.info('A new code has been sent');
                Haptics.light();
              },
              width: double.infinity,
            ),
            const SizedBox(height: AppDimensions.spacing24),
            Text(
              'We texted you a code to verify your phone number (+84) 0398829xxx',
              style: AppTypography.bodyMedium.copyWith(color: AppColors.textSecondary),
            ),
            const SizedBox(height: AppDimensions.spacing16),
            Text(
              'This code will expired 10 minutes after this message. If you don\'t get a message.',
              style: AppTypography.bodyMedium.copyWith(color: AppColors.textSecondary),
            ),
            const Spacer(),
            TextButton(
              onPressed: () {
                AppToast.info('Phone number change flow coming soon');
              },
              child: Text(
                'Change your phone number',
                style: AppTypography.bodyMedium.copyWith(color: AppColors.primaryPurple),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
