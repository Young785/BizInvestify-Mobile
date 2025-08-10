import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/constants/app_dimensions.dart';
import '../../../shared/widgets/buttons/primary_button.dart';

class DashboardScreen extends ConsumerWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      backgroundColor: AppColors.background200,
      body: CustomScrollView(
        slivers: [
          // App Bar
          SliverAppBar(
            expandedHeight: 120,
            floating: false,
            pinned: true,
            backgroundColor: AppColors.primary500,
            flexibleSpace: FlexibleSpaceBar(
              title: Text(
                'Dashboard',
                style: AppTypography.headlineSmall.copyWith(
                  color: Colors.white,
                  fontWeight: AppTypography.bold,
                ),
              ),
              background: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [AppColors.primary500, AppColors.accent500],
                  ),
                ),
              ),
            ),
            actions: [
              IconButton(
                icon: const Icon(Icons.notifications, color: Colors.white),
                onPressed: () {
                  // Navigate to notifications
                },
              ),
              IconButton(
                icon: const Icon(Icons.person, color: Colors.white),
                onPressed: () {
                  // Navigate to profile
                },
              ),
            ],
          ),
          
          // Dashboard Content
          SliverPadding(
            padding: const EdgeInsets.all(AppDimensions.spacing16),
            sliver: SliverList(
              delegate: SliverChildListDelegate([
                // Welcome Message
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Colors.white, AppColors.primary50],
                    ),
                    borderRadius: BorderRadius.circular(AppDimensions.borderRadiusLarge),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.1),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Welcome back, User!',
                        style: AppTypography.headlineSmall.copyWith(
                          fontWeight: AppTypography.bold,
                        ),
                      ),
                      const SizedBox(height: AppDimensions.spacing8),
                      Text(
                        'Here\'s what\'s happening with your business today.',
                        style: AppTypography.bodyMedium.copyWith(
                          color: AppColors.text600,
                        ),
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(height: AppDimensions.spacing24),
                
                // Stats Cards
                GridView.count(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  crossAxisCount: 2,
                  crossAxisSpacing: AppDimensions.spacing16,
                  mainAxisSpacing: AppDimensions.spacing16,
                  childAspectRatio: 1.5,
                  children: [
                    _buildStatsCard(
                      title: 'Total Revenue',
                      value: '\$12,450',
                      icon: Icons.attach_money,
                      color: AppColors.accent500,
                      trend: '+12.5%',
                      trendUp: true,
                    ),
                    _buildStatsCard(
                      title: 'Total Products',
                      value: '24',
                      icon: Icons.inventory,
                      color: AppColors.primary500,
                      trend: '+8.2%',
                      trendUp: true,
                    ),
                    _buildStatsCard(
                      title: 'Active Listings',
                      value: '18',
                      icon: Icons.store,
                      color: AppColors.accent600,
                      trend: '+5.1%',
                      trendUp: true,
                    ),
                    _buildStatsCard(
                      title: 'Messages',
                      value: '5',
                      icon: Icons.message,
                      color: AppColors.primary600,
                      trend: 'New',
                      trendUp: false,
                    ),
                  ],
                ),
                
                const SizedBox(height: AppDimensions.spacing24),
                
                // Quick Actions
                Text(
                  'Quick Actions',
                  style: AppTypography.titleLarge.copyWith(
                    fontWeight: AppTypography.bold,
                  ),
                ),
                const SizedBox(height: AppDimensions.spacing16),
                GridView.count(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  crossAxisCount: 2,
                  crossAxisSpacing: AppDimensions.spacing16,
                  mainAxisSpacing: AppDimensions.spacing16,
                  childAspectRatio: 1.2,
                  children: [
                    _buildQuickActionCard(
                      title: 'Add Product',
                      subtitle: 'List a new product',
                      icon: Icons.add_shopping_cart,
                      color: AppColors.primary500,
                      onTap: () {
                        // Navigate to add product
                      },
                    ),
                    _buildQuickActionCard(
                      title: 'List Business',
                      subtitle: 'Seek investment',
                      icon: Icons.business,
                      color: AppColors.accent500,
                      onTap: () {
                        // Navigate to list business
                      },
                    ),
                    _buildQuickActionCard(
                      title: 'Messages',
                      subtitle: '5 unread',
                      icon: Icons.message,
                      color: AppColors.primary600,
                      onTap: () {
                        // Navigate to messages
                      },
                    ),
                    _buildQuickActionCard(
                      title: 'Analytics',
                      subtitle: 'View performance',
                      icon: Icons.analytics,
                      color: AppColors.accent600,
                      onTap: () {
                        // Navigate to analytics
                      },
                    ),
                  ],
                ),
              ]),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatsCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
    required String trend,
    required bool trendUp,
  }) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(AppDimensions.borderRadiusLarge),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.all(AppDimensions.spacing8),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(AppDimensions.borderRadiusSmall),
                ),
                child: Icon(
                  icon,
                  color: color,
                  size: AppDimensions.iconSizeMedium,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: AppDimensions.spacing8,
                  vertical: AppDimensions.spacing4,
                ),
                decoration: BoxDecoration(
                  color: trendUp
                      ? Colors.green.withOpacity(0.1)
                      : Colors.blue.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(AppDimensions.borderRadiusSmall),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      trendUp ? Icons.trending_up : Icons.info,
                      size: 12,
                      color: trendUp ? Colors.green : Colors.blue,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      trend,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: AppTypography.medium,
                        color: trendUp ? Colors.green : Colors.blue,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: AppDimensions.spacing16),
          Text(
            value,
            style: AppTypography.headlineSmall.copyWith(
              fontWeight: AppTypography.bold,
              color: AppColors.text800,
            ),
          ),
          const SizedBox(height: AppDimensions.spacing4),
          Text(
            title,
            style: AppTypography.bodyMedium.copyWith(
              color: AppColors.text600,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickActionCard({
    required String title,
    required String subtitle,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusLarge),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(AppDimensions.spacing12),
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
              ),
              child: Icon(
                icon,
                color: color,
                size: AppDimensions.iconSizeLarge,
              ),
            ),
            const SizedBox(height: AppDimensions.spacing16),
            Text(
              title,
              style: AppTypography.titleMedium.copyWith(
                fontWeight: AppTypography.semibold,
                color: AppColors.text800,
              ),
            ),
            const SizedBox(height: AppDimensions.spacing4),
            Text(
              subtitle,
              style: AppTypography.bodySmall.copyWith(
                color: AppColors.text600,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
