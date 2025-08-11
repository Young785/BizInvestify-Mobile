import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/constants/app_dimensions.dart';
import '../../marketplace/screens/marketplace_screen.dart';
import '../../../shared/widgets/navigation/sidebar_drawer.dart';
import '../../messaging/screens/messages_screen.dart';
import '../../profile/screens/profile_screen.dart';
import '../providers/dashboard_provider.dart';
import '../../notifications/screens/notifications_screen.dart';
import '../../../core/services/api_service.dart';
import '../../marketplace/providers/marketplace_provider.dart';
import 'package:cached_network_image/cached_network_image.dart';

class DashboardScreen extends ConsumerStatefulWidget {
  const DashboardScreen({super.key});

  @override
  ConsumerState<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends ConsumerState<DashboardScreen> {
  int _currentIndex = 0;
  int _unreadMessages = 0;
  int _unreadNotifications = 0;

  late final List<Widget> _pages = [
    const DashboardContent(),
    const MarketplaceScreen(),
    const MessagesScreen(),
    const ProfileScreen(),
  ];

  Future<void>? _badgeRefreshFuture;

  @override
  void initState() {
    super.initState();
    _badgeRefreshFuture = _refreshBadges();
  }

  Future<void> _refreshBadges() async {
    try {
      final results = await Future.wait<int>([
        apiService.getUnreadCount(),
        apiService.getNotificationUnreadCount(),
      ]);
      if (mounted) {
        setState(() {
          _unreadMessages = results[0];
          _unreadNotifications = results[1];
        });
      }
    } catch (_) {}
  }

  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const SidebarDrawer(),
      backgroundColor: AppColors.background200,
      appBar: AppBar(
        title: Text('Dashboard', style: AppTypography.titleLarge.copyWith(fontWeight: AppTypography.bold)),
        leading: Builder(
          builder: (context) => IconButton(
            icon: const Icon(Icons.menu),
            onPressed: () => Scaffold.of(context).openDrawer(),
          ),
        ),
        actions: [
          Stack(
            clipBehavior: Clip.none,
            children: [
              IconButton(
                icon: const Icon(Icons.notifications_outlined),
                onPressed: () {
                  Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                  );
                },
              ),
              if (_unreadNotifications > 0)
                Positioned(right: 10, top: 10, child: _Badge(count: _unreadNotifications)),
            ],
          ),
        ],
      ),
      body: _pages[_currentIndex],
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
          _badgeRefreshFuture ??= _refreshBadges();
        },
        type: BottomNavigationBarType.fixed,
        backgroundColor: Colors.white,
        selectedItemColor: AppColors.primary500,
        unselectedItemColor: AppColors.text400,
        selectedLabelStyle: const TextStyle(
          fontSize: 12,
          fontWeight: AppTypography.medium,
        ),
        unselectedLabelStyle: const TextStyle(
          fontSize: 12,
          fontWeight: AppTypography.regular,
        ),
        elevation: 8,
        items: [
          const BottomNavigationBarItem(
            icon: Icon(Icons.dashboard_outlined),
            activeIcon: Icon(Icons.dashboard),
            label: 'Dashboard',
          ),
          const BottomNavigationBarItem(
            icon: Icon(Icons.store_outlined),
            activeIcon: Icon(Icons.store),
            label: 'Marketplace',
          ),
          BottomNavigationBarItem(
            icon: Stack(
              clipBehavior: Clip.none,
              children: [
                const Icon(Icons.message_outlined),
                if (_unreadMessages > 0)
                  Positioned(
                    right: -6,
                    top: -2,
                    child: _Badge(count: _unreadMessages),
                  ),
              ],
            ),
            activeIcon: const Icon(Icons.message),
            label: 'Messages',
          ),
          const BottomNavigationBarItem(
            icon: Icon(Icons.person_outline),
            activeIcon: Icon(Icons.person),
            label: 'Profile',
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          // TODO: Quick add menu
        },
        backgroundColor: AppColors.primary500,
        child: const Icon(Icons.add, color: Colors.white),
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
  // Duplicate helper removed

  Widget _buildHeroCarousel({required MarketplaceState meta}) {
    final featured = meta.featuredProducts;
    final trending = meta.trending;
    final hasData = featured.isNotEmpty || trending.isNotEmpty;
    if (!hasData) {
      return SizedBox(
        height: 110,
        child: ListView.separated(
          scrollDirection: Axis.horizontal,
          itemBuilder: (_, i) => _miniAssetCard(i),
          separatorBuilder: (_, __) => const SizedBox(width: 12),
          itemCount: 6,
        ),
      );
    }

    final cards = <Widget>[];
    for (final p in featured.take(10)) {
      final image = p.images.isNotEmpty ? p.images.first : null;
      cards.add(_heroCard(title: p.name, subtitle: p.category, imageUrl: image));
    }
    for (final t in trending.take(10)) {
      final title = (t['name'] ?? t['title'] ?? 'Item').toString();
      final subtitle = (t['category'] ?? t['industry'] ?? '').toString();
      final images = (t['images'] is List) ? List<String>.from(t['images']) : <String>[];
      final imageUrl = images.isNotEmpty ? images.first : null;
      cards.add(_heroCard(title: title, subtitle: subtitle, imageUrl: imageUrl));
    }

    return SizedBox(
      height: 160,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemBuilder: (_, i) => SizedBox(width: 260, child: cards[i]),
        separatorBuilder: (_, __) => const SizedBox(width: 12),
        itemCount: cards.length.clamp(0, 12),
      ),
    );
  }

  Widget _heroCard({required String title, required String subtitle, String? imageUrl}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 12, offset: const Offset(0, 6)),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Stack(children: [
        Positioned.fill(
          child: imageUrl == null
              ? Container(color: AppColors.primary500.withOpacity(0.08))
              : CachedNetworkImage(
                  imageUrl: imageUrl,
                  fit: BoxFit.cover,
                ),
        ),
        Positioned.fill(
          child: DecoratedBox(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [Colors.transparent, Colors.black.withOpacity(0.35)],
              ),
            ),
          ),
        ),
        Positioned(
          left: 12,
          right: 12,
          bottom: 12,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: AppTypography.titleSmall.copyWith(color: Colors.white, fontWeight: AppTypography.bold)),
              if (subtitle.isNotEmpty)
                Text(subtitle, style: AppTypography.captionSmall.copyWith(color: Colors.white70)),
            ],
          ),
        )
      ]),
    );
  }
  @override
  void initState() {
    super.initState();
    _loadAnalytics();
  }

  void _loadAnalytics() {
    ref.read(dashboardProvider.notifier).loadAnalytics();
    // Also ensure marketplace meta is loaded for the hero carousel
    ref.read(marketplaceProvider.notifier).loadMarketplaceMeta();
  }

  @override
  Widget build(BuildContext context) {
    final dashboardState = ref.watch(dashboardProvider);
    final marketplaceMeta = ref.watch(marketplaceProvider);
    final analytics = dashboardState.analytics;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(AppDimensions.spacing16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildBalanceCard(analytics),
          const SizedBox(height: AppDimensions.spacing16),
          // Welcome Card
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(AppDimensions.borderRadiusLarge),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.06),
                  blurRadius: 12,
                  offset: const Offset(0, 6),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Welcome back, ${analytics != null ? 'User' : 'User'}!',
                  style: AppTypography.headlineSmall.copyWith(
                    fontWeight: AppTypography.bold,
                  ),
                ),
                const SizedBox(height: AppDimensions.spacing8),
                Text(
                  "Here's what's happening with your business today.",
                  style: AppTypography.bodyMedium.copyWith(color: AppColors.text600),
                ),
              ],
            ),
          ),

          const SizedBox(height: AppDimensions.spacing24),

          // Stats Cards
          if (dashboardState.isLoading)
            const Center(child: CircularProgressIndicator())
          else
            GridView.count(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: 2,
              crossAxisSpacing: AppDimensions.spacing16,
              mainAxisSpacing: AppDimensions.spacing16,
              childAspectRatio: 1.6,
              children: [
                _buildStatsCard(
                  title: 'Total Revenue',
                  value: '\$${analytics?.totalRevenue.toStringAsFixed(0) ?? '0'}',
                  icon: Icons.attach_money,
                  color: AppColors.accent500,
                  trend: '+12.5%',
                  trendUp: true,
                ),
                _buildStatsCard(
                  title: 'Total Products',
                  value: '${analytics?.totalProducts ?? 0}',
                  icon: Icons.inventory,
                  color: AppColors.primary500,
                  trend: '+8.2%',
                  trendUp: true,
                ),
                _buildStatsCard(
                  title: 'Active Listings',
                  value: '${analytics?.activeListings ?? 0}',
                  icon: Icons.store,
                  color: AppColors.accent600,
                  trend: '+5.1%',
                  trendUp: true,
                ),
                _buildStatsCard(
                  title: 'Messages',
                  value: '${analytics?.unreadMessages ?? 0}',
                  icon: Icons.message,
                  color: AppColors.primary600,
                  trend: 'New',
                  trendUp: false,
                ),
              ],
            ),

          const SizedBox(height: AppDimensions.spacing24),

          // Hero carousel (featured + trending with images)
          Text('Highlights', style: AppTypography.titleLarge.copyWith(fontWeight: AppTypography.bold)),
          const SizedBox(height: 12),
          _buildHeroCarousel(meta: marketplaceMeta),
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
            childAspectRatio: 1.3,
            children: [
              _buildQuickActionCard(
                title: 'Add Product',
                subtitle: 'List a new product',
                icon: Icons.add_shopping_cart,
                color: AppColors.primary500,
                onTap: () {},
              ),
              _buildQuickActionCard(
                title: 'List Business',
                subtitle: 'Seek investment',
                icon: Icons.business,
                color: AppColors.accent500,
                onTap: () {},
              ),
              _buildQuickActionCard(
                title: 'Messages',
                subtitle: '5 unread',
                icon: Icons.message,
                color: AppColors.primary600,
                onTap: () {},
              ),
              _buildQuickActionCard(
                title: 'Analytics',
                subtitle: 'View performance',
                icon: Icons.analytics,
                color: AppColors.accent600,
                onTap: () {},
              ),
            ],
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
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(AppDimensions.borderRadiusLarge),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 12,
            offset: const Offset(0, 6),
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
              color: Colors.black.withOpacity(0.06),
              blurRadius: 12,
              offset: const Offset(0, 6),
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

  Widget _buildBalanceCard(DashboardAnalytics? analytics) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 18),
      decoration: BoxDecoration(
        color: AppColors.primary500,
        borderRadius: BorderRadius.circular(AppDimensions.borderRadiusLarge),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 12, offset: const Offset(0, 6)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Your current balance', style: AppTypography.captionMedium.copyWith(color: Colors.white70)),
              const Icon(Icons.remove_red_eye_outlined, color: Colors.white70),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            '\$${(analytics?.totalRevenue ?? 0).toStringAsFixed(2)}',
            style: AppTypography.headlineSmall.copyWith(color: Colors.white, fontWeight: AppTypography.bold),
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _balanceAction(icon: Icons.south_west, label: 'Deposit'),
              _balanceAction(icon: Icons.north_east, label: 'Withdraw'),
              _balanceAction(icon: Icons.receipt_long_outlined, label: 'History'),
            ],
          )
        ],
      ),
    );
  }

  Widget _balanceAction({required IconData icon, required String label}) {
    return Column(
      children: [
        Container(
          width: 44,
          height: 44,
          decoration: BoxDecoration(color: Colors.white.withOpacity(0.15), shape: BoxShape.circle),
          child: Icon(icon, color: Colors.white),
        ),
        const SizedBox(height: 8),
        Text(label, style: AppTypography.captionSmall.copyWith(color: Colors.white)),
      ],
    );
  }

  Widget _miniAssetCard(int i) {
    final colors = [AppColors.primary500, AppColors.secondary500, AppColors.accent600, AppColors.primary600];
    final color = colors[i % colors.length];
    return Container(
      width: 160,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [
        BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 10, offset: const Offset(0, 6)),
      ]),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          CircleAvatar(radius: 14, backgroundColor: color.withOpacity(0.15), child: Icon(Icons.show_chart, color: color, size: 16)),
          const SizedBox(width: 8),
          Text(i % 2 == 0 ? 'USD' : 'BNB/USD', style: AppTypography.bodySmall.copyWith(fontWeight: AppTypography.semibold)),
        ]),
        const Spacer(),
        Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
          Text('\$${(120 + i * 3).toStringAsFixed(2)}', style: AppTypography.titleSmall.copyWith(fontWeight: AppTypography.bold)),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
            decoration: BoxDecoration(color: Colors.green.withOpacity(0.1), borderRadius: BorderRadius.circular(10)),
            child: Text('+${(1.2 + i * 0.2).toStringAsFixed(2)}%', style: const TextStyle(color: Colors.green, fontSize: 11, fontWeight: FontWeight.w600)),
          )
        ])
      ]),
    );
  }
}
