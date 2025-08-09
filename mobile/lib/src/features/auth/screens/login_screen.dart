import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants/colors.dart';
import '../../../core/constants/dimensions.dart';
import '../../../core/constants/typography.dart';
import '../../../core/constants/routes.dart';
import '../../../components/buttons/primary_button.dart';
import '../../../components/inputs/custom_text_field.dart';
import '../../../components/inputs/password_field.dart';
import '../models/auth_models.dart';
import '../providers/auth_provider.dart';
import '../../../core/utils/toast.dart';
import '../../../core/utils/feedback.dart';

/// Beautiful login screen matching the mockup design
class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _twoFactorController = TextEditingController();
  bool _rememberDevice = false;
  LoginStep _currentStep = LoginStep.credentials;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    _twoFactorController.dispose();
    super.dispose();
  }

  bool get _isFormValid {
    if (_currentStep == LoginStep.credentials) {
      return _emailController.text.isNotEmpty && 
             _passwordController.text.isNotEmpty;
    } else if (_currentStep == LoginStep.twoFactor) {
      return _twoFactorController.text.length == 6;
    }
    return false;
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    final authNotifier = ref.read(authProvider.notifier);

    if (_currentStep == LoginStep.credentials) {
      final loginData = LoginData(
        email: _emailController.text,
        password: _passwordController.text,
        rememberDevice: _rememberDevice,
      );

      final result = await authNotifier.login(loginData);

      if (result == LoginResult.success) {
        if (!mounted) return;
        _handleSuccessfulLogin();
      } else if (result == LoginResult.requires2FA) {
        setState(() {
          _currentStep = LoginStep.twoFactor;
        });
      }
      // Error handling is done through the provider
    } else if (_currentStep == LoginStep.twoFactor) {
      final loginData = LoginData(
        email: _emailController.text,
        password: _passwordController.text,
        twoFactorCode: _twoFactorController.text,
        rememberDevice: _rememberDevice,
      );

      final result = await authNotifier.login(loginData);

      if (result == LoginResult.success) {
        if (!mounted) return;
        _handleSuccessfulLogin();
      }
    }
  }

  void _handleSuccessfulLogin() {
    // Show a single success message before navigating
    final currentUser = ref.read(authProvider).user;
    final welcomeText = currentUser != null
        ? 'Welcome back, ${currentUser.firstName}!'
        : 'Signed in successfully';
    AppToast.success(welcomeText);
    Haptics.success();

    final verificationStatus = ref.read(authProvider).verificationStatus;

    // Defer navigation slightly to allow the success message to be seen
    if (verificationStatus == null) {
      Future.delayed(const Duration(milliseconds: 1200), () {
        _navigateBasedOnVerificationStatus();
      });
    } else {
      Future.delayed(const Duration(milliseconds: 1200), () {
        _navigateBasedOnVerificationStatus();
      });
    }
  }

  void _navigateBasedOnVerificationStatus() {
    final verificationStatus = ref.read(authProvider).verificationStatus;
    
    if (verificationStatus == null) {
      context.go(AppRoutes.home);
      return;
    }

    // Navigate based on verification progress
    if (!verificationStatus.emailVerified) {
      context.go(AppRoutes.verifyEmail);
    } else if (!verificationStatus.phoneVerified) {
      context.go(AppRoutes.verifyPhone);
    } else if (!verificationStatus.kycSubmitted) {
      // Navigate to KYC screen (to be implemented)
      context.go(AppRoutes.home);
    } else if (!verificationStatus.twoFactorEnabled) {
      context.go(AppRoutes.setup2FA);
    } else {
      context.go(AppRoutes.home);
    }
  }

  String? _validateEmail(String? value) {
    if (value == null || value.isEmpty) {
      return 'Email is required';
    }
    if (!RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(value)) {
      return 'Please enter a valid email';
    }
    return null;
  }

  String? _validatePassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'Password is required';
    }
    if (value.length < 6) {
      return 'Password must be at least 6 characters';
    }
    return null;
  }

  @override
  Widget build(BuildContext context) {
    final isLoading = ref.watch(authLoadingProvider);

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
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, color: AppColors.textPrimary),
          onPressed: () => context.pop(),
        ),
        title: Text(
          'Sign in',
          style: AppTypography.headlineSmall,
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppDimensions.spacing24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: AppDimensions.spacing24),
                
                // Welcome Section
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(AppDimensions.spacing24),
                  decoration: BoxDecoration(
                    color: AppColors.cardWhite,
                    borderRadius: BorderRadius.circular(AppDimensions.radiusXLarge),
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.textPrimary.withValues(alpha: 0.06),
                        blurRadius: 16,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _currentStep == LoginStep.credentials 
                          ? 'Welcome Back' 
                          : 'Two-Factor Authentication',
                        style: AppTypography.displaySmall.copyWith(
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(height: AppDimensions.spacing8),
                      Text(
                        _currentStep == LoginStep.credentials
                          ? 'Hello there, sign in to continue'
                          : 'Enter the 6-digit code from your authenticator app',
                        style: AppTypography.bodyLarge.copyWith(
                          color: AppColors.textSecondary,
                        ),
                      ),
                      
                      const SizedBox(height: AppDimensions.spacing32),
                      
                      // Lock Icon with Floating Elements
                      Center(
                        child: Container(
                          width: 120,
                          height: 120,
                          decoration: BoxDecoration(
                            color: AppColors.primaryPurple.withValues(alpha: 0.1),
                            shape: BoxShape.circle,
                          ),
                          child: Stack(
                            alignment: Alignment.center,
                            children: [
                              // Floating decorative dots
                              Positioned(
                                top: 20,
                                right: 25,
                                child: Container(
                                  width: 8,
                                  height: 8,
                                  decoration: const BoxDecoration(
                                    color: AppColors.accentOrange,
                                    shape: BoxShape.circle,
                                  ),
                                ),
                              ),
                              Positioned(
                                bottom: 25,
                                left: 20,
                                child: Container(
                                  width: 6,
                                  height: 6,
                                  decoration: const BoxDecoration(
                                    color: AppColors.successGreen,
                                    shape: BoxShape.circle,
                                  ),
                                ),
                              ),
                              Positioned(
                                top: 30,
                                left: 30,
                                child: Container(
                                  width: 4,
                                  height: 4,
                                  decoration: const BoxDecoration(
                                    color: AppColors.infoBlue,
                                    shape: BoxShape.circle,
                                  ),
                                ),
                              ),
                              Positioned(
                                bottom: 20,
                                right: 30,
                                child: Container(
                                  width: 10,
                                  height: 10,
                                  decoration: const BoxDecoration(
                                    color: AppColors.warningYellow,
                                    shape: BoxShape.circle,
                                  ),
                                ),
                              ),
                              
                              // Central lock icon
                              Container(
                                width: 60,
                                height: 60,
                                decoration: BoxDecoration(
                                  color: AppColors.primaryPurple,
                                  borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
                                ),
                                child: const Icon(
                                  Icons.lock_outline,
                                  color: AppColors.cardWhite,
                                  size: 28,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(height: AppDimensions.spacing32),
                
                // Form Fields
                if (_currentStep == LoginStep.credentials) ...[
                  CustomTextField(
                    label: 'Email',
                    hint: 'Enter your email',
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    textInputAction: TextInputAction.next,
                    prefixIcon: Icons.email_outlined,
                    validator: _validateEmail,
                    onChanged: (_) => setState(() {}),
                  ),
                  
                  const SizedBox(height: AppDimensions.spacing20),
                  
                  PasswordField(
                    label: 'Password',
                    hint: 'Enter your password',
                    controller: _passwordController,
                    textInputAction: TextInputAction.done,
                    validator: _validatePassword,
                    onChanged: (_) => setState(() {}),
                    onSubmitted: (_) => _handleLogin(),
                  ),
                ] else if (_currentStep == LoginStep.twoFactor) ...[
                  CustomTextField(
                    label: '2FA Code',
                    hint: '000000',
                    controller: _twoFactorController,
                    keyboardType: TextInputType.number,
                    textInputAction: TextInputAction.done,
                    prefixIcon: Icons.security,
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return '2FA code is required';
                      }
                      if (value.length != 6) {
                        return 'Please enter a valid 6-digit code';
                      }
                      return null;
                    },
                    onChanged: (_) => setState(() {}),
                    onSubmitted: (_) => _handleLogin(),
                  ),
                  
                  const SizedBox(height: AppDimensions.spacing16),
                  
                  // Back to credentials button
                  TextButton(
                    onPressed: () {
                      setState(() {
                        _currentStep = LoginStep.credentials;
                        _twoFactorController.clear();
                      });
                    },
                    child: Text(
                      'Back to login',
                      style: AppTypography.bodyMedium.copyWith(
                        color: AppColors.primaryPurple,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                ],
                
                const SizedBox(height: AppDimensions.spacing16),
                
                // Remember Device & Forgot Password (only show on credentials step)
                if (_currentStep == LoginStep.credentials) ...[
                  Row(
                    children: [
                      SizedBox(
                        width: 24,
                        height: 24,
                        child: Checkbox(
                          value: _rememberDevice,
                          onChanged: (value) {
                            setState(() {
                              _rememberDevice = value ?? false;
                            });
                          },
                          activeColor: AppColors.primaryPurple,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(AppDimensions.radiusSmall),
                          ),
                        ),
                      ),
                      const SizedBox(width: AppDimensions.spacing8),
                      Text(
                        'Remember this device',
                        style: AppTypography.bodyMedium,
                      ),
                      const Spacer(),
                      TextButton(
                        onPressed: () {
                          context.push(AppRoutes.forgotPassword);
                        },
                        child: Text(
                          'Forgot your password?',
                          style: AppTypography.bodyMedium.copyWith(
                            color: AppColors.primaryPurple,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
                
                const SizedBox(height: AppDimensions.spacing32),
                
                // Sign In Button
                PrimaryButton(
                  text: _currentStep == LoginStep.credentials ? 'Sign In' : 'Verify',
                  onPressed: _isFormValid ? _handleLogin : null,
                  isLoading: isLoading,
                  isEnabled: _isFormValid,
                  width: double.infinity,
                  size: ButtonSize.large,
                ),
                
                const SizedBox(height: AppDimensions.spacing24),
                
                // Biometric Authentication (only show on credentials step)
                if (_currentStep == LoginStep.credentials) ...[
                  Center(
                    child: Container(
                      width: 64,
                      height: 64,
                      decoration: BoxDecoration(
                        color: AppColors.primaryPurple.withValues(alpha: 0.1),
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: AppColors.primaryPurple.withValues(alpha: 0.2),
                          width: 2,
                        ),
                      ),
                      child: const Icon(
                        Icons.fingerprint,
                        color: AppColors.primaryPurple,
                        size: 32,
                      ),
                    ),
                  ),
                  
                  const SizedBox(height: AppDimensions.spacing32),
                  
                  // Sign Up Link
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        "Don't have an account? ",
                        style: AppTypography.bodyMedium,
                      ),
                      TextButton(
                        onPressed: () {
                          context.push(AppRoutes.register);
                        },
                        child: Text(
                          'Sign Up',
                          style: AppTypography.bodyMedium.copyWith(
                            color: AppColors.primaryPurple,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
                
                const SizedBox(height: AppDimensions.spacing24),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
