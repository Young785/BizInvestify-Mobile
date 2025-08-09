import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../../../core/constants/colors.dart';
import '../../../core/constants/dimensions.dart';
import '../../../core/constants/typography.dart';
import '../../../core/constants/routes.dart';
import '../../../components/buttons/primary_button.dart';

import '../models/auth_models.dart';
import '../providers/auth_provider.dart';

/// Two-Factor Authentication setup screen
class Setup2FAScreen extends ConsumerStatefulWidget {
  const Setup2FAScreen({super.key});

  @override
  ConsumerState<Setup2FAScreen> createState() => _Setup2FAScreenState();
}

class _Setup2FAScreenState extends ConsumerState<Setup2FAScreen> {
  final _codeController = TextEditingController();
  final PageController _pageController = PageController();

  TwoFactorSetupData? _setupData;
  int _currentStep = 0;
  bool _isLoading = false;

  final List<String> _steps = [
    'Choose Method',
    'Setup Authenticator',
    'Verify Code',
    'Backup Codes',
  ];

  @override
  void initState() {
    super.initState();
    _setup2FA();
  }

  @override
  void dispose() {
    _codeController.dispose();
    _pageController.dispose();
    super.dispose();
  }

  Future<void> _setup2FA() async {
    setState(() {
      _isLoading = true;
    });

    final authNotifier = ref.read(authProvider.notifier);
    final setupData = await authNotifier.setup2FA();

    if (setupData != null && mounted) {
      setState(() {
        _setupData = setupData;
        _currentStep = 1;
      });
      _pageController.nextPage(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
      );
    }

    setState(() {
      _isLoading = false;
    });
  }

  Future<void> _confirm2FA() async {
    if (_codeController.text.length != 6) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter a valid 6-digit code'),
          backgroundColor: AppColors.errorRed,
        ),
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    final authNotifier = ref.read(authProvider.notifier);
    final success = await authNotifier.confirm2FA(_codeController.text);

    if (success && mounted) {
      setState(() {
        _currentStep = 3;
      });
      _pageController.nextPage(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
      );
    }

    setState(() {
      _isLoading = false;
    });
  }

  Future<void> _skip2FA() async {
    final authNotifier = ref.read(authProvider.notifier);
    final success = await authNotifier.skip2FA();

    if (success && mounted) {
      context.go(AppRoutes.home);
    }
  }

  void _completeTwoFactorSetup() {
    context.go(AppRoutes.home);
  }

  void _nextStep() {
    if (_currentStep < _steps.length - 1) {
      setState(() {
        _currentStep++;
      });
      _pageController.nextPage(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
      );
    }
  }

  void _previousStep() {
    if (_currentStep > 0) {
      setState(() {
        _currentStep--;
      });
      _pageController.previousPage(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
      );
    }
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
        leading: _currentStep > 0
            ? IconButton(
                icon: const Icon(Icons.arrow_back_ios, color: AppColors.textPrimary),
                onPressed: _previousStep,
              )
            : IconButton(
                icon: const Icon(Icons.close, color: AppColors.textPrimary),
                onPressed: () => context.pop(),
              ),
        title: Text(
          'Two-Factor Authentication',
          style: AppTypography.headlineSmall,
        ),
        actions: [
          if (_currentStep < 3)
            TextButton(
              onPressed: _skip2FA,
              child: Text(
                'Skip',
                style: AppTypography.bodyMedium.copyWith(
                  color: AppColors.textSecondary,
                ),
              ),
            ),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Progress indicator
            Container(
              padding: const EdgeInsets.symmetric(horizontal: AppDimensions.spacing24),
              child: Row(
                children: [
                  for (int i = 0; i < _steps.length; i++)
                    Expanded(
                      child: Container(
                        height: 4,
                        margin: EdgeInsets.only(right: i < _steps.length - 1 ? 8 : 0),
                        decoration: BoxDecoration(
                          color: i <= _currentStep
                              ? AppColors.primaryPurple
                              : AppColors.borderLight,
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                    ),
                ],
              ),
            ),

            const SizedBox(height: AppDimensions.spacing24),

            // Step indicator
            Text(
              'Step ${_currentStep + 1} of ${_steps.length}',
              style: AppTypography.bodyMedium.copyWith(
                color: AppColors.textSecondary,
              ),
            ),

            Text(
              _steps[_currentStep],
              style: AppTypography.titleLarge.copyWith(
                fontWeight: FontWeight.w600,
              ),
            ),

            const SizedBox(height: AppDimensions.spacing24),

            // Page view for different steps
            Expanded(
              child: PageView(
                controller: _pageController,
                physics: const NeverScrollableScrollPhysics(),
                children: [
                  _buildChooseMethodStep(),
                  _buildSetupAuthenticatorStep(),
                  _buildVerifyCodeStep(),
                  _buildBackupCodesStep(),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildChooseMethodStep() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppDimensions.spacing24),
      child: Column(
        children: [
          // Illustration
          Container(
            width: 120,
            height: 120,
            decoration: BoxDecoration(
              color: AppColors.primaryPurple.withValues(alpha: 0.1),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.security,
              color: AppColors.primaryPurple,
              size: 60,
            ),
          ),

          const SizedBox(height: AppDimensions.spacing32),

          Text(
            'Secure Your Account',
            style: AppTypography.displaySmall.copyWith(
              fontWeight: FontWeight.w800,
            ),
            textAlign: TextAlign.center,
          ),

          const SizedBox(height: AppDimensions.spacing16),

          Text(
            'Two-factor authentication adds an extra layer of security to your account by requiring a second form of verification.',
            style: AppTypography.bodyLarge.copyWith(
              color: AppColors.textSecondary,
            ),
            textAlign: TextAlign.center,
          ),

          const SizedBox(height: AppDimensions.spacing32),

          // Method selection (for now, only Google Authenticator)
          _buildMethodCard(
            icon: Icons.phone_android,
            title: 'Authenticator App',
            description: 'Use Google Authenticator or similar app',
            isSelected: true,
            onTap: _setup2FA,
          ),

          const SizedBox(height: AppDimensions.spacing32),

          PrimaryButton(
            text: 'Continue with Authenticator App',
            onPressed: _setup2FA,
            isLoading: _isLoading,
            width: double.infinity,
            size: ButtonSize.large,
          ),
        ],
      ),
    );
  }

  Widget _buildSetupAuthenticatorStep() {
    if (_setupData == null) {
      return const Center(child: CircularProgressIndicator());
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppDimensions.spacing24),
      child: Column(
        children: [
          Text(
            'Scan QR Code',
            style: AppTypography.displaySmall.copyWith(
              fontWeight: FontWeight.w800,
            ),
            textAlign: TextAlign.center,
          ),

          const SizedBox(height: AppDimensions.spacing16),

          Text(
            'Open your authenticator app and scan the QR code below to add your BizInvestify account.',
            style: AppTypography.bodyLarge.copyWith(
              color: AppColors.textSecondary,
            ),
            textAlign: TextAlign.center,
          ),

          const SizedBox(height: AppDimensions.spacing32),

          // QR Code
          Container(
            padding: const EdgeInsets.all(AppDimensions.spacing20),
            decoration: BoxDecoration(
              color: AppColors.cardWhite,
              borderRadius: BorderRadius.circular(AppDimensions.radiusLarge),
              boxShadow: [
                BoxShadow(
                  color: AppColors.textPrimary.withValues(alpha: 0.08),
                  blurRadius: 16,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: QrImageView(
              data: _setupData!.qrCodeUrl,
              version: QrVersions.auto,
              size: 200.0,
              backgroundColor: AppColors.cardWhite,
              eyeStyle: const QrEyeStyle(color: AppColors.textPrimary),
        dataModuleStyle: const QrDataModuleStyle(color: AppColors.textPrimary),
            ),
          ),

          const SizedBox(height: AppDimensions.spacing24),

          // Manual entry option
          Text(
            'Can\'t scan? Enter this code manually:',
            style: AppTypography.bodyMedium.copyWith(
              color: AppColors.textSecondary,
            ),
            textAlign: TextAlign.center,
          ),

          const SizedBox(height: AppDimensions.spacing12),

          Container(
            padding: const EdgeInsets.all(AppDimensions.spacing16),
            decoration: BoxDecoration(
              color: AppColors.backgroundGray,
              borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
            ),
            child: Row(
              children: [
                Expanded(
                  child: Text(
                    _setupData!.manualEntryKey,
                    style: AppTypography.bodyMedium.copyWith(
                      fontFamily: 'monospace',
                      fontWeight: FontWeight.w500,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
                IconButton(
                  onPressed: () {
                    Clipboard.setData(ClipboardData(text: _setupData!.manualEntryKey));
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Code copied to clipboard'),
                        backgroundColor: AppColors.successGreen,
                      ),
                    );
                  },
                  icon: const Icon(Icons.copy, size: 20),
                ),
              ],
            ),
          ),

          const SizedBox(height: AppDimensions.spacing32),

          PrimaryButton(
            text: 'I\'ve Added the Account',
            onPressed: _nextStep,
            width: double.infinity,
            size: ButtonSize.large,
          ),
        ],
      ),
    );
  }

  Widget _buildVerifyCodeStep() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppDimensions.spacing24),
      child: Column(
        children: [
          Text(
            'Verify Setup',
            style: AppTypography.displaySmall.copyWith(
              fontWeight: FontWeight.w800,
            ),
            textAlign: TextAlign.center,
          ),

          const SizedBox(height: AppDimensions.spacing16),

          Text(
            'Enter the 6-digit code from your authenticator app to verify the setup.',
            style: AppTypography.bodyLarge.copyWith(
              color: AppColors.textSecondary,
            ),
            textAlign: TextAlign.center,
          ),

          const SizedBox(height: AppDimensions.spacing32),

          // Code input
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
              controller: _codeController,
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
                setState(() {});
                if (value.length == 6) {
                  _confirm2FA();
                }
              },
            ),
          ),

          const SizedBox(height: AppDimensions.spacing32),

          PrimaryButton(
            text: 'Verify Code',
            onPressed: _codeController.text.length == 6 ? _confirm2FA : null,
            isLoading: _isLoading,
            isEnabled: _codeController.text.length == 6,
            width: double.infinity,
            size: ButtonSize.large,
          ),
        ],
      ),
    );
  }

  Widget _buildBackupCodesStep() {
    if (_setupData == null) {
      return const Center(child: CircularProgressIndicator());
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppDimensions.spacing24),
      child: Column(
        children: [
          Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              color: AppColors.successGreen.withValues(alpha: 0.1),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.check_circle,
              color: AppColors.successGreen,
              size: 40,
            ),
          ),

          const SizedBox(height: AppDimensions.spacing24),

          Text(
            '2FA Setup Complete!',
            style: AppTypography.displaySmall.copyWith(
              fontWeight: FontWeight.w800,
            ),
            textAlign: TextAlign.center,
          ),

          const SizedBox(height: AppDimensions.spacing16),

          Text(
            'Save these backup codes in a safe place. You can use them to access your account if you lose your authenticator device.',
            style: AppTypography.bodyLarge.copyWith(
              color: AppColors.textSecondary,
            ),
            textAlign: TextAlign.center,
          ),

          const SizedBox(height: AppDimensions.spacing32),

          // Backup codes
          Container(
            padding: const EdgeInsets.all(AppDimensions.spacing20),
            decoration: BoxDecoration(
              color: AppColors.cardWhite,
              borderRadius: BorderRadius.circular(AppDimensions.radiusLarge),
              border: Border.all(color: AppColors.borderLight),
            ),
            child: Column(
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Backup Codes',
                      style: AppTypography.titleMedium.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    IconButton(
                      onPressed: () {
                        final codesText = _setupData!.backupCodes.join('\n');
                        Clipboard.setData(ClipboardData(text: codesText));
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Backup codes copied to clipboard'),
                            backgroundColor: AppColors.successGreen,
                          ),
                        );
                      },
                      icon: const Icon(Icons.copy, size: 20),
                    ),
                  ],
                ),
                const SizedBox(height: AppDimensions.spacing16),
                ...(_setupData!.backupCodes.map((code) => Padding(
                  padding: const EdgeInsets.only(bottom: AppDimensions.spacing8),
                  child: Text(
                    code,
                    style: AppTypography.bodyMedium.copyWith(
                      fontFamily: 'monospace',
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ))),
              ],
            ),
          ),

          const SizedBox(height: AppDimensions.spacing32),

          PrimaryButton(
            text: 'Continue to Dashboard',
            onPressed: _completeTwoFactorSetup,
            width: double.infinity,
            size: ButtonSize.large,
          ),
        ],
      ),
    );
  }

  Widget _buildMethodCard({
    required IconData icon,
    required String title,
    required String description,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(AppDimensions.spacing20),
        decoration: BoxDecoration(
          color: AppColors.cardWhite,
          border: Border.all(
            color: isSelected ? AppColors.primaryPurple : AppColors.borderLight,
            width: isSelected ? 2 : 1,
          ),
          borderRadius: BorderRadius.circular(AppDimensions.radiusLarge),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: AppColors.primaryPurple.withValues(alpha: 0.1),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ]
              : [
                  BoxShadow(
                    color: AppColors.textPrimary.withValues(alpha: 0.04),
                    blurRadius: 4,
                    offset: const Offset(0, 1),
                  ),
                ],
        ),
        child: Row(
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                color: isSelected 
                    ? AppColors.primaryPurple 
                    : AppColors.primaryPurple.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
              ),
              child: Icon(
                icon,
                color: isSelected ? AppColors.cardWhite : AppColors.primaryPurple,
                size: 28,
              ),
            ),
            const SizedBox(width: AppDimensions.spacing16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: AppTypography.titleLarge.copyWith(
                      fontWeight: FontWeight.w600,
                      color: isSelected ? AppColors.primaryPurple : AppColors.textPrimary,
                    ),
                  ),
                  const SizedBox(height: AppDimensions.spacing4),
                  Text(
                    description,
                    style: AppTypography.bodyMedium.copyWith(
                      color: AppColors.textSecondary,
                    ),
                  ),
                ],
              ),
            ),
            if (isSelected)
              const Icon(
                Icons.check_circle,
                color: AppColors.primaryPurple,
                size: 24,
              ),
          ],
        ),
      ),
    );
  }
}
