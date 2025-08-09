import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/constants/colors.dart';
import '../../../core/constants/dimensions.dart';
import '../../../core/constants/typography.dart';
import '../../../components/animations/pulse_button.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundLight,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(AppDimensions.spacing24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              Row(
                children: [
                  CircleAvatar(
                    radius: 24,
                    backgroundColor: AppColors.primaryPurple,
                    child: Text(
                      'LV',
                      style: AppTypography.titleMedium.copyWith(
                        color: AppColors.cardWhite,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                  const SizedBox(width: AppDimensions.spacing12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Hello!',
                        style: AppTypography.bodyMedium.copyWith(
                          color: AppColors.textSecondary,
                        ),
                      ),
                      Text(
                        'Livia Vaccaro',
                        style: AppTypography.titleLarge.copyWith(
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ],
                  ),
                  const Spacer(),
                  IconButton(
                    onPressed: () {},
                    icon: const Icon(Icons.notifications_outlined),
                    color: AppColors.textPrimary,
                  ),
                ],
              ),
              
              const SizedBox(height: AppDimensions.spacing32),
              
              // Progress Card
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(AppDimensions.spacing24),
                decoration: BoxDecoration(
                  gradient: AppColors.primaryGradient,
                  borderRadius: BorderRadius.circular(AppDimensions.radiusXLarge),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Your today\'s task\nalmost done!',
                                style: AppTypography.titleLarge.copyWith(
                                  color: AppColors.cardWhite,
                                  fontWeight: FontWeight.w700,
                                  height: 1.3,
                                ),
                              ),
                              const SizedBox(height: AppDimensions.spacing16),
            PulseButton(
              onTap: () {},
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: AppDimensions.spacing16,
                  vertical: AppDimensions.spacing8,
                ),
                decoration: BoxDecoration(
                  color: AppColors.cardWhite.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(AppDimensions.radiusMedium),
                ),
                child: Text(
                  'View Task',
                  style: AppTypography.labelLarge.copyWith(
                    color: AppColors.cardWhite,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
                            ],
                          ),
                        ),
                        const SizedBox(width: AppDimensions.spacing16),
                        Stack(
                          alignment: Alignment.center,
                          children: [
                            SizedBox(
                              width: 80,
                              height: 80,
                              child: CircularProgressIndicator(
                                value: 0.85,
                                strokeWidth: 8,
                                backgroundColor: AppColors.cardWhite.withValues(alpha: 0.3),
                                valueColor: const AlwaysStoppedAnimation<Color>(AppColors.cardWhite),
                              ),
                            ),
                            Text(
                              '85%',
                              style: AppTypography.titleLarge.copyWith(
                                color: AppColors.cardWhite,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              
              const SizedBox(height: AppDimensions.spacing32),

              // Quick Actions
              Row(
                children: [
                  Expanded(
                    child: _QuickActionCard(
                      title: 'Marketplace',
                      subtitle: 'Explore Opportunities',
                      icon: Icons.business_center,
                      color: Colors.blue,
                      onTap: () => context.push('/marketplace'),
                    ),
                  ),
                  const SizedBox(width: AppDimensions.spacing16),
                  Expanded(
                    child: _QuickActionCard(
                      title: 'Portfolio',
                      subtitle: 'Your Investments',
                      icon: Icons.trending_up,
                      color: Colors.green,
                      onTap: () => context.push('/portfolio'),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: AppDimensions.spacing32),
              
              // In Progress Section
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'In Progress',
                    style: AppTypography.headlineSmall.copyWith(
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  Text(
                    '6',
                    style: AppTypography.titleMedium.copyWith(
                      color: AppColors.textSecondary,
                    ),
                  ),
                ],
              ),
              
              const SizedBox(height: AppDimensions.spacing16),
              
              // Task Cards
              Expanded(
                child: ListView(
                  children: [
                    _TaskCard(
                      title: 'Grocery shopping app design',
                      subtitle: 'Office Project',
                      progress: 0.7,
                      color: AppColors.accentOrange,
                    ),
                    const SizedBox(height: AppDimensions.spacing16),
                    _TaskCard(
                      title: 'Uber Eats redesign challenge',
                      subtitle: 'Personal Project',
                      progress: 0.52,
                      color: AppColors.primaryPurple,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _QuickActionCard extends StatelessWidget {
  const _QuickActionCard({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.color,
    required this.onTap,
  });

  final String title;
  final String subtitle;
  final IconData icon;
  final Color color;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(AppDimensions.spacing16),
        decoration: BoxDecoration(
          color: AppColors.cardWhite,
          borderRadius: BorderRadius.circular(AppDimensions.radiusLarge),
          boxShadow: [
            BoxShadow(
              color: AppColors.textPrimary.withValues(alpha: 0.06),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(
                icon,
                color: color,
                size: 24,
              ),
            ),
            const SizedBox(height: AppDimensions.spacing12),
            Text(
              title,
              style: AppTypography.titleSmall.copyWith(
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              subtitle,
              style: AppTypography.bodySmall.copyWith(
                color: AppColors.textSecondary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _TaskCard extends StatelessWidget {
  const _TaskCard({
    required this.title,
    required this.subtitle,
    required this.progress,
    required this.color,
  });

  final String title;
  final String subtitle;
  final double progress;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(AppDimensions.spacing20),
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
          Row(
            children: [
              Container(
                width: 12,
                height: 12,
                decoration: BoxDecoration(
                  color: color,
                  shape: BoxShape.circle,
                ),
              ),
              const SizedBox(width: AppDimensions.spacing8),
              Text(
                subtitle,
                style: AppTypography.bodyMedium.copyWith(
                  color: AppColors.textSecondary,
                ),
              ),
            ],
          ),
          const SizedBox(height: AppDimensions.spacing8),
          Text(
            title,
            style: AppTypography.titleMedium.copyWith(
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: AppDimensions.spacing12),
          LinearProgressIndicator(
            value: progress,
            backgroundColor: AppColors.borderLight,
            valueColor: AlwaysStoppedAnimation<Color>(color),
          ),
        ],
      ),
    );
  }
}
