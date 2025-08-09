import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/api_service.dart';
import '../services/marketplace_service.dart';
import '../models/marketplace_models.dart';

// Marketplace Service Provider
final marketplaceServiceProvider = Provider<MarketplaceService>((ref) {
  final apiService = ref.watch(apiServiceProvider);
  return MarketplaceService(apiService);
});

// Marketplace State
class MarketplaceState {
  final List<Business> businesses;
  final List<Business> featuredBusinesses;
  final List<Business> trendingBusinesses;
  final List<Investment> investments;
  final Portfolio? portfolio;
  final MarketplaceStats? stats;
  final bool isLoading;
  final String? error;
  final BusinessFilters filters;
  final int currentPage;
  final bool hasMore;

  const MarketplaceState({
    this.businesses = const [],
    this.featuredBusinesses = const [],
    this.trendingBusinesses = const [],
    this.investments = const [],
    this.portfolio,
    this.stats,
    this.isLoading = false,
    this.error,
    this.filters = const BusinessFilters(),
    this.currentPage = 1,
    this.hasMore = true,
  });

  MarketplaceState copyWith({
    List<Business>? businesses,
    List<Business>? featuredBusinesses,
    List<Business>? trendingBusinesses,
    List<Investment>? investments,
    Portfolio? portfolio,
    MarketplaceStats? stats,
    bool? isLoading,
    String? error,
    BusinessFilters? filters,
    int? currentPage,
    bool? hasMore,
  }) {
    return MarketplaceState(
      businesses: businesses ?? this.businesses,
      featuredBusinesses: featuredBusinesses ?? this.featuredBusinesses,
      trendingBusinesses: trendingBusinesses ?? this.trendingBusinesses,
      investments: investments ?? this.investments,
      portfolio: portfolio ?? this.portfolio,
      stats: stats ?? this.stats,
      isLoading: isLoading ?? this.isLoading,
      error: error,
      filters: filters ?? this.filters,
      currentPage: currentPage ?? this.currentPage,
      hasMore: hasMore ?? this.hasMore,
    );
  }
}

// Marketplace Notifier
class MarketplaceNotifier extends StateNotifier<MarketplaceState> {
  final MarketplaceService _marketplaceService;

  MarketplaceNotifier(this._marketplaceService) : super(const MarketplaceState());

  // Load businesses with filters
  Future<void> loadBusinesses({
    BusinessFilters? filters,
    bool refresh = false,
  }) async {
    if (refresh) {
      state = state.copyWith(
        businesses: [],
        currentPage: 1,
        hasMore: true,
        error: null,
      );
    }

    if (state.isLoading || !state.hasMore) return;

    state = state.copyWith(isLoading: true, error: null);

    try {
      final response = await _marketplaceService.getBusinesses(
        filters: filters ?? state.filters,
        page: refresh ? 1 : state.currentPage,
        perPage: 10,
      );

      if (response.success && response.data != null) {
        final newBusinesses = response.data!;
        final updatedBusinesses = refresh 
            ? newBusinesses 
            : [...state.businesses, ...newBusinesses];

        state = state.copyWith(
          businesses: updatedBusinesses,
          isLoading: false,
          currentPage: (response.currentPage ?? state.currentPage) + 1,
          hasMore: (response.currentPage ?? 1) < (response.lastPage ?? 1),
          filters: filters ?? state.filters,
        );
      } else {
        state = state.copyWith(
          isLoading: false,
          error: response.message ?? 'Failed to load businesses',
        );
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  // Load featured businesses
  Future<void> loadFeaturedBusinesses() async {
    try {
      final response = await _marketplaceService.getFeaturedBusinesses();

      if (response.success && response.data != null) {
        state = state.copyWith(featuredBusinesses: response.data!);
      }
    } catch (e) {
      // Silently fail for featured businesses as they're not critical
    }
  }

  // Load trending businesses
  Future<void> loadTrendingBusinesses() async {
    try {
      final response = await _marketplaceService.getTrendingBusinesses();

      if (response.success && response.data != null) {
        state = state.copyWith(trendingBusinesses: response.data!);
      }
    } catch (e) {
      // Silently fail for trending businesses as they're not critical
    }
  }

  // Load user investments
  Future<void> loadInvestments() async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final response = await _marketplaceService.getMyInvestments();

      if (response.success && response.data != null) {
        state = state.copyWith(
          investments: response.data!,
          isLoading: false,
        );
      } else {
        state = state.copyWith(
          isLoading: false,
          error: response.message ?? 'Failed to load investments',
        );
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  // Load user portfolio
  Future<void> loadPortfolio() async {
    try {
      final response = await _marketplaceService.getUserPortfolio();

      if (response.success && response.data != null) {
        state = state.copyWith(portfolio: response.data!);
      }
    } catch (e) {
      // Silently fail for portfolio as it's not critical for UI
    }
  }

  // Load marketplace statistics
  Future<void> loadStats() async {
    try {
      final response = await _marketplaceService.getMarketplaceStats();

      if (response.success && response.data != null) {
        state = state.copyWith(stats: response.data!);
      }
    } catch (e) {
      // Silently fail for stats as they're not critical
    }
  }

  // Create investment
  Future<bool> createInvestment({
    required String businessId,
    required double amount,
    String? message,
  }) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final request = CreateInvestmentRequest(
        businessId: businessId,
        amount: amount,
        message: message,
      );

      final response = await _marketplaceService.createInvestment(request);

      if (response.success) {
        state = state.copyWith(isLoading: false);
        // Reload investments to get updated list
        await loadInvestments();
        await loadPortfolio();
        return true;
      } else {
        state = state.copyWith(
          isLoading: false,
          error: response.message ?? 'Failed to create investment',
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      return false;
    }
  }

  // Search businesses
  Future<void> searchBusinesses(String query, {BusinessFilters? filters}) async {
    state = state.copyWith(
      isLoading: true,
      error: null,
      businesses: [],
      currentPage: 1,
      hasMore: true,
    );

    try {
      final response = await _marketplaceService.searchBusinesses(
        query: query,
        filters: filters,
        page: 1,
        perPage: 10,
      );

      if (response.success && response.data != null) {
        state = state.copyWith(
          businesses: response.data!,
          isLoading: false,
          currentPage: (response.currentPage ?? 1) + 1,
          hasMore: (response.currentPage ?? 1) < (response.lastPage ?? 1),
        );
      } else {
        state = state.copyWith(
          isLoading: false,
          error: response.message ?? 'Failed to search businesses',
        );
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  // Update filters
  void updateFilters(BusinessFilters filters) {
    state = state.copyWith(filters: filters);
  }

  // Clear error
  void clearError() {
    state = state.copyWith(error: null);
  }

  // Refresh all data
  Future<void> refresh() async {
    await Future.wait([
      loadBusinesses(refresh: true),
      loadFeaturedBusinesses(),
      loadTrendingBusinesses(),
      loadInvestments(),
      loadPortfolio(),
      loadStats(),
    ]);
  }
}

// Marketplace Provider
final marketplaceProvider = StateNotifierProvider<MarketplaceNotifier, MarketplaceState>((ref) {
  final marketplaceService = ref.watch(marketplaceServiceProvider);
  return MarketplaceNotifier(marketplaceService);
});

// Individual Business Provider
final businessProvider = FutureProvider.family<Business?, String>((ref, businessId) async {
  final marketplaceService = ref.watch(marketplaceServiceProvider);
  
  try {
    final response = await marketplaceService.getBusiness(businessId);
    return response.success ? response.data : null;
  } catch (e) {
    return null;
  }
});

// Business Investments Provider
final businessInvestmentsProvider = FutureProvider.family<List<Investment>, String>((ref, businessId) async {
  final marketplaceService = ref.watch(marketplaceServiceProvider);
  
  try {
    final response = await marketplaceService.getBusinessInvestments(businessId);
    return response.success ? response.data ?? [] : [];
  } catch (e) {
    return [];
  }
});

// User Businesses Provider (for sellers)
final userBusinessesProvider = FutureProvider<List<Business>>((ref) async {
  final marketplaceService = ref.watch(marketplaceServiceProvider);
  
  try {
    final response = await marketplaceService.getUserBusinesses();
    return response.success ? response.data ?? [] : [];
  } catch (e) {
    return [];
  }
});

// Transaction History Provider
final transactionHistoryProvider = FutureProvider<List<Transaction>>((ref) async {
  final marketplaceService = ref.watch(marketplaceServiceProvider);
  
  try {
    final response = await marketplaceService.getTransactionHistory();
    return response.success ? response.data ?? [] : [];
  } catch (e) {
    return [];
  }
});

// Loading state providers for specific operations
final marketplaceLoadingProvider = Provider<bool>((ref) {
  return ref.watch(marketplaceProvider.select((state) => state.isLoading));
});

final marketplaceErrorProvider = Provider<String?>((ref) {
  return ref.watch(marketplaceProvider.select((state) => state.error));
});

final businessesProvider = Provider<List<Business>>((ref) {
  return ref.watch(marketplaceProvider.select((state) => state.businesses));
});

final featuredBusinessesProvider = Provider<List<Business>>((ref) {
  return ref.watch(marketplaceProvider.select((state) => state.featuredBusinesses));
});

final trendingBusinessesProvider = Provider<List<Business>>((ref) {
  return ref.watch(marketplaceProvider.select((state) => state.trendingBusinesses));
});

final investmentsProvider = Provider<List<Investment>>((ref) {
  return ref.watch(marketplaceProvider.select((state) => state.investments));
});

final portfolioProvider = Provider<Portfolio?>((ref) {
  return ref.watch(marketplaceProvider.select((state) => state.portfolio));
});

final marketplaceStatsProvider = Provider<MarketplaceStats?>((ref) {
  return ref.watch(marketplaceProvider.select((state) => state.stats));
});
