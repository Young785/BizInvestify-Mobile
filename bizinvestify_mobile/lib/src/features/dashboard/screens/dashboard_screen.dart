import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/constants/app_dimensions.dart';
import '../../../shared/widgets/buttons/primary_button.dart';
import '../../../shared/widgets/cards/modern_card.dart';
import '../../../shared/widgets/navigation/modern_bottom_nav.dart';
import '../../marketplace/screens/marketplace_screen.dart';
import '../../messaging/screens/messages_screen.dart';
import '../../profile/screens/profile_screen.dart';
import '../providers/dashboard_provider.dart';
import '../../notifications/screens/notifications_screen.dart';
import '../../../core/services/api_service.dart';

class DashboardScreen extends ConsumerStatefulWidget {
  const DashboardScreen({super.key});

  @override
  ConsumerState<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends ConsumerState<DashboardScreen> {
  int _currentIndex = 0;
  int _unreadMessages = 0;
  int _unreadNotifications = 0;

  final List<Widget> _pages = [
    const DashboardContent(),
    const MarketplaceScreen(),
    const MessagesScreen(),
    const ProfileScreen(),
  ];

  @override
  void initState() {
    super.initState();
    // Schedule after first frame to avoid build-phase state changes
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _refreshBadges();
    });
  }

  Future<void> _refreshBadges() async {
    try {
      final msg = await apiService.getUnreadCount();
      final noti = await apiService.getNotificationUnreadCount();
      if (mounted) {
        setState(() {
          _unreadMessages = msg;
          _unreadNotifications = noti;
        });
      }
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundPrimary,
      body: _pages[_currentIndex],
      bottomNavigationBar: ModernBottomNav(
        currentIndex: _currentIndex,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
          _refreshBadges();
        },
        items: [
          const ModernBottomNavItem(
            icon: Icons.dashboard_outlined,
            activeIcon: Icons.dashboard,
            label: 'Dashboard',
          ),
          const ModernBottomNavItem(
            icon: Icons.store_outlined,
            activeIcon: Icons.store,
            label: 'Marketplace',
          ),
          ModernBottomNavItem(
            icon: Icons.chat_outlined,
            activeIcon: Icons.chat,
            label: 'Messages',
            badgeCount: _unreadMessages > 0 ? _unreadMessages : null,
          ),
          const ModernBottomNavItem(
            icon: Icons.person_outlined,
            activeIcon: Icons.person,
            label: 'Profile',
          ),
        ],
      ),
      floatingActionButton: Container(
        decoration: BoxDecoration(
          gradient: AppColors.primaryGradient,
          borderRadius: BorderRadius.circular(AppDimensions.spacing32),
          boxShadow: [
            BoxShadow(
              color: AppColors.shadowPrimary,
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: FloatingActionButton(
          onPressed: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (context) => const NotificationsScreen(),
              ),
            );
          },
          backgroundColor: Colors.transparent,
          elevation: 0,
          child: Badge(
            isLabelVisible: _unreadNotifications > 0,
            label: Text(
              '$_unreadNotifications',
              style: AppTypography.labelSmall.copyWith(
                color: AppColors.white,
              ),
            ),
            child: const Icon(
              Icons.notifications_outlined,
              color: AppColors.white,
            ),
          ),
        ),
      ),
    );
  }
}

class _Badge extends StatelessWidget {
  final int count;
  const _Badge({required this.count});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
      decoration: BoxDecoration(
        color: Colors.red,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        count > 99 ? '99+' : '$count',
        style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
      ),
    );
  }
}

class DashboardContent extends ConsumerStatefulWidget {
  const DashboardContent({super.key});

  @override
  ConsumerState<DashboardContent> createState() => _DashboardContentState();
}

class _DashboardContentState extends ConsumerState<DashboardContent> {
  @override
  void initState() {
    super.initState();
    _loadAnalytics();
  }

  void _loadAnalytics() {
    // Defer provider mutation to a microtask to respect Riverpod rules
    Future.microtask(() => ref.read(dashboardProvider.notifier).loadAnalytics());
  }

  @override
  Widget build(BuildContext context) {
    final dashboardState = ref.watch(dashboardProvider);
    final analytics = dashboardState.analytics;
    
    return CustomScrollView(
      slivers: [
        // Modern App Bar
        SliverAppBar(
          expandedHeight: 140,
          floating: false,
          pinned: true,
          backgroundColor: AppColors.backgroundSecondary,
          elevation: 0,
          flexibleSpace: FlexibleSpaceBar(
            title: null,
            titlePadding: const EdgeInsets.only(
              left: AppDimensions.containerPaddingMobile,
              bottom: AppDimensions.spacing16,
            ),
            background: Container(
              decoration: BoxDecoration(
                gradient: AppColors.primaryGradient,
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(AppDimensions.borderRadiusXLarge),
                  bottomRight: Radius.circular(AppDimensions.borderRadiusXLarge),
                ),
              ),
              child: Container(
                padding: const EdgeInsets.all(AppDimensions.containerPaddingMobile),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.end,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Welcome back, User!',
                      style: AppTypography.headlineMedium.copyWith(
                        color: AppColors.white,
                      ),
                    ),
                    const SizedBox(height: AppDimensions.spacing8),
                    Text(
                      "Here's what's happening with your business today.",
                      style: AppTypography.bodyMedium.copyWith(
                        color: AppColors.white.withOpacity(0.9),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          actions: [
            Container(
              margin: const EdgeInsets.only(right: AppDimensions.spacing8),
              child: IconButton(
                icon: Icon(
                  Icons.search,
                  color: AppColors.white,
                  size: AppDimensions.iconSizeLarge,
                ),
                onPressed: () {
                  // Navigate to search
                },
              ),
            ),
          ],
        ),
        
                // Dashboard Content
        SliverPadding(
          padding: const EdgeInsets.fromLTRB(
            AppDimensions.containerPaddingMobile,
            AppDimensions.spacing24,
            AppDimensions.containerPaddingMobile,
            AppDimensions.spacing24,
          ),
          sliver: SliverList(
            delegate: SliverChildListDelegate([
              // Stats Cards
              if (dashboardState.isLoading)
                const Center(
                  child: Padding(
                    padding: EdgeInsets.all(AppDimensions.spacing48),
                    child: CircularProgressIndicator(),
                  ),
                )
              else
                GridView.count(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  crossAxisCount: 2,
                  crossAxisSpacing: AppDimensions.spacing16,
                  mainAxisSpacing: AppDimensions.spacing16,
                  childAspectRatio: 1.4,
                  children: [
                    DashboardCard(
                      title: 'Total Revenue',
                      value: '\$${analytics?.totalRevenue.toStringAsFixed(0) ?? '0'}',
                      icon: Icons.trending_up,
                      trend: TrendType.up,
                      trendValue: '+12.5%',
                      gradient: AppColors.primaryGradient,
                    ),
                    DashboardCard(
                      title: 'Total Products',
                      value: '${analytics?.totalProducts ?? 0}',
                      icon: Icons.inventory_2_outlined,
                      trend: TrendType.up,
                      trendValue: '+8.2%',
                      backgroundColor: AppColors.success100,
                    ),
                    DashboardCard(
                      title: 'Active Listings',
                      value: '${analytics?.activeListings ?? 0}',
                      icon: Icons.store_outlined,
                      trend: TrendType.up,
                      trendValue: '+5.1%',
                      backgroundColor: AppColors.purple100,
                    ),
                    DashboardCard(
                      title: 'Messages',
                      value: '${analytics?.unreadMessages ?? 0}',
                      icon: Icons.chat_outlined,
                      trend: TrendType.neutral,
                      trendValue: 'New',
                      backgroundColor: AppColors.primary100,
                    ),
                  ],
                ),
              
              const SizedBox(height: AppDimensions.spacing32),
              
              // Quick Actions Header
              Padding(
                padding: const EdgeInsets.only(bottom: AppDimensions.spacing16),
                child: Text(
                  'Quick Actions',
                  style: AppTypography.headlineSmall.copyWith(
                    color: AppColors.textPrimary,
                  ),
                ),
              ),
              
              // Quick Actions Grid
              GridView.count(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                crossAxisCount: 2,
                crossAxisSpacing: AppDimensions.spacing16,
                mainAxisSpacing: AppDimensions.spacing16,
                childAspectRatio: 1.1,
                children: [
                  QuickActionCard(
                    title: 'Add Product',
                    subtitle: 'List a new product',
                    icon: Icons.add_shopping_cart_outlined,
                    backgroundColor: AppColors.primary100,
                    iconColor: AppColors.primary500,
                    onTap: () {
                      // Navigate to add product
                    },
                  ),
                  QuickActionCard(
                    title: 'List Business',
                    subtitle: 'Seek investment',
                    icon: Icons.business_outlined,
                    backgroundColor: AppColors.success100,
                    iconColor: AppColors.success500,
                    onTap: () {
                      // Navigate to list business
                    },
                  ),
                  QuickActionCard(
                    title: 'Messages',
                    subtitle: '5 unread',
                    icon: Icons.chat_outlined,
                    backgroundColor: AppColors.purple100,
                    iconColor: AppColors.purple500,
                    onTap: () {
                      // Navigate to messages
                    },
                  ),
                  QuickActionCard(
                    title: 'Analytics',
                    subtitle: 'View performance',
                    icon: Icons.analytics_outlined,
                    backgroundColor: AppColors.neutral200,
                    iconColor: AppColors.neutral700,
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
      padding: const EdgeInsets.all(16),
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
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(AppDimensions.borderRadiusSmall),
                ),
                child: Icon(
                  icon,
                  color: color,
                  size: 20,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 6,
                  vertical: 2,
                ),
                decoration: BoxDecoration(
                  color: trendUp
                      ? Colors.green.withOpacity(0.1)
                      : Colors.blue.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      trendUp ? Icons.trending_up : Icons.info,
                      size: 10,
                      color: trendUp ? Colors.green : Colors.blue,
                    ),
                    const SizedBox(width: 2),
                    Text(
                      trend,
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: AppTypography.medium,
                        color: trendUp ? Colors.green : Colors.blue,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  value,
                  style: AppTypography.titleLarge.copyWith(
                    fontWeight: AppTypography.bold,
                    color: AppColors.text800,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  title,
                  style: AppTypography.bodySmall.copyWith(
                    color: AppColors.text600,
                  ),
                ),
              ],
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
        padding: const EdgeInsets.all(16),
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
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
              ),
              child: Icon(
                icon,
                color: color,
                size: 24,
              ),
            ),
            const SizedBox(height: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: AppTypography.titleSmall.copyWith(
                      fontWeight: AppTypography.semibold,
                      color: AppColors.text800,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    subtitle,
                    style: AppTypography.captionMedium.copyWith(
                      color: AppColors.text600,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
