import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants/colors.dart';
import '../../../core/constants/dimensions.dart';
import '../../../core/constants/typography.dart';
import '../../../core/constants/routes.dart';
import '../../../core/utils/toast.dart';
import '../../../core/utils/feedback.dart';
import '../../../components/buttons/primary_button.dart';
import '../../../components/buttons/secondary_button.dart';
import '../providers/auth_provider.dart';

/// Email verification screen
class VerifyEmailScreen extends ConsumerStatefulWidget {
  final String? email;
  final bool isWelcome;

  const VerifyEmailScreen({
    super.key,
    this.email,
    this.isWelcome = false,
  });

  @override
  ConsumerState<VerifyEmailScreen> createState() => _VerifyEmailScreenState();
}

class _VerifyEmailScreenState extends ConsumerState<VerifyEmailScreen> {
  bool _isResending = false;
  int _resendCooldown = 0;

  @override
  void initState() {
    super.initState();
    _checkEmailVerificationStatus();
  }

  Future<void> _checkEmailVerificationStatus() async {
    if (widget.email != null) {
      // Check if email is already verified
      final authNotifier = ref.read(authProvider.notifier);
      // This would need to be implemented in the auth service
      // For now, we'll assume the user needs to verify
    }
  }

  Future<void> _resendVerification() async {
    if (widget.email == null) return;

    setState(() {
      _isResending = true;
    });

    final authNotifier = ref.read(authProvider.notifier);
    final success = await authNotifier.resendEmailVerification(widget.email!);

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Verification email sent successfully!'),
          backgroundColor: AppColors.successGreen,
        ),
      );

      // Start cooldown timer
      setState(() {
        _resendCooldown = 60;
      });

      _startCooldownTimer();
    }

    setState(() {
      _isResending = false;
    });
  }

  void _startCooldownTimer() {
    Future.delayed(const Duration(seconds: 1), () {
      if (mounted && _resendCooldown > 0) {
        setState(() {
          _resendCooldown--;
        });
        _startCooldownTimer();
      }
    });
  }

  void _navigateToPhoneVerification() {
    context.go(AppRoutes.verifyPhone);
  }

  void _navigateToLogin() {
    context.go(AppRoutes.login);
  }

  @override
  Widget build(BuildContext context) {
    ref.watch(authLoadingProvider);

    // Listen for auth errors and show toast
    ref.listen<String?>(authErrorProvider, (previous, next) {
      if (next != null) {
        AppToast.error(next);
        Haptics.error();
        // Clear the error after showing
        Future.delayed(const Duration(seconds: 3), () {
          ref.read(authProvider.notifier).clearError();
        });
      }
    });

    return Scaffold(
      backgroundColor: AppColors.backgroundLight,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: widget.isWelcome
            ? null
            : IconButton(
                icon: const Icon(Icons.arrow_back_ios, color: AppColors.textPrimary),
                onPressed: () => context.pop(),
              ),
        title: Text(
          'Verify Email',
          style: AppTypography.headlineSmall,
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppDimensions.spacing24),
          child: Column(
            children: [
              const SizedBox(height: AppDimensions.spacing32),

              // Illustration
              Container(
                width: 200,
                height: 200,
                decoration: BoxDecoration(
                  color: AppColors.primaryPurple.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: Stack(
                  alignment: Alignment.center,
                  children: [
                    // Floating decorative elements
                    Positioned(
                      top: 30,
                      right: 40,
                      child: Container(
                        width: 12,
                        height: 12,
                        decoration: const BoxDecoration(
                          color: AppColors.accentOrange,
                          shape: BoxShape.circle,
                        ),
                      ),
                    ),
                    Positioned(
                      bottom: 40,
                      left: 30,
                      child: Container(
                        width: 8,
                        height: 8,
                        decoration: const BoxDecoration(
                          color: AppColors.successGreen,
                          shape: BoxShape.circle,
                        ),
                      ),
                    ),
                    Positioned(
                      top: 50,
                      left: 45,
                      child: Container(
                        width: 6,
                        height: 6,
                        decoration: const BoxDecoration(
                          color: AppColors.infoBlue,
                          shape: BoxShape.circle,
                        ),
                      ),
                    ),

                    // Central email icon
                    Container(
                      width: 80,
                      height: 80,
                      decoration: BoxDecoration(
                        color: AppColors.primaryPurple,
                        borderRadius: BorderRadius.circular(AppDimensions.radiusLarge),
                      ),
                      child: const Icon(
                        Icons.email_outlined,
                        color: AppColors.cardWhite,
                        size: 40,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: AppDimensions.spacing32),

              // Title and description
              Text(
                widget.isWelcome ? 'Welcome to BizInvestify!' : 'Check Your Email',
                style: AppTypography.displaySmall.copyWith(
                  fontWeight: FontWeight.w800,
                ),
                textAlign: TextAlign.center,
              ),

              const SizedBox(height: AppDimensions.spacing16),

              Text(
                widget.isWelcome
                    ? 'We\'ve sent a verification link to your email address. Please check your inbox and click the link to verify your account.'
                    : 'We\'ve sent a verification link to ${widget.email ?? 'your email'}. Please check your inbox and click the link to continue.',
                style: AppTypography.bodyLarge.copyWith(
                  color: AppColors.textSecondary,
                ),
                textAlign: TextAlign.center,
              ),

              const SizedBox(height: AppDimensions.spacing32),

              // Verification steps
              Container(
                padding: const EdgeInsets.all(AppDimensions.spacing20),
                decoration: BoxDecoration(
                  color: AppColors.cardWhite,
                  borderRadius: BorderRadius.circular(AppDimensions.radiusLarge),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.textPrimary.withValues(alpha: 0.04),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Verification Steps:',
                      style: AppTypography.titleMedium.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: AppDimensions.spacing16),
                    
                    _buildVerificationStep(
                      icon: Icons.email,
                      title: 'Check your email',
                      description: 'Look for an email from BizInvestify',
                    ),
                    
                    const SizedBox(height: AppDimensions.spacing12),
                    
                    _buildVerificationStep(
                      icon: Icons.link,
                      title: 'Click the verification link',
                      description: 'This will verify your email address',
                    ),
                    
                    const SizedBox(height: AppDimensions.spacing12),
                    
                    _buildVerificationStep(
                      icon: Icons.check_circle_outline,
                      title: 'Complete verification',
                      description: 'You\'ll be redirected to continue setup',
                    ),
                  ],
                ),
              ),

              const SizedBox(height: AppDimensions.spacing32),

              // Resend verification
              if (widget.email != null) ...[
                Text(
                  'Didn\'t receive the email?',
                  style: AppTypography.bodyMedium.copyWith(
                    color: AppColors.textSecondary,
                  ),
                  textAlign: TextAlign.center,
                ),

                const SizedBox(height: AppDimensions.spacing16),

                SecondaryButton(
                  text: _resendCooldown > 0
                      ? 'Resend in ${_resendCooldown}s'
                      : 'Resend Verification Email',
                  onPressed: _resendCooldown > 0 ? null : _resendVerification,
                  isLoading: _isResending,
                  width: double.infinity,
                ),

                const SizedBox(height: AppDimensions.spacing24),
              ],

              // Help text
              Container(
                padding: const EdgeInsets.all(AppDimensions.spacing16),
                decoration: BoxDecoration(
                  color: AppColors.infoBlue.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
                  border: Border.all(
                    color: AppColors.infoBlue.withValues(alpha: 0.2),
                  ),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.info_outline,
                      color: AppColors.infoBlue,
                      size: 20,
                    ),
                    const SizedBox(width: AppDimensions.spacing12),
                    Expanded(
                      child: Text(
                        'Check your spam folder if you don\'t see the email in your inbox.',
                        style: AppTypography.bodySmall.copyWith(
                          color: AppColors.infoBlue,
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: AppDimensions.spacing32),

              // Navigation buttons
              if (!widget.isWelcome) ...[
                PrimaryButton(
                  text: 'Continue to Phone Verification',
                  onPressed: _navigateToPhoneVerification,
                  width: double.infinity,
                  size: ButtonSize.large,
                ),

                const SizedBox(height: AppDimensions.spacing16),

                TextButton(
                  onPressed: _navigateToLogin,
                  child: Text(
                    'Back to Login',
                    style: AppTypography.bodyMedium.copyWith(
                      color: AppColors.primaryPurple,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildVerificationStep({
    required IconData icon,
    required String title,
    required String description,
  }) {
    return Row(
      children: [
        Container(
          width: 32,
          height: 32,
          decoration: BoxDecoration(
            color: AppColors.primaryPurple.withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(AppDimensions.radiusSmall),
          ),
          child: Icon(
            icon,
            color: AppColors.primaryPurple,
            size: 16,
          ),
        ),
        const SizedBox(width: AppDimensions.spacing12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: AppTypography.bodyMedium.copyWith(
                  fontWeight: FontWeight.w500,
                ),
              ),
              Text(
                description,
                style: AppTypography.bodySmall.copyWith(
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
