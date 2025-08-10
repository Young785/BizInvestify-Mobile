import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/api_service.dart';

// Product Model
class Product {
  final int id;
  final String name;
  final String description;
  final double price;
  final String category;
  final String status;
  final List<String> images;
  final List<String> tags;
  final int sellerId;
  final String sellerName;
  final DateTime createdAt;

  Product({
    required this.id,
    required this.name,
    required this.description,
    required this.price,
    required this.category,
    required this.status,
    required this.images,
    required this.tags,
    required this.sellerId,
    required this.sellerName,
    required this.createdAt,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'],
      name: json['name'],
      description: json['description'] ?? '',
      price: double.parse(json['price'].toString()),
      category: json['category'],
      status: json['status'] ?? 'active',
      images: List<String>.from(json['images'] ?? []),
      tags: List<String>.from(json['tags'] ?? []),
      sellerId: json['user_id'],
      sellerName: json['seller_name'] ?? 'Unknown Seller',
      createdAt: DateTime.parse(json['created_at']),
    );
  }
}

// Business Model
class Business {
  final int id;
  final String name;
  final String description;
  final String industry;
  final double valuation;
  final double fundingGoal;
  final double equityOffered;
  final String status;
  final List<String> documents;
  final int ownerId;
  final String ownerName;
  final DateTime createdAt;

  Business({
    required this.id,
    required this.name,
    required this.description,
    required this.industry,
    required this.valuation,
    required this.fundingGoal,
    required this.equityOffered,
    required this.status,
    required this.documents,
    required this.ownerId,
    required this.ownerName,
    required this.createdAt,
  });

  factory Business.fromJson(Map<String, dynamic> json) {
    return Business(
      id: json['id'],
      name: json['name'],
      description: json['description'] ?? '',
      industry: json['industry'],
      valuation: double.parse(json['valuation'].toString()),
      fundingGoal: double.parse(json['funding_goal'].toString()),
      equityOffered: double.parse(json['equity_offered'].toString()),
      status: json['status'] ?? 'active',
      documents: List<String>.from(json['documents'] ?? []),
      ownerId: json['user_id'],
      ownerName: json['owner_name'] ?? 'Unknown Owner',
      createdAt: DateTime.parse(json['created_at']),
    );
  }
}

// Marketplace State
class MarketplaceState {
  final List<Product> products;
  final List<Business> businesses;
  final List<Product> featuredProducts;
  final List<dynamic> trending; // can include both
  final List<String> categories;
  final bool isLoadingProducts;
  final bool isLoadingBusinesses;
  final bool isLoadingMeta;
  final String? error;
  final String? searchQuery;
  final String? selectedCategory;
  final String? selectedIndustry;

  const MarketplaceState({
    this.products = const [],
    this.businesses = const [],
    this.featuredProducts = const [],
    this.trending = const [],
    this.categories = const [],
    this.isLoadingProducts = false,
    this.isLoadingBusinesses = false,
    this.isLoadingMeta = false,
    this.error,
    this.searchQuery,
    this.selectedCategory,
    this.selectedIndustry,
  });

  MarketplaceState copyWith({
    List<Product>? products,
    List<Business>? businesses,
    List<Product>? featuredProducts,
    List<dynamic>? trending,
    List<String>? categories,
    bool? isLoadingProducts,
    bool? isLoadingBusinesses,
    bool? isLoadingMeta,
    String? error,
    String? searchQuery,
    String? selectedCategory,
    String? selectedIndustry,
  }) {
    return MarketplaceState(
      products: products ?? this.products,
      businesses: businesses ?? this.businesses,
      featuredProducts: featuredProducts ?? this.featuredProducts,
      trending: trending ?? this.trending,
      categories: categories ?? this.categories,
      isLoadingProducts: isLoadingProducts ?? this.isLoadingProducts,
      isLoadingBusinesses: isLoadingBusinesses ?? this.isLoadingBusinesses,
      isLoadingMeta: isLoadingMeta ?? this.isLoadingMeta,
      error: error ?? this.error,
      searchQuery: searchQuery ?? this.searchQuery,
      selectedCategory: selectedCategory ?? this.selectedCategory,
      selectedIndustry: selectedIndustry ?? this.selectedIndustry,
    );
  }
}

// Marketplace Notifier
class MarketplaceNotifier extends StateNotifier<MarketplaceState> {
  final ApiService _apiService;

  MarketplaceNotifier(this._apiService) : super(const MarketplaceState());

  // Load Products
  Future<void> loadProducts() async {
    state = state.copyWith(isLoadingProducts: true, error: null);
    
    try {
      final productsData = await _apiService.getPublicProducts();
      final products = productsData.map((json) => Product.fromJson(json)).toList();
      
      state = state.copyWith(
        products: products,
        isLoadingProducts: false,
      );
    } catch (e) {
      state = state.copyWith(
        error: e.toString(),
        isLoadingProducts: false,
      );
    }
  }

  // Load Businesses
  Future<void> loadBusinesses() async {
    state = state.copyWith(isLoadingBusinesses: true, error: null);
    
    try {
      final businessesData = await _apiService.getPublicBusinesses();
      final businesses = businessesData.map((json) => Business.fromJson(json)).toList();
      
      state = state.copyWith(
        businesses: businesses,
        isLoadingBusinesses: false,
      );
    } catch (e) {
      state = state.copyWith(
        error: e.toString(),
        isLoadingBusinesses: false,
      );
    }
  }

  // Load marketplace meta: featured, trending, categories
  Future<void> loadMarketplaceMeta() async {
    state = state.copyWith(isLoadingMeta: true, error: null);
    try {
      final results = await Future.wait([
        _apiService.getPublicTrending(),
        _apiService.getPublicFeatured(),
        _apiService.getPublicCategories(),
      ]);

      final trending = List<Map<String, dynamic>>.from(results[0] as List);
      final featured = List<Map<String, dynamic>>.from(results[1] as List);
      final categories = List<Map<String, dynamic>>.from(results[2] as List)
          .map((e) => (e['name'] ?? e['title'] ?? e['slug'] ?? '').toString())
          .where((e) => e.isNotEmpty)
          .toList();

      state = state.copyWith(
        trending: trending,
        featuredProducts: featured.map((e) => Product.fromJson(e)).toList(),
        categories: categories,
        isLoadingMeta: false,
      );
    } catch (e) {
      state = state.copyWith(isLoadingMeta: false, error: e.toString());
    }
  }

  // Search Products
  Future<void> searchProducts(String query) async {
    state = state.copyWith(isLoadingProducts: true, error: null, searchQuery: query);
    
    try {
      final productsData = await _apiService.getProducts(search: query);
      final products = productsData.map((json) => Product.fromJson(json)).toList();
      
      state = state.copyWith(
        products: products,
        isLoadingProducts: false,
      );
    } catch (e) {
      state = state.copyWith(
        error: e.toString(),
        isLoadingProducts: false,
      );
    }
  }

  // Search Businesses
  Future<void> searchBusinesses(String query) async {
    state = state.copyWith(isLoadingBusinesses: true, error: null, searchQuery: query);
    
    try {
      final businessesData = await _apiService.getBusinesses(search: query);
      final businesses = businessesData.map((json) => Business.fromJson(json)).toList();
      
      state = state.copyWith(
        businesses: businesses,
        isLoadingBusinesses: false,
      );
    } catch (e) {
      state = state.copyWith(
        error: e.toString(),
        isLoadingBusinesses: false,
      );
    }
  }

  // Filter Products
  Future<void> filterProducts({String? category, String? search}) async {
    state = state.copyWith(
      isLoadingProducts: true,
      error: null,
      selectedCategory: category,
      searchQuery: search,
    );
    
    try {
      final productsData = await _apiService.getProducts(
        category: category,
        search: search,
      );
      final products = productsData.map((json) => Product.fromJson(json)).toList();
      
      state = state.copyWith(
        products: products,
        isLoadingProducts: false,
      );
    } catch (e) {
      state = state.copyWith(
        error: e.toString(),
        isLoadingProducts: false,
      );
    }
  }

  // Filter Businesses
  Future<void> filterBusinesses({String? industry, String? search}) async {
    state = state.copyWith(
      isLoadingBusinesses: true,
      error: null,
      selectedIndustry: industry,
      searchQuery: search,
    );
    
    try {
      final businessesData = await _apiService.getBusinesses(
        industry: industry,
        search: search,
      );
      final businesses = businessesData.map((json) => Business.fromJson(json)).toList();
      
      state = state.copyWith(
        businesses: businesses,
        isLoadingBusinesses: false,
      );
    } catch (e) {
      state = state.copyWith(
        error: e.toString(),
        isLoadingBusinesses: false,
      );
    }
  }

  // Clear error
  void clearError() {
    state = state.copyWith(error: null);
  }
}

// Providers
final marketplaceProvider = StateNotifierProvider<MarketplaceNotifier, MarketplaceState>((ref) {
  return MarketplaceNotifier(apiService);
});

final productsProvider = Provider<List<Product>>((ref) {
  return ref.watch(marketplaceProvider).products;
});

final businessesProvider = Provider<List<Business>>((ref) {
  return ref.watch(marketplaceProvider).businesses;
});

final featuredProductsProvider = Provider<List<Product>>((ref) {
  return ref.watch(marketplaceProvider).featuredProducts;
});

final categoriesProvider = Provider<List<String>>((ref) {
  return ref.watch(marketplaceProvider).categories;
});

final marketplaceErrorProvider = Provider<String?>((ref) {
  return ref.watch(marketplaceProvider).error;
});
