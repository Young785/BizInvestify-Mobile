import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/theme/app_theme.dart';

import '../../marketplace/screens/marketplace_screen.dart';
import '../../messaging/screens/messages_screen.dart';
import '../../profile/screens/profile_screen.dart';
import '../providers/dashboard_provider.dart';
import '../../notifications/screens/notifications_screen.dart';
import '../../../core/services/api_service.dart';

/// Professional Dashboard Screen
/// Modern, elegant design following BizInvestify brand guidelines
class DashboardScreen extends ConsumerStatefulWidget {
  const DashboardScreen({super.key});

  @override
  ConsumerState<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends ConsumerState<DashboardScreen>
    with TickerProviderStateMixin {
  int _currentIndex = 0;
  int _unreadMessages = 0;
  
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;

  final List<Widget> _pages = [
    const DashboardContent(),
    const MarketplaceScreen(),
    const MessagesScreen(),
    const ProfileScreen(),
  ];

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );
    _fadeAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeInOut,
    ));
    
    _animationController.forward();
    _refreshBadges();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  Future<void> _refreshBadges() async {
    try {
      final msg = await apiService.getUnreadCount();
      if (mounted) {
        setState(() {
          _unreadMessages = msg;
        });
      }
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      
      body: FadeTransition(
        opacity: _fadeAnimation,
        child: _pages[_currentIndex],
      ),
      
      bottomNavigationBar: _buildModernBottomNavigation(),
      
      floatingActionButton: _buildModernFAB(),
      floatingActionButtonLocation: FloatingActionButtonLocation.endFloat,
    );
  }

  /// Modern Bottom Navigation with Professional Styling
  Widget _buildModernBottomNavigation() {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.backgroundElevated,
        borderRadius: const BorderRadius.vertical(
          top: Radius.circular(24.0),
        ),
        boxShadow: [
          BoxShadow(
            color: AppColors.shadowLg,
            blurRadius: 20.0,
            offset: const Offset(0, -4),
            spreadRadius: 0,
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: const BorderRadius.vertical(
          top: Radius.circular(24.0),
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: (index) {
            setState(() {
              _currentIndex = index;
            });
            _refreshBadges();
            
            // Haptic feedback
            HapticFeedback.lightImpact();
          },
          
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.transparent,
          elevation: 0,
          
          selectedItemColor: AppColors.primary500,
          unselectedItemColor: AppColors.textTertiary,
          
          selectedLabelStyle: AppTypography.labelSmall.copyWith(
            fontWeight: AppTypography.semibold,
            color: AppColors.primary500,
          ),
          unselectedLabelStyle: AppTypography.labelSmall.copyWith(
            fontWeight: AppTypography.medium,
            color: AppColors.textTertiary,
          ),
          
          items: [
            BottomNavigationBarItem(
              icon: _buildNavIcon(
                Icons.dashboard_rounded,
                _currentIndex == 0,
              ),
              labelText: 'Dashboard',
            ),
            BottomNavigationBarItem(
              icon: _buildNavIcon(
                Icons.store_rounded,
                _currentIndex == 1,
              ),
              labelText: 'Marketplace',
            ),
            BottomNavigationBarItem(
              icon: _buildNavIconWithBadge(
                Icons.chat_rounded,
                _currentIndex == 2,
                _unreadMessages,
              ),
              labelText: 'Messages',
            ),
            BottomNavigationBarItem(
              icon: _buildNavIcon(
                Icons.person_rounded,
                _currentIndex == 3,
              ),
              labelText: 'Profile',
            ),
          ],
        ),
      ),
    );
  }

  /// Professional Navigation Icon with Animation
  Widget _buildNavIcon(IconData icon, bool isSelected) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      padding: EdgeInsets.all(isSelected ? 8.0 : 4.0),
      decoration: BoxDecoration(
        color: isSelected 
            ? AppColors.primary500.withOpacity(0.1)
            : Colors.transparent,
        borderRadius: BorderRadius.circular(12.0),
      ),
      child: Icon(
        icon,
        size: isSelected ? 26.0 : 24.0,
        color: isSelected 
            ? AppColors.primary500 
            : AppColors.textTertiary,
      ),
    );
  }

  /// Navigation Icon with Professional Badge
  Widget _buildNavIconWithBadge(IconData icon, bool isSelected, int count) {
    return Stack(
      clipBehavior: Clip.none,
      children: [
        _buildNavIcon(icon, isSelected),
        if (count > 0)
          Positioned(
            right: -2,
            top: -2,
            child: Container(
              padding: const EdgeInsets.symmetric(
                horizontal: 6.0,
                vertical: 2.0,
              ),
              decoration: BoxDecoration(
                color: AppColors.error500,
                borderRadius: BorderRadius.circular(10.0),
                border: Border.all(
                  color: AppColors.backgroundElevated,
                  width: 2.0,
                ),
              ),
              constraints: const BoxConstraints(
                minWidth: 18.0,
                minHeight: 18.0,
              ),
              child: Text(
                count > 99 ? '99+' : '$count',
                style: AppTypography.captionSmall.copyWith(
                  color: AppColors.textInverse,
                  fontWeight: AppTypography.bold,
                  fontSize: 10.0,
                ),
                textAlign: TextAlign.center,
              ),
            ),
          ),
      ],
    );
  }

  /// Modern Floating Action Button
  Widget _buildModernFAB() {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16.0),
        gradient: AppColors.brandPrimary,
        boxShadow: [
          BoxShadow(
            color: AppColors.shadowPrimary,
            blurRadius: 16.0,
            offset: const Offset(0, 4),
            spreadRadius: 0,
          ),
        ],
      ),
      child: FloatingActionButton(
        onPressed: () {
          _showAddOptionsBottomSheet();
        },
        backgroundColor: Colors.transparent,
        elevation: 0,
        child: const Icon(
          Icons.add_rounded,
          color: AppColors.textInverse,
          size: 28.0,
        ),
      ),
    );
  }

  /// Professional Add Options Bottom Sheet
  void _showAddOptionsBottomSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        decoration: const BoxDecoration(
          color: AppColors.backgroundElevated,
          borderRadius: BorderRadius.vertical(
            top: Radius.circular(24.0),
          ),
        ),
        padding: const EdgeInsets.fromLTRB(24.0, 20.0, 24.0, 40.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Handle
            Container(
              width: 40.0,
              height: 4.0,
              decoration: BoxDecoration(
                color: AppColors.surfaceBorder,
                borderRadius: BorderRadius.circular(2.0),
              ),
            ),
            
            const SizedBox(height: 24.0),
            
            Text(
              'Add New',
              style: AppTypography.headlineSmall.copyWith(
                fontWeight: AppTypography.bold,
              ),
            ),
            
            const SizedBox(height: 24.0),
            
            Row(
              children: [
                Expanded(
                  child: _buildAddOptionCard(
                    icon: Icons.inventory_rounded,
                    title: 'Product',
                    subtitle: 'List a new product',
                    gradient: AppColors.brandPrimary,
                    onTap: () {
                      Navigator.pop(context);
                      // Navigate to add product
                    },
                  ),
                ),
                const SizedBox(width: 16.0),
                Expanded(
                  child: _buildAddOptionCard(
                    icon: Icons.business_center_rounded,
                    title: 'Business',
                    subtitle: 'Seek investment',
                    gradient: AppColors.successGradient,
                    onTap: () {
                      Navigator.pop(context);
                      // Navigate to add business
                    },
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAddOptionCard({
    required IconData icon,
    required String title,
    required String subtitle,
    required Gradient gradient,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(20.0),
        decoration: BoxDecoration(
          gradient: gradient,
          borderRadius: BorderRadius.circular(16.0),
          boxShadow: [
            BoxShadow(
              color: AppColors.shadowMd,
              blurRadius: 12.0,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          children: [
            Icon(
              icon,
              color: AppColors.textInverse,
              size: 32.0,
            ),
            const SizedBox(height: 12.0),
            Text(
              title,
              style: AppTypography.titleSmall.copyWith(
                color: AppColors.textInverse,
                fontWeight: AppTypography.bold,
              ),
            ),
            const SizedBox(height: 4.0),
            Text(
              subtitle,
              style: AppTypography.captionMedium.copyWith(
                color: AppColors.textInverse.withOpacity(0.9),
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}

/// Professional Dashboard Content
class DashboardContent extends ConsumerStatefulWidget {
  const DashboardContent({super.key});

  @override
  ConsumerState<DashboardContent> createState() => _DashboardContentState();
}

class _DashboardContentState extends ConsumerState<DashboardContent>
    with TickerProviderStateMixin {
  late AnimationController _headerAnimationController;
  late AnimationController _cardsAnimationController;
  late Animation<Offset> _headerSlideAnimation;
  late Animation<double> _cardsScaleAnimation;

  @override
  void initState() {
    super.initState();
    _setupAnimations();
    _loadAnalytics();
  }

  void _setupAnimations() {
    _headerAnimationController = AnimationController(
      duration: const Duration(milliseconds: 600),
      vsync: this,
    );
    
    _cardsAnimationController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );

    _headerSlideAnimation = Tween<Offset>(
      begin: const Offset(0, -0.5),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _headerAnimationController,
      curve: Curves.easeOutCubic,
    ));

    _cardsScaleAnimation = Tween<double>(
      begin: 0.8,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _cardsAnimationController,
      curve: Curves.elasticOut,
    ));

    _headerAnimationController.forward();
    Future.delayed(const Duration(milliseconds: 300), () {
      _cardsAnimationController.forward();
    });
  }

  @override
  void dispose() {
    _headerAnimationController.dispose();
    _cardsAnimationController.dispose();
    super.dispose();
  }

  void _loadAnalytics() {
    ref.read(dashboardProvider.notifier).loadAnalytics();
  }

  @override
  Widget build(BuildContext context) {
    final dashboardState = ref.watch(dashboardProvider);
    final analytics = dashboardState.analytics;
    
    return RefreshIndicator(
      onRefresh: () async {
        _loadAnalytics();
      },
      color: AppColors.primary500,
      backgroundColor: AppColors.backgroundElevated,
      child: CustomScrollView(
        physics: const BouncingScrollPhysics(),
        slivers: [
          // Professional App Bar
          _buildProfessionalAppBar(),
          
          // Dashboard Content
          SliverPadding(
            padding: const EdgeInsets.fromLTRB(20.0, 0, 20.0, 100.0),
            sliver: SliverList(
              delegate: SliverChildListDelegate([
                const SizedBox(height: 24.0),
                
                // Welcome Section
                SlideTransition(
                  position: _headerSlideAnimation,
                  child: _buildWelcomeSection(analytics),
                ),
                
                const SizedBox(height: 32.0),
                
                // Analytics Cards
                ScaleTransition(
                  scale: _cardsScaleAnimation,
                  child: _buildAnalyticsSection(dashboardState, analytics),
                ),
                
                const SizedBox(height: 32.0),
                
                // Quick Actions
                _buildQuickActionsSection(),
                
                const SizedBox(height: 32.0),
                
                // Recent Activity
                _buildRecentActivitySection(),
              ]),
            ),
          ),
        ],
      ),
    );
  }

  /// Professional App Bar with Gradient Background
  Widget _buildProfessionalAppBar() {
    return SliverAppBar(
      expandedHeight: 120.0,
      floating: false,
      pinned: true,
      elevation: 0,
      scrolledUnderElevation: 8.0,
      
      flexibleSpace: FlexibleSpaceBar(
        background: Container(
          decoration: const BoxDecoration(
            gradient: AppColors.heroGradient,
          ),
          child: SafeArea(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: 16.0),
                  
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'BizInvestify',
                        style: AppTypography.headlineMedium.copyWith(
                          color: AppColors.textInverse,
                          fontWeight: AppTypography.bold,
                        ),
                      ),
                      
                      Row(
                        children: [
                          _buildHeaderIconButton(
                            Icons.notifications_rounded,
                            onPressed: () {
                              Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (_) => const NotificationsScreen(),
                                ),
                              );
                            },
                          ),
                          const SizedBox(width: 8.0),
                          _buildHeaderIconButton(
                            Icons.search_rounded,
                            onPressed: () {
                              // Navigate to search
                            },
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
      
      backgroundColor: AppColors.primary500,
      foregroundColor: AppColors.textInverse,
    );
  }

  Widget _buildHeaderIconButton(IconData icon, {required VoidCallback onPressed}) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.textInverse.withOpacity(0.2),
        borderRadius: BorderRadius.circular(12.0),
      ),
      child: IconButton(
        icon: Icon(
          icon,
          color: AppColors.textInverse,
          size: 22.0,
        ),
        onPressed: onPressed,
        padding: const EdgeInsets.all(8.0),
        constraints: const BoxConstraints(
          minWidth: 40.0,
          minHeight: 40.0,
        ),
      ),
    );
  }

  /// Modern Welcome Section
  Widget _buildWelcomeSection(dynamic analytics) {
    return Container(
      padding: const EdgeInsets.all(24.0),
      decoration: AppTheme.cardDecoration(
        color: AppColors.backgroundElevated,
        boxShadow: [
          BoxShadow(
            color: AppColors.shadowMd,
            blurRadius: 16.0,
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
                padding: const EdgeInsets.all(12.0),
                decoration: BoxDecoration(
                  gradient: AppColors.brandPrimary,
                  borderRadius: BorderRadius.circular(16.0),
                ),
                child: const Icon(
                  Icons.trending_up_rounded,
                  color: AppColors.textInverse,
                  size: 24.0,
                ),
              ),
              const SizedBox(width: 16.0),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Welcome back, Investor!',
                      style: AppTypography.titleLarge.copyWith(
                        fontWeight: AppTypography.bold,
                      ),
                    ),
                    const SizedBox(height: 4.0),
                    Text(
                      'Here\'s your portfolio performance today',
                      style: AppTypography.bodyMedium.copyWith(
                        color: AppColors.textTertiary,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  /// Professional Analytics Section
  Widget _buildAnalyticsSection(dynamic dashboardState, dynamic analytics) {
    if (dashboardState.isLoading) {
      return _buildLoadingSkeleton();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Portfolio Overview',
          style: AppTypography.titleLarge.copyWith(
            fontWeight: AppTypography.bold,
          ),
        ),
        const SizedBox(height: 16.0),
        
        GridView.count(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 2,
          crossAxisSpacing: 16.0,
          mainAxisSpacing: 16.0,
          childAspectRatio: 1.4,
          children: [
            _buildProfessionalStatsCard(
              title: 'Total Revenue',
              value: '\$${analytics?.totalRevenue?.toStringAsFixed(0) ?? '125,430'}',
              icon: Icons.account_balance_wallet_rounded,
              gradient: AppColors.successGradient,
              trend: '+12.5%',
              trendUp: true,
            ),
            _buildProfessionalStatsCard(
              title: 'Active Investments',
              value: '${analytics?.totalProducts ?? 8}',
              icon: Icons.trending_up_rounded,
              gradient: AppColors.brandPrimary,
              trend: '+8.2%',
              trendUp: true,
            ),
            _buildProfessionalStatsCard(
              title: 'Portfolio Value',
              value: '\$${analytics?.activeListings?.toString() ?? '89,200'}',
              icon: Icons.account_balance_rounded,
              gradient: AppColors.premiumGradient,
              trend: '+15.3%',
              trendUp: true,
            ),
            _buildProfessionalStatsCard(
              title: 'Monthly Return',
              value: '${analytics?.unreadMessages?.toString() ?? '7.2'}%',
              icon: Icons.show_chart_rounded,
              gradient: AppColors.brandSecondary,
              trend: '+2.1%',
              trendUp: true,
            ),
          ],
        ),
      ],
    );
  }

  /// Professional Stats Card
  Widget _buildProfessionalStatsCard({
    required String title,
    required String value,
    required IconData icon,
    required Gradient gradient,
    required String trend,
    required bool trendUp,
  }) {
    return Container(
      padding: const EdgeInsets.all(20.0),
      decoration: AppTheme.cardDecoration(
        boxShadow: [
          BoxShadow(
            color: AppColors.shadowMd,
            blurRadius: 12.0,
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
                padding: const EdgeInsets.all(10.0),
                decoration: BoxDecoration(
                  gradient: gradient,
                  borderRadius: BorderRadius.circular(12.0),
                ),
                child: Icon(
                  icon,
                  color: AppColors.textInverse,
                  size: 20.0,
                ),
              ),
              
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 8.0,
                  vertical: 4.0,
                ),
                decoration: BoxDecoration(
                  color: trendUp 
                      ? AppColors.success50 
                      : AppColors.error50,
                  borderRadius: BorderRadius.circular(8.0),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      trendUp 
                          ? Icons.arrow_upward_rounded 
                          : Icons.arrow_downward_rounded,
                      size: 12.0,
                      color: trendUp 
                          ? AppColors.success500 
                          : AppColors.error500,
                    ),
                    const SizedBox(width: 2.0),
                    Text(
                      trend,
                      style: AppTypography.captionSmall.copyWith(
                        color: trendUp 
                            ? AppColors.success500 
                            : AppColors.error500,
                        fontWeight: AppTypography.semibold,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          
          const Spacer(),
          
          Text(
            value,
            style: AppTypography.headlineSmall.copyWith(
              fontWeight: AppTypography.bold,
              color: AppColors.textPrimary,
            ),
          ),
          
          const SizedBox(height: 4.0),
          
          Text(
            title,
            style: AppTypography.bodySmall.copyWith(
              color: AppColors.textTertiary,
            ),
          ),
        ],
      ),
    );
  }

  /// Quick Actions Section
  Widget _buildQuickActionsSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Quick Actions',
          style: AppTypography.titleLarge.copyWith(
            fontWeight: AppTypography.bold,
          ),
        ),
        const SizedBox(height: 16.0),
        
        Row(
          children: [
            Expanded(
              child: _buildQuickActionCard(
                title: 'Invest Now',
                subtitle: 'Browse opportunities',
                icon: Icons.rocket_launch_rounded,
                gradient: AppColors.brandPrimary,
                onTap: () {
                  // Navigate to investments
                },
              ),
            ),
            const SizedBox(width: 16.0),
            Expanded(
              child: _buildQuickActionCard(
                title: 'My Portfolio',
                subtitle: 'View investments',
                icon: Icons.pie_chart_rounded,
                gradient: AppColors.premiumGradient,
                onTap: () {
                  // Navigate to portfolio
                },
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildQuickActionCard({
    required String title,
    required String subtitle,
    required IconData icon,
    required Gradient gradient,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(20.0),
        decoration: AppTheme.gradientDecoration(
          gradient: gradient,
          boxShadow: [
            BoxShadow(
              color: AppColors.shadowMd,
              blurRadius: 12.0,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(
              icon,
              color: AppColors.textInverse,
              size: 28.0,
            ),
            const SizedBox(height: 16.0),
            Text(
              title,
              style: AppTypography.titleSmall.copyWith(
                color: AppColors.textInverse,
                fontWeight: AppTypography.bold,
              ),
            ),
            const SizedBox(height: 4.0),
            Text(
              subtitle,
              style: AppTypography.captionMedium.copyWith(
                color: AppColors.textInverse.withOpacity(0.9),
              ),
            ),
          ],
        ),
      ),
    );
  }

  /// Recent Activity Section
  Widget _buildRecentActivitySection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Recent Activity',
              style: AppTypography.titleLarge.copyWith(
                fontWeight: AppTypography.bold,
              ),
            ),
            TextButton(
              onPressed: () {
                // View all activity
              },
              child: Text(
                'View All',
                style: AppTypography.labelMedium.copyWith(
                  color: AppColors.primary500,
                  fontWeight: AppTypography.semibold,
                ),
              ),
            ),
          ],
        ),
        
        const SizedBox(height: 16.0),
        
        Container(
          decoration: AppTheme.cardDecoration(),
          child: Column(
            children: [
              _buildActivityItem(
                icon: Icons.trending_up_rounded,
                title: 'Investment in TechCorp',
                subtitle: 'Successfully invested \$5,000',
                time: '2 hours ago',
                iconColor: AppColors.success500,
              ),
              _buildActivityDivider(),
              _buildActivityItem(
                icon: Icons.account_balance_wallet_rounded,
                title: 'Dividend Received',
                subtitle: 'GreenEnergy Co. - \$250',
                time: '1 day ago',
                iconColor: AppColors.secondary500,
              ),
              _buildActivityDivider(),
              _buildActivityItem(
                icon: Icons.notifications_rounded,
                title: 'New Opportunity',
                subtitle: 'AI Startup seeking investment',
                time: '2 days ago',
                iconColor: AppColors.primary500,
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildActivityItem({
    required IconData icon,
    required String title,
    required String subtitle,
    required String time,
    required Color iconColor,
  }) {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10.0),
            decoration: BoxDecoration(
              color: iconColor.withOpacity(0.1),
              borderRadius: BorderRadius.circular(12.0),
            ),
            child: Icon(
              icon,
              color: iconColor,
              size: 20.0,
            ),
          ),
          
          const SizedBox(width: 16.0),
          
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: AppTypography.bodyMedium.copyWith(
                    fontWeight: AppTypography.semibold,
                  ),
                ),
                const SizedBox(height: 2.0),
                Text(
                  subtitle,
                  style: AppTypography.bodySmall.copyWith(
                    color: AppColors.textTertiary,
                  ),
                ),
              ],
            ),
          ),
          
          Text(
            time,
            style: AppTypography.captionSmall.copyWith(
              color: AppColors.textQuaternary,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActivityDivider() {
    return Divider(
      height: 1.0,
      thickness: 0.5,
      color: AppColors.surfaceDivider,
      indent: 16.0,
      endIndent: 16.0,
    );
  }

  /// Loading Skeleton
  Widget _buildLoadingSkeleton() {
    return GridView.count(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisCount: 2,
      crossAxisSpacing: 16.0,
      mainAxisSpacing: 16.0,
      childAspectRatio: 1.4,
      children: List.generate(4, (index) => 
        Container(
          decoration: AppTheme.cardDecoration(),
          child: const Center(
            child: CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(AppColors.primary500),
            ),
          ),
        ),
      ),
    );
  }
}