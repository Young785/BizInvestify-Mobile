import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/constants/app_dimensions.dart';
import '../widgets/product_card.dart';
import '../widgets/business_card.dart';
import '../providers/marketplace_provider.dart';

class MarketplaceScreen extends ConsumerStatefulWidget {
  const MarketplaceScreen({super.key});

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
    _tabController = TabController(length: 2, vsync: this);
    _loadData();
  }

  void _loadData() {
    ref.read(marketplaceProvider.notifier).loadProducts();
    ref.read(marketplaceProvider.notifier).loadBusinesses();
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
      backgroundColor: AppColors.background50,
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
        onSelected: (value) {
          if (value == 'product') {
            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Add Product flow coming soon')));
          } else {
            ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Add Business flow coming soon')));
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
      child: GridView.builder(
        padding: const EdgeInsets.all(AppDimensions.spacing16),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          childAspectRatio: 0.75,
          crossAxisSpacing: AppDimensions.spacing16,
          mainAxisSpacing: AppDimensions.spacing16,
        ),
        itemCount: state.products.length,
        itemBuilder: (context, index) {
          final product = state.products[index];
          return ProductCard(
            product: product,
            onTap: () {
              // Navigate to product details
            },
          );
        },
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
      child: ListView.builder(
        padding: const EdgeInsets.all(AppDimensions.spacing16),
        itemCount: state.businesses.length,
        itemBuilder: (context, index) {
          final business = state.businesses[index];
          return Padding(
            padding: const EdgeInsets.only(bottom: AppDimensions.spacing16),
            child: BusinessCard(
              business: business,
              onTap: () {
                // Navigate to business details
              },
            ),
          );
        },
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
            color: AppColors.text400,
          ),
          const SizedBox(height: AppDimensions.spacing16),
          Text(
            title,
            style: AppTypography.titleLarge.copyWith(
              color: AppColors.text600,
              fontWeight: AppTypography.semibold,
            ),
          ),
          const SizedBox(height: AppDimensions.spacing8),
          Text(
            subtitle,
            style: AppTypography.bodyMedium.copyWith(
              color: AppColors.text500,
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
