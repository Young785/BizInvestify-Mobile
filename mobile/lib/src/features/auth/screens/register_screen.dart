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

/// Multi-step registration screen
class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final PageController _pageController = PageController();
  final _formKey = GlobalKey<FormState>();
  
  // Controllers for form fields
  final _firstNameController = TextEditingController();
  final _lastNameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _businessNameController = TextEditingController();
  final _businessTypeController = TextEditingController();
  final _investmentAmountController = TextEditingController();
  final _investmentFocusController = TextEditingController();
  
  RegistrationStep _currentStep = RegistrationStep.roleSelection;
  UserRole _selectedRole = UserRole.seller;
  bool _agreeToTerms = false;
  bool _agreeToPrivacy = false;
  bool _confirmAge = false;
  bool _confirmIdentity = false;

  @override
  void dispose() {
    _pageController.dispose();
    _firstNameController.dispose();
    _lastNameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    _businessNameController.dispose();
    _businessTypeController.dispose();
    _investmentAmountController.dispose();
    _investmentFocusController.dispose();
    super.dispose();
  }

  void _nextStep() {
    if (_currentStep == RegistrationStep.roleSelection) {
      setState(() {
        _currentStep = RegistrationStep.basicInfo;
      });
      _pageController.nextPage(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
      );
    } else if (_currentStep == RegistrationStep.basicInfo) {
      if (_formKey.currentState!.validate()) {
        setState(() {
          _currentStep = RegistrationStep.professionalInfo;
        });
        _pageController.nextPage(
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeInOut,
        );
      }
    } else if (_currentStep == RegistrationStep.professionalInfo) {
      if (_validateProfessionalInfo()) {
        setState(() {
          _currentStep = RegistrationStep.verification;
        });
        _pageController.nextPage(
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeInOut,
        );
      }
    } else if (_currentStep == RegistrationStep.verification) {
      if (_validateVerification()) {
        _handleRegistration();
      }
    }
  }

  void _previousStep() {
    if (_currentStep == RegistrationStep.basicInfo) {
      setState(() {
        _currentStep = RegistrationStep.roleSelection;
      });
      _pageController.previousPage(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
      );
    } else if (_currentStep == RegistrationStep.professionalInfo) {
      setState(() {
        _currentStep = RegistrationStep.basicInfo;
      });
      _pageController.previousPage(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
      );
    } else if (_currentStep == RegistrationStep.verification) {
      setState(() {
        _currentStep = RegistrationStep.professionalInfo;
      });
      _pageController.previousPage(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
      );
    }
  }

  bool _validateProfessionalInfo() {
    if (_selectedRole == UserRole.seller) {
      if (_businessNameController.text.isEmpty || _businessTypeController.text.isEmpty) {
        AppToast.error('Please fill in all business information');
        Haptics.error();
        return false;
      }
    } else if (_selectedRole == UserRole.investor) {
      if (_investmentAmountController.text.isEmpty || _investmentFocusController.text.isEmpty) {
        AppToast.error('Please fill in all investment information');
        Haptics.error();
        return false;
      }
    }
    return true;
  }

  bool _validateVerification() {
    if (!_agreeToTerms || !_agreeToPrivacy || !_confirmAge || !_confirmIdentity) {
      AppToast.error('Please accept all terms and confirmations');
      Haptics.error();
      return false;
    }
    return true;
  }

  Future<void> _handleRegistration() async {
    final authNotifier = ref.read(authProvider.notifier);

    final registrationData = RegistrationData(
      role: _selectedRole.value,
      firstName: _firstNameController.text,
      lastName: _lastNameController.text,
      email: _emailController.text,
      phone: _phoneController.text,
      password: _passwordController.text,
      passwordConfirmation: _confirmPasswordController.text,
      businessName: _selectedRole == UserRole.seller ? _businessNameController.text : null,
      businessType: _selectedRole == UserRole.seller ? _businessTypeController.text : null,
      investmentAmount: _selectedRole == UserRole.investor ? _investmentAmountController.text : null,
      investmentFocus: _selectedRole == UserRole.investor ? _investmentFocusController.text : null,
      agreeToTerms: _agreeToTerms,
      agreeToPrivacy: _agreeToPrivacy,
      confirmAge: _confirmAge,
      confirmIdentity: _confirmIdentity,
    );

    final success = await authNotifier.register(registrationData);

    if (success && mounted) {
      AppToast.success('Account created! Verify your email to continue.');
      Haptics.success();
      context.go('${AppRoutes.verifyEmail}?email=${Uri.encodeComponent(_emailController.text)}&welcome=true');
    }
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
        leading: _currentStep != RegistrationStep.roleSelection
            ? IconButton(
                icon: const Icon(Icons.arrow_back_ios, color: AppColors.textPrimary),
                onPressed: _previousStep,
              )
            : IconButton(
                icon: const Icon(Icons.arrow_back_ios, color: AppColors.textPrimary),
                onPressed: () => context.pop(),
              ),
        title: Text(
          'Sign up',
          style: AppTypography.headlineSmall,
        ),
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Progress indicator
            Container(
              padding: const EdgeInsets.symmetric(horizontal: AppDimensions.spacing24),
              child: Row(
                children: [
                  for (int i = 0; i < 4; i++)
                    Expanded(
                      child: Container(
                        height: 4,
                        margin: EdgeInsets.only(right: i < 3 ? 8 : 0),
                        decoration: BoxDecoration(
                          color: i <= _currentStep.index
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
            
            // Page view for different steps
            Expanded(
              child: PageView(
                controller: _pageController,
                physics: const NeverScrollableScrollPhysics(),
                children: [
                  _buildRoleSelectionStep(),
                  _buildBasicInfoStep(),
                  _buildProfessionalInfoStep(),
                  _buildVerificationStep(),
                ],
              ),
            ),
            
            // Bottom action buttons
            Container(
              padding: const EdgeInsets.all(AppDimensions.spacing24),
              child: PrimaryButton(
                text: _getButtonText(),
                onPressed: _nextStep,
                isLoading: isLoading,
                width: double.infinity,
                size: ButtonSize.large,
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _getButtonText() {
    switch (_currentStep) {
      case RegistrationStep.roleSelection:
        return 'Continue';
      case RegistrationStep.basicInfo:
        return 'Next';
      case RegistrationStep.professionalInfo:
        return 'Next';
      case RegistrationStep.verification:
        return 'Create Account';
    }
  }

  Widget _buildRoleSelectionStep() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppDimensions.spacing24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Choose Your Role',
            style: AppTypography.displaySmall.copyWith(
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: AppDimensions.spacing8),
          Text(
            'Select how you\'ll use BizInvestify',
            style: AppTypography.bodyLarge.copyWith(
              color: AppColors.textSecondary,
            ),
          ),
          
          const SizedBox(height: AppDimensions.spacing32),
          
          // Role selection cards
          _buildRoleCard(
            role: UserRole.seller,
            title: 'Business Seller',
            description: 'List and sell your business',
            icon: Icons.business,
            isSelected: _selectedRole == UserRole.seller,
            onTap: () => setState(() => _selectedRole = UserRole.seller),
          ),
          
          const SizedBox(height: AppDimensions.spacing16),
          
          _buildRoleCard(
            role: UserRole.investor,
            title: 'Investor',
            description: 'Discover and invest in businesses',
            icon: Icons.trending_up,
            isSelected: _selectedRole == UserRole.investor,
            onTap: () => setState(() => _selectedRole = UserRole.investor),
          ),
        ],
      ),
    );
  }

  Widget _buildRoleCard({
    required UserRole role,
    required String title,
    required String description,
    required IconData icon,
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

  Widget _buildBasicInfoStep() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppDimensions.spacing24),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Basic Information',
              style: AppTypography.displaySmall.copyWith(
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: AppDimensions.spacing8),
            Text(
              'Tell us about yourself',
              style: AppTypography.bodyLarge.copyWith(
                color: AppColors.textSecondary,
              ),
            ),
            
            const SizedBox(height: AppDimensions.spacing32),
            
            Row(
              children: [
                Expanded(
                  child: CustomTextField(
                    label: 'First Name',
                    hint: 'Enter your first name',
                    controller: _firstNameController,
                    textInputAction: TextInputAction.next,
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'First name is required';
                      }
                      return null;
                    },
                  ),
                ),
                const SizedBox(width: AppDimensions.spacing16),
                Expanded(
                  child: CustomTextField(
                    label: 'Last Name',
                    hint: 'Enter your last name',
                    controller: _lastNameController,
                    textInputAction: TextInputAction.next,
                    validator: (value) {
                      if (value == null || value.isEmpty) {
                        return 'Last name is required';
                      }
                      return null;
                    },
                  ),
                ),
              ],
            ),
            
            const SizedBox(height: AppDimensions.spacing20),
            
            CustomTextField(
              label: 'Email',
              hint: 'Enter your email',
              controller: _emailController,
              keyboardType: TextInputType.emailAddress,
              textInputAction: TextInputAction.next,
              prefixIcon: Icons.email_outlined,
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Email is required';
                }
                if (!RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(value)) {
                  return 'Please enter a valid email';
                }
                return null;
              },
            ),
            
            const SizedBox(height: AppDimensions.spacing20),
            
            CustomTextField(
              label: 'Phone',
              hint: 'Enter your phone number',
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              textInputAction: TextInputAction.next,
              prefixIcon: Icons.phone_outlined,
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Phone number is required';
                }
                return null;
              },
            ),
            
            const SizedBox(height: AppDimensions.spacing20),
            
            PasswordField(
              label: 'Password',
              hint: 'Enter your password',
              controller: _passwordController,
              textInputAction: TextInputAction.next,
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Password is required';
                }
                if (value.length < 8) {
                  return 'Password must be at least 8 characters';
                }
                return null;
              },
            ),
            
            const SizedBox(height: AppDimensions.spacing20),
            
            PasswordField(
              label: 'Confirm Password',
              hint: 'Confirm your password',
              controller: _confirmPasswordController,
              textInputAction: TextInputAction.done,
              validator: (value) {
                if (value == null || value.isEmpty) {
                  return 'Please confirm your password';
                }
                if (value != _passwordController.text) {
                  return 'Passwords do not match';
                }
                return null;
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProfessionalInfoStep() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppDimensions.spacing24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            _selectedRole == UserRole.seller ? 'Business Information' : 'Investment Information',
            style: AppTypography.displaySmall.copyWith(
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: AppDimensions.spacing8),
          Text(
            _selectedRole == UserRole.seller 
                ? 'Tell us about your business'
                : 'Tell us about your investment preferences',
            style: AppTypography.bodyLarge.copyWith(
              color: AppColors.textSecondary,
            ),
          ),
          
          const SizedBox(height: AppDimensions.spacing32),
          
          if (_selectedRole == UserRole.seller) ...[
            CustomTextField(
              label: 'Business Name',
              hint: 'Enter your business name',
              controller: _businessNameController,
              textInputAction: TextInputAction.next,
              prefixIcon: Icons.business,
            ),
            
            const SizedBox(height: AppDimensions.spacing20),
            
            CustomTextField(
              label: 'Business Type',
              hint: 'e.g., Technology, Retail, Manufacturing',
              controller: _businessTypeController,
              textInputAction: TextInputAction.done,
              prefixIcon: Icons.category,
            ),
          ] else if (_selectedRole == UserRole.investor) ...[
            CustomTextField(
              label: 'Investment Amount',
              hint: 'e.g., \$10,000 - \$100,000',
              controller: _investmentAmountController,
              textInputAction: TextInputAction.next,
              prefixIcon: Icons.attach_money,
            ),
            
            const SizedBox(height: AppDimensions.spacing20),
            
            CustomTextField(
              label: 'Investment Focus',
              hint: 'e.g., Technology, Healthcare, Real Estate',
              controller: _investmentFocusController,
              textInputAction: TextInputAction.done,
              prefixIcon: Icons.trending_up,
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildVerificationStep() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppDimensions.spacing24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Terms & Verification',
            style: AppTypography.displaySmall.copyWith(
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: AppDimensions.spacing8),
          Text(
            'Please review and accept our terms',
            style: AppTypography.bodyLarge.copyWith(
              color: AppColors.textSecondary,
            ),
          ),
          
          const SizedBox(height: AppDimensions.spacing32),
          
          _buildCheckboxTile(
            value: _agreeToTerms,
            onChanged: (value) => setState(() => _agreeToTerms = value ?? false),
            title: 'I agree to the Terms of Service',
          ),
          
          const SizedBox(height: AppDimensions.spacing16),
          
          _buildCheckboxTile(
            value: _agreeToPrivacy,
            onChanged: (value) => setState(() => _agreeToPrivacy = value ?? false),
            title: 'I agree to the Privacy Policy',
          ),
          
          const SizedBox(height: AppDimensions.spacing16),
          
          _buildCheckboxTile(
            value: _confirmAge,
            onChanged: (value) => setState(() => _confirmAge = value ?? false),
            title: 'I confirm that I am at least 18 years old',
          ),
          
          const SizedBox(height: AppDimensions.spacing16),
          
          _buildCheckboxTile(
            value: _confirmIdentity,
            onChanged: (value) => setState(() => _confirmIdentity = value ?? false),
            title: 'I confirm my identity and information is accurate',
          ),
        ],
      ),
    );
  }

  Widget _buildCheckboxTile({
    required bool value,
    required ValueChanged<bool?> onChanged,
    required String title,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 24,
          height: 24,
          child: Checkbox(
            value: value,
            onChanged: onChanged,
            activeColor: AppColors.primaryPurple,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(AppDimensions.radiusSmall),
            ),
          ),
        ),
        const SizedBox(width: AppDimensions.spacing12),
        Expanded(
          child: Text(
            title,
            style: AppTypography.bodyMedium,
          ),
        ),
      ],
    );
  }
}