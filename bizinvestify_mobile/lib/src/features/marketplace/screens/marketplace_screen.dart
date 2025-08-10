import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/constants/app_dimensions.dart';
import '../../../shared/widgets/inputs/custom_text_field.dart';
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
  String _selectedCategory = 'All';
  String _selectedIndustry = 'All';

  final List<String> _productCategories = [
    'All',
    'Electronics',
    'Fashion',
    'Home & Garden',
    'Sports',
    'Books',
    'Automotive',
    'Health & Beauty',
    'Toys & Games',
  ];

  final List<String> _businessIndustries = [
    'All',
    'Technology',
    'Healthcare',
    'Finance',
    'Retail',
    'Manufacturing',
    'Food & Beverage',
    'Real Estate',
    'Education',
    'Entertainment',
  ];

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
      backgroundColor: AppColors.background200,
      appBar: AppBar(
        title: const Text('Marketplace'),
        backgroundColor: Colors.white,
        elevation: 1,
        bottom: TabBar(
          controller: _tabController,
          labelColor: AppColors.primary500,
          unselectedLabelColor: AppColors.text500,
          indicatorColor: AppColors.primary500,
          tabs: const [
            Tab(text: 'Products'),
            Tab(text: 'Businesses'),
          ],
        ),
      ),
      body: Column(
        children: [
          // Search and Filter Section
          Container(
            padding: const EdgeInsets.all(AppDimensions.spacing16),
            color: Colors.white,
            child: Column(
              children: [
                // Search Bar
                CustomTextField(
                  controller: _searchController,
                  hint: 'Search products, businesses...',
                  prefixIcon: const Icon(Icons.search, color: AppColors.text500),
                  onChanged: (value) {
                    setState(() {
                      _searchQuery = value;
                    });
                    _performSearch();
                  },
                ),
                
                const SizedBox(height: AppDimensions.spacing12),
                
                // Filter Chips
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      if (_tabController.index == 0) ...[
                        ..._productCategories.map((category) => Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: FilterChip(
                            label: Text(category),
                            selected: _selectedCategory == category,
                            onSelected: (selected) {
                              setState(() {
                                _selectedCategory = selected ? category : 'All';
                              });
                              _applyFilters();
                            },
                            selectedColor: AppColors.primary500.withOpacity(0.2),
                            checkmarkColor: AppColors.primary500,
                          ),
                        )),
                      ] else ...[
                        ..._businessIndustries.map((industry) => Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: FilterChip(
                            label: Text(industry),
                            selected: _selectedIndustry == industry,
                            onSelected: (selected) {
                              setState(() {
                                _selectedIndustry = selected ? industry : 'All';
                              });
                              _applyFilters();
                            },
                            selectedColor: AppColors.primary500.withOpacity(0.2),
                            checkmarkColor: AppColors.primary500,
                          ),
                        )),
                      ],
                    ],
                  ),
                ),
              ],
            ),
          ),
          
          // Content
          Expanded(
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

  void _applyFilters() {
    if (_tabController.index == 0) {
      ref.read(marketplaceProvider.notifier).filterProducts(
        category: _selectedCategory == 'All' ? null : _selectedCategory,
        search: _searchQuery.isEmpty ? null : _searchQuery,
      );
    } else {
      ref.read(marketplaceProvider.notifier).filterBusinesses(
        industry: _selectedIndustry == 'All' ? null : _selectedIndustry,
        search: _searchQuery.isEmpty ? null : _searchQuery,
      );
    }
  }
}
