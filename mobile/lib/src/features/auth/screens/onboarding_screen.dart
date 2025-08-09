import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants/colors.dart';
import '../../../core/constants/dimensions.dart';
import '../../../core/constants/typography.dart';
import '../../../core/constants/routes.dart';
import '../../../components/buttons/primary_button.dart';

/// Onboarding screen with beautiful gradient and 3D illustration
class OnboardingScreen extends StatelessWidget {
  const OnboardingScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              Color(0xFFF3F0FF), // Light purple tint
              Color(0xFFFAF9FF), // Very light purple
              AppColors.cardWhite,
            ],
            stops: [0.0, 0.5, 1.0],
          ),
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(AppDimensions.spacing24),
            child: Column(
              children: [
                const Spacer(flex: 2),
                
                // 3D Illustration Area
                Container(
                  height: 280,
                  width: double.infinity,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(AppDimensions.radiusXLarge),
                    gradient: const RadialGradient(
                      center: Alignment.center,
                      radius: 1.0,
                      colors: [
                        Color(0xFFF8F6FF),
                        Color(0xFFEDE9FE),
                      ],
                    ),
                  ),
                  child: Stack(
                    alignment: Alignment.center,
                    children: [
                      // Floating decorative elements
                      Positioned(
                        top: 30,
                        right: 40,
                        child: _FloatingElement(
                          color: AppColors.accentOrange,
                          size: 12,
                        ),
                      ),
                      Positioned(
                        top: 60,
                        left: 30,
                        child: _FloatingElement(
                          color: AppColors.successGreen,
                          size: 8,
                        ),
                      ),
                      Positioned(
                        bottom: 40,
                        left: 50,
                        child: _FloatingElement(
                          color: AppColors.infoBlue,
                          size: 10,
                        ),
                      ),
                      Positioned(
                        bottom: 60,
                        right: 30,
                        child: _FloatingElement(
                          color: AppColors.warningYellow,
                          size: 6,
                        ),
                      ),
                      
                      // Central character illustration (placeholder)
                      Container(
                        width: 160,
                        height: 160,
                        decoration: BoxDecoration(
                          color: AppColors.primaryPurple.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(AppDimensions.radiusXXLarge),
                        ),
                        child: Stack(
                          alignment: Alignment.center,
                          children: [
                            // Main character (simplified)
                            Container(
                              width: 120,
                              height: 120,
                              decoration: BoxDecoration(
                                color: AppColors.primaryPurple,
                                borderRadius: BorderRadius.circular(AppDimensions.radiusLarge),
                              ),
                              child: const Icon(
                                Icons.person,
                                size: 60,
                                color: AppColors.cardWhite,
                              ),
                            ),
                            
                            // Laptop/work elements
                            Positioned(
                              bottom: 10,
                              right: 10,
                              child: Container(
                                width: 40,
                                height: 30,
                                decoration: BoxDecoration(
                                  color: AppColors.textSecondary,
                                  borderRadius: BorderRadius.circular(AppDimensions.radiusSmall),
                                ),
                                child: const Icon(
                                  Icons.laptop_mac,
                                  size: 20,
                                  color: AppColors.cardWhite,
                                ),
                              ),
                            ),
                            
                            // Task/document elements
                            Positioned(
                              top: 10,
                              left: 10,
                              child: Container(
                                width: 30,
                                height: 30,
                                decoration: BoxDecoration(
                                  color: AppColors.accentOrange,
                                  borderRadius: BorderRadius.circular(AppDimensions.radiusSmall),
                                ),
                                child: const Icon(
                                  Icons.task_alt,
                                  size: 16,
                                  color: AppColors.cardWhite,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                
                const Spacer(),
                
                // Title and Description
                Text(
                  'Task Management &\nTo-Do List',
                  textAlign: TextAlign.center,
                  style: AppTypography.displaySmall.copyWith(
                    fontWeight: FontWeight.w800,
                    height: 1.2,
                  ),
                ),
                
                const SizedBox(height: AppDimensions.spacing16),
                
                Text(
                  'This productive tool is designed to help\nyou better manage your task\nproject-wise conveniently!',
                  textAlign: TextAlign.center,
                  style: AppTypography.bodyLarge.copyWith(
                    color: AppColors.textSecondary,
                    height: 1.5,
                  ),
                ),
                
                const Spacer(),
                
                // CTA Button
                PrimaryButton(
                  text: "Let's Start",
                  onPressed: () {
                    context.go(AppRoutes.login);
                  },
                  width: double.infinity,
                  size: ButtonSize.large,
                  icon: Icons.arrow_forward,
                ),
                
                const SizedBox(height: AppDimensions.spacing24),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// Floating decorative element widget
class _FloatingElement extends StatelessWidget {
  const _FloatingElement({
    required this.color,
    required this.size,
  });

  final Color color;
  final double size;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: color,
        shape: BoxShape.circle,
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.3),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
    );
  }
}
