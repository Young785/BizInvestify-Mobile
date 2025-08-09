import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
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
import '../../../components/inputs/custom_text_field.dart';
import '../providers/auth_provider.dart';

/// Phone verification screen with OTP input
class VerifyPhoneScreen extends ConsumerStatefulWidget {
  final String? email;
  final String? phone;

  const VerifyPhoneScreen({
    super.key,
    this.email,
    this.phone,
  });

  @override
  ConsumerState<VerifyPhoneScreen> createState() => _VerifyPhoneScreenState();
}

class _VerifyPhoneScreenState extends ConsumerState<VerifyPhoneScreen> {
  final _phoneController = TextEditingController();
  final _otpController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  
  bool _isCodeSent = false;
  bool _isSendingCode = false;
  bool _isVerifying = false;
  int _resendCooldown = 0;
  String? _verificationPhone;

  @override
  void initState() {
    super.initState();
    if (widget.phone != null) {
      _phoneController.text = widget.phone!;
    }
  }

  @override
  void dispose() {
    _phoneController.dispose();
    _otpController.dispose();
    super.dispose();
  }

  Future<void> _sendVerificationCode() async {
    if (!_formKey.currentState!.validate()) return;

    final email = widget.email ?? ref.read(authProvider).user?.email;
    if (email == null) {
      AppToast.error('Email is required for phone verification');
      Haptics.error();
      return;
    }

    setState(() {
      _isSendingCode = true;
    });

    final authNotifier = ref.read(authProvider.notifier);
    final success = await authNotifier.sendPhoneVerificationCode(email, _phoneController.text);

    if (success && mounted) {
      setState(() {
        _isCodeSent = true;
        _verificationPhone = _phoneController.text;
        _resendCooldown = 60;
      });

      _startCooldownTimer();

      AppToast.success('Verification code sent to your phone!');
      Haptics.success();
    }

    setState(() {
      _isSendingCode = false;
    });
  }

  Future<void> _verifyCode() async {
    if (_otpController.text.length != 6) {
      AppToast.error('Please enter a valid 6-digit code');
      Haptics.error();
      return;
    }

    final email = widget.email ?? ref.read(authProvider).user?.email;
    if (email == null) return;

    setState(() {
      _isVerifying = true;
    });

    final authNotifier = ref.read(authProvider.notifier);
    final success = await authNotifier.verifyPhone(email, _otpController.text);

    if (success && mounted) {
      AppToast.success('Phone verified successfully!');
      Haptics.success();

      // Navigate to next step (KYC or 2FA setup)
      Future.delayed(const Duration(seconds: 1), () {
        if (mounted) {
          context.go(AppRoutes.setup2FA);
        }
      });
    }

    setState(() {
      _isVerifying = false;
    });
  }

  Future<void> _resendCode() async {
    final email = widget.email ?? ref.read(authProvider).user?.email;
    if (email == null || _verificationPhone == null) return;

    setState(() {
      _isSendingCode = true;
    });

    final authNotifier = ref.read(authProvider.notifier);
    final success = await authNotifier.sendPhoneVerificationCode(email, _verificationPhone!);

    if (success && mounted) {
      setState(() {
        _resendCooldown = 60;
      });

      _startCooldownTimer();

      AppToast.info('Verification code sent!');
    }

    setState(() {
      _isSendingCode = false;
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

  void _changePhoneNumber() {
    setState(() {
      _isCodeSent = false;
      _otpController.clear();
      _verificationPhone = null;
    });
  }

  @override
  Widget build(BuildContext context) {
    ref.watch(authErrorProvider);

    // Listen for auth errors and show snackbar
    ref.listen<String?>(authErrorProvider, (previous, next) {
      if (next != null) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(next),
            backgroundColor: AppColors.errorRed,
          ),
        );
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
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, color: AppColors.textPrimary),
          onPressed: () => context.pop(),
        ),
        title: Text(
          'Verify Phone',
          style: AppTypography.headlineSmall,
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppDimensions.spacing24),
          child: Form(
            key: _formKey,
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

                      // Central phone icon
                      Container(
                        width: 80,
                        height: 80,
                        decoration: BoxDecoration(
                          color: AppColors.primaryPurple,
                          borderRadius: BorderRadius.circular(AppDimensions.radiusLarge),
                        ),
                        child: const Icon(
                          Icons.phone_android,
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
                  _isCodeSent ? 'Enter Verification Code' : 'Verify Your Phone',
                  style: AppTypography.displaySmall.copyWith(
                    fontWeight: FontWeight.w800,
                  ),
                  textAlign: TextAlign.center,
                ),

                const SizedBox(height: AppDimensions.spacing16),

                Text(
                  _isCodeSent
                      ? 'We\'ve sent a 6-digit verification code to $_verificationPhone. Please enter the code below.'
                      : 'We need to verify your phone number to secure your account and enable important notifications.',
                  style: AppTypography.bodyLarge.copyWith(
                    color: AppColors.textSecondary,
                  ),
                  textAlign: TextAlign.center,
                ),

                const SizedBox(height: AppDimensions.spacing32),

                if (!_isCodeSent) ...[
                  // Phone input
                  CustomTextField(
                    label: 'Phone Number',
                    hint: '+1 (555) 123-4567',
                    controller: _phoneController,
                    keyboardType: TextInputType.phone,
                    textInputAction: TextInputAction.done,
                    prefixIcon: Icons.phone,
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Phone number is required';
                      }
                      return null;
                    },
                    onSubmitted: (_) => _sendVerificationCode(),
                  ),

                  const SizedBox(height: AppDimensions.spacing32),

                  // Send code button
                  PrimaryButton(
                    text: 'Send Verification Code',
                    onPressed: _sendVerificationCode,
                    isLoading: _isSendingCode,
                    width: double.infinity,
                    size: ButtonSize.large,
                  ),
                ] else ...[
                  // OTP input
                  Container(
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
                    child: TextFormField(
                      controller: _otpController,
                      keyboardType: TextInputType.number,
                      textAlign: TextAlign.center,
                      style: AppTypography.displaySmall.copyWith(
                        fontWeight: FontWeight.w600,
                        letterSpacing: 8,
                      ),
                      inputFormatters: [
                        FilteringTextInputFormatter.digitsOnly,
                        LengthLimitingTextInputFormatter(6),
                      ],
                      decoration: InputDecoration(
                        hintText: '000000',
                        hintStyle: AppTypography.displaySmall.copyWith(
                          color: AppColors.textTertiary,
                          letterSpacing: 8,
                        ),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(AppDimensions.radiusLarge),
                          borderSide: BorderSide.none,
                        ),
                        filled: true,
                        fillColor: Colors.transparent,
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: AppDimensions.spacing24,
                          vertical: AppDimensions.spacing20,
                        ),
                      ),
                      onChanged: (value) {
                        if (value.length == 6) {
                          _verifyCode();
                        }
                      },
                    ),
                  ),

                  const SizedBox(height: AppDimensions.spacing24),

                  // Change phone number
                  TextButton(
                    onPressed: _changePhoneNumber,
                    child: Text(
                      'Change phone number',
                      style: AppTypography.bodyMedium.copyWith(
                        color: AppColors.primaryPurple,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),

                  const SizedBox(height: AppDimensions.spacing32),

                  // Verify button
                  PrimaryButton(
                    text: 'Verify Phone',
                    onPressed: _otpController.text.length == 6 ? _verifyCode : null,
                    isLoading: _isVerifying,
                    isEnabled: _otpController.text.length == 6,
                    width: double.infinity,
                    size: ButtonSize.large,
                  ),

                  const SizedBox(height: AppDimensions.spacing24),

                  // Resend code
                  Text(
                    'Didn\'t receive the code?',
                    style: AppTypography.bodyMedium.copyWith(
                      color: AppColors.textSecondary,
                    ),
                    textAlign: TextAlign.center,
                  ),

                  const SizedBox(height: AppDimensions.spacing16),

                  SecondaryButton(
                    text: _resendCooldown > 0
                        ? 'Resend in ${_resendCooldown}s'
                        : 'Resend Code',
                    onPressed: _resendCooldown > 0 ? null : _resendCode,
                    isLoading: _isSendingCode,
                    width: double.infinity,
                  ),
                ],

                const SizedBox(height: AppDimensions.spacing32),

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
                          _isCodeSent
                              ? 'The verification code will expire in 10 minutes. Make sure to enter it before then.'
                              : 'We\'ll send a verification code via SMS to confirm your phone number.',
                          style: AppTypography.bodySmall.copyWith(
                            color: AppColors.infoBlue,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
