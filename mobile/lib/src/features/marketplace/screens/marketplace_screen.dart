import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../providers/marketplace_provider.dart';
import '../models/marketplace_models.dart';
import '../widgets/business_card.dart';
import '../widgets/marketplace_header.dart';
import '../widgets/search_bar.dart' as custom;
import '../widgets/filter_sheet.dart';
import '../../../core/utils/toast.dart';
import '../../../components/animations/shimmer_skeleton.dart';

class MarketplaceScreen extends ConsumerStatefulWidget {
  const MarketplaceScreen({super.key});

  @override
  ConsumerState<MarketplaceScreen> createState() => _MarketplaceScreenState();
}

class _MarketplaceScreenState extends ConsumerState<MarketplaceScreen> {
  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();
  
  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    
    // Load initial data
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadInitialData();
    });
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _loadInitialData() {
    final notifier = ref.read(marketplaceProvider.notifier);
    notifier.refresh();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= 
        _scrollController.position.maxScrollExtent - 200) {
      // Load more businesses when near bottom
      ref.read(marketplaceProvider.notifier).loadBusinesses();
    }
  }

  void _showFilterSheet() {
    showModalBottomSheet<BusinessFilters>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => FilterSheet(
        currentFilters: ref.read(marketplaceProvider).filters,
      ),
    ).then((filters) {
      if (filters != null) {
        ref.read(marketplaceProvider.notifier).updateFilters(filters);
        ref.read(marketplaceProvider.notifier).loadBusinesses(
          filters: filters,
          refresh: true,
        );
      }
    });
  }

  void _onSearch(String query) {
    if (query.trim().isEmpty) {
      ref.read(marketplaceProvider.notifier).loadBusinesses(refresh: true);
    } else {
      ref.read(marketplaceProvider.notifier).searchBusinesses(query.trim());
    }
  }

  @override
  Widget build(BuildContext context) {
    final businesses = ref.watch(businessesProvider);
    final featuredBusinesses = ref.watch(featuredBusinessesProvider);
    final trendingBusinesses = ref.watch(trendingBusinessesProvider);
    final isLoading = ref.watch(marketplaceLoadingProvider);
    final error = ref.watch(marketplaceErrorProvider);
    final stats = ref.watch(marketplaceStatsProvider);

    return Scaffold(
      backgroundColor: Colors.grey[50],
      body: RefreshIndicator(
        onRefresh: () async {
          await ref.read(marketplaceProvider.notifier).refresh();
        },
        child: CustomScrollView(
          controller: _scrollController,
          slivers: [
            // App Bar
            SliverAppBar(
              expandedHeight: 120,
              floating: true,
              pinned: true,
              backgroundColor: Colors.white,
              elevation: 0,
              flexibleSpace: FlexibleSpaceBar(
                background: MarketplaceHeader(stats: stats),
              ),
              bottom: PreferredSize(
                preferredSize: const Size.fromHeight(60),
                child: Container(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: [
                      Expanded(
                        child: custom.SearchBar(
                          controller: _searchController,
                          onSearch: _onSearch,
                          hintText: 'Search businesses...',
                        ),
                      ),
                      const SizedBox(width: 12),
                      Material(
                        color: Colors.blue[50],
                        borderRadius: BorderRadius.circular(12),
                        child: InkWell(
                          onTap: _showFilterSheet,
                          borderRadius: BorderRadius.circular(12),
                          child: Container(
                            padding: const EdgeInsets.all(12),
                            child: Icon(
                              Icons.tune,
                              color: Colors.blue[600],
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // Error message
            if (error != null)
              SliverToBoxAdapter(
                child: Container(
                  margin: const EdgeInsets.all(16),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.red[50],
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.red[200]!),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.error_outline, color: Colors.red[600]),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          error,
                          style: TextStyle(color: Colors.red[800]),
                        ),
                      ),
                      TextButton(
                        onPressed: () {
                          ref.read(marketplaceProvider.notifier).clearError();
                          _loadInitialData();
                          AppToast.info('Retrying...');
                        },
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                ),
              ),

            // Featured Businesses Section
            if (featuredBusinesses.isNotEmpty)
              SliverToBoxAdapter(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'Featured Opportunities',
                            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          TextButton(
                            onPressed: () {
                              // TODO: Navigate to featured businesses page
                            },
                            child: const Text('View All'),
                          ),
                        ],
                      ),
                    ),
                    SizedBox(
                      height: 280,
                      child: ListView.builder(
                        scrollDirection: Axis.horizontal,
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        itemCount: featuredBusinesses.length,
                        itemBuilder: (context, index) {
                          final business = featuredBusinesses[index];
                          return Container(
                            width: 300,
                            margin: const EdgeInsets.only(right: 16),
                            child: BusinessCard(
                              business: business,
                              onTap: () => context.push('/business/${business.id}'),
                              featured: true,
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
              ),

            // Trending Businesses Section
            if (trendingBusinesses.isNotEmpty)
              SliverToBoxAdapter(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'Trending Now',
                            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          TextButton(
                            onPressed: () {
                              // TODO: Navigate to trending businesses page
                            },
                            child: const Text('View All'),
                          ),
                        ],
                      ),
                    ),
                    SizedBox(
                      height: 280,
                      child: ListView.builder(
                        scrollDirection: Axis.horizontal,
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        itemCount: trendingBusinesses.length,
                        itemBuilder: (context, index) {
                          final business = trendingBusinesses[index];
                          return Container(
                            width: 300,
                            margin: const EdgeInsets.only(right: 16),
                            child: BusinessCard(
                              business: business,
                              onTap: () => context.push('/business/${business.id}'),
                              trending: true,
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
              ),

            // All Businesses Section
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Text(
                  'All Opportunities',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),

            // Business List
            if (isLoading && businesses.isEmpty)
              SliverList(
                delegate: SliverChildBuilderDelegate(
                  (context, index) => const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    child: ShimmerSkeleton(height: 120, width: double.infinity),
                  ),
                  childCount: 6,
                ),
              )
            else if (businesses.isEmpty)
              const SliverToBoxAdapter(
                child: Center(
                  child: Padding(
                    padding: EdgeInsets.all(32),
                    child: Column(
                      children: [
                        Icon(
                          Icons.business_outlined,
                          size: 64,
                          color: Colors.grey,
                        ),
                        SizedBox(height: 16),
                        Text(
                          'No businesses found',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w500,
                            color: Colors.grey,
                          ),
                        ),
                        SizedBox(height: 8),
                        Text(
                          'Try adjusting your search or filters',
                          style: TextStyle(color: Colors.grey),
                        ),
                      ],
                    ),
                  ),
                ),
              )
            else
              SliverList(
                delegate: SliverChildBuilderDelegate(
                  (context, index) {
                    if (index < businesses.length) {
                      final business = businesses[index];
                      return Padding(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 8,
                        ),
                        child: BusinessCard(
                          business: business,
                          onTap: () => context.push('/business/${business.id}'),
                        ),
                      );
                    }
                    
                    // Loading indicator at the end
                    if (isLoading) {
                      return const Padding(
                        padding: EdgeInsets.all(16),
                        child: Center(child: CircularProgressIndicator()),
                      );
                    }
                    
                    return const SizedBox.shrink();
                  },
                  childCount: businesses.length + (isLoading ? 1 : 0),
                ),
              ),

            // Bottom padding
            const SliverToBoxAdapter(
              child: SizedBox(height: 100),
            ),
          ],
        ),
      ),
    );
  }
}
