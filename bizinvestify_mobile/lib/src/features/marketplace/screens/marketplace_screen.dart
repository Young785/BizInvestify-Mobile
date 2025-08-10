import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../widgets/product_card.dart';
import '../widgets/business_card.dart';
import '../providers/marketplace_provider.dart';
import 'add_product_screen.dart';
import 'add_business_screen.dart';

class MarketplaceScreen extends ConsumerStatefulWidget {
  final int? initialTab; // 0 products, 1 businesses
  const MarketplaceScreen({super.key, this.initialTab});

  @override
  ConsumerState<MarketplaceScreen> createState() => _MarketplaceScreenState();
}

class _MarketplaceScreenState extends ConsumerState<MarketplaceScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final _searchController = TextEditingController();
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this, initialIndex: widget.initialTab ?? 0);
    _loadData();
  }

  void _loadData() {
    ref.read(marketplaceProvider.notifier).loadProducts();
    ref.read(marketplaceProvider.notifier).loadBusinesses();
    ref.read(marketplaceProvider.notifier).loadMarketplaceMeta();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final marketplaceState = ref.watch(marketplaceProvider);
    
    return Scaffold(
      backgroundColor: AppColors.backgroundPrimary,
      body: CustomScrollView(
        slivers: [
          // Search App Bar
          SliverAppBar(
            floating: true,
            backgroundColor: Colors.white,
            elevation: 2,
            title: Container(
              height: 40,
              decoration: BoxDecoration(
                color: Colors.grey[100],
                borderRadius: BorderRadius.circular(20),
              ),
              child: TextField(
                controller: _searchController,
                decoration: InputDecoration(
                  hintText: 'Search products, businesses...',
                  prefixIcon: const Icon(Icons.search),
                  border: InputBorder.none,
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16),
                ),
                onChanged: (value) {
                  setState(() => _searchQuery = value);
                  _performSearch();
                },
              ),
            ),
            bottom: TabBar(
              controller: _tabController,
              labelColor: AppColors.primary500,
              unselectedLabelColor: Colors.grey[600],
              indicatorColor: AppColors.primary500,
              tabs: const [
                Tab(text: 'Products'),
                Tab(text: 'Businesses'),
              ],
            ),
          ),
          
          // Marketplace Content
          SliverFillRemaining(
            child: TabBarView(
              controller: _tabController,
              children: [
                // Products Tab
                _buildProductsTab(marketplaceState),
                
                // Businesses Tab
                _buildBusinessesTab(marketplaceState),
              ],
            ),
          ),
        ],
      ),
      floatingActionButton: PopupMenuButton<String>(
        itemBuilder: (_) => const [
          PopupMenuItem(value: 'product', child: Text('Add Product')),
          PopupMenuItem(value: 'business', child: Text('Add Business')),
        ],
        onSelected: (value) async {
          if (value == 'product') {
            await Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const AddProductScreen()),
            );
            if (mounted) ref.read(marketplaceProvider.notifier).loadProducts();
          } else {
            await Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const AddBusinessScreen()),
            );
            if (mounted) ref.read(marketplaceProvider.notifier).loadBusinesses();
          }
        },
        child: const CircleAvatar(
          backgroundColor: AppColors.primary500,
          child: Icon(Icons.add, color: Colors.white),
        ),
      ),
    );
  }

  Widget _buildProductsTab(MarketplaceState state) {
    if (state.isLoadingProducts) {
      return const Center(child: CircularProgressIndicator());
    }

    if (state.products.isEmpty) {
      return _buildEmptyState(
        icon: Icons.inventory_2,
        title: 'No Products Found',
        subtitle: 'Try adjusting your search or filters',
      );
    }

    return RefreshIndicator(
      onRefresh: () async {
        ref.read(marketplaceProvider.notifier).loadProducts();
      },
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (state.categories.isNotEmpty) _buildCategoryChips(state),
          if (state.featuredProducts.isNotEmpty) ...[
            const SizedBox(height: 12),
            Text('Featured', style: AppTypography.titleLarge.copyWith(fontWeight: AppTypography.bold)),
            const SizedBox(height: 8),
            SizedBox(
              height: 240,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemBuilder: (context, i) => SizedBox(width: 180, child: ProductCard(product: state.featuredProducts[i])),
                separatorBuilder: (_, __) => const SizedBox(width: 12),
                itemCount: state.featuredProducts.length.clamp(0, 10),
              ),
            ),
          ],
          const SizedBox(height: 16),
          Text('All Products', style: AppTypography.titleLarge.copyWith(fontWeight: AppTypography.bold)),
          const SizedBox(height: 8),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 0.75,
              crossAxisSpacing: 16.0,
              mainAxisSpacing: 16.0,
            ),
            itemCount: state.products.length,
            itemBuilder: (context, index) {
              final product = state.products[index];
              return ProductCard(product: product);
            },
          ),
        ],
      ),
    );
  }

  Widget _buildBusinessesTab(MarketplaceState state) {
    if (state.isLoadingBusinesses) {
      return const Center(child: CircularProgressIndicator());
    }

    if (state.businesses.isEmpty) {
      return _buildEmptyState(
        icon: Icons.business,
        title: 'No Businesses Found',
        subtitle: 'Try adjusting your search or filters',
      );
    }

    return RefreshIndicator(
      onRefresh: () async {
        ref.read(marketplaceProvider.notifier).loadBusinesses();
      },
      child: ListView(
        padding: const EdgeInsets.all(16.0),
        children: [
          if (state.trending.isNotEmpty) ...[
            Text('Trending', style: AppTypography.titleLarge.copyWith(fontWeight: AppTypography.bold)),
            const SizedBox(height: 8),
            SizedBox(
              height: 140,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemBuilder: (_, i) {
                  final item = state.trending[i];
                  return Container(
                    width: 220,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), boxShadow: [
                      BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 10, offset: const Offset(0, 6)),
                    ]),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text('${item['name'] ?? item['title'] ?? 'Item'}', style: AppTypography.titleSmall.copyWith(fontWeight: AppTypography.semibold)),
                      const SizedBox(height: 6),
                      Text('${item['category'] ?? item['industry'] ?? ''}', style: AppTypography.captionSmall.copyWith(color: AppColors.textTertiary)),
                    ]),
                  );
                },
                separatorBuilder: (_, __) => const SizedBox(width: 12),
                itemCount: state.trending.length.clamp(0, 10),
              ),
            ),
            const SizedBox(height: 16),
          ],
          ...List.generate(state.businesses.length, (index) {
            final business = state.businesses[index];
            return Padding(
              padding: const EdgeInsets.only(bottom: 16.0),
              child: BusinessCard(business: business),
            );
          })
        ],
      ),
    );
  }

  Widget _buildEmptyState({
    required IconData icon,
    required String title,
    required String subtitle,
  }) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            icon,
            size: 64,
            color: AppColors.textQuaternary,
          ),
          const SizedBox(height: 16.0),
          Text(
            title,
            style: AppTypography.titleLarge.copyWith(
              color: AppColors.textTertiary,
              fontWeight: AppTypography.semibold,
            ),
          ),
          const SizedBox(height: 8.0),
          Text(
            subtitle,
            style: AppTypography.bodyMedium.copyWith(
              color: AppColors.textTertiary,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  void _performSearch() {
    if (_tabController.index == 0) {
      ref.read(marketplaceProvider.notifier).searchProducts(_searchQuery);
    } else {
      ref.read(marketplaceProvider.notifier).searchBusinesses(_searchQuery);
    }
  }

  // Filters are not implemented yet
}

Widget _buildCategoryChips(MarketplaceState state) {
  return SizedBox(
    height: 40,
    child: ListView.separated(
      scrollDirection: Axis.horizontal,
      itemBuilder: (_, i) => Chip(
        label: Text(state.categories[i]),
        backgroundColor: Colors.white,
      ),
      separatorBuilder: (_, __) => const SizedBox(width: 8),
      itemCount: state.categories.length.clamp(0, 15),
    ),
  );
}
