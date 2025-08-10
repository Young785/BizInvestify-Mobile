import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/api_service.dart';

// Dashboard Analytics Model
class DashboardAnalytics {
  final double totalRevenue;
  final int totalProducts;
  final int activeListings;
  final int unreadMessages;
  final int totalInvestments;
  final double totalInvestmentValue;
  final int pendingKyc;
  final int completedTransactions;

  DashboardAnalytics({
    required this.totalRevenue,
    required this.totalProducts,
    required this.activeListings,
    required this.unreadMessages,
    required this.totalInvestments,
    required this.totalInvestmentValue,
    required this.pendingKyc,
    required this.completedTransactions,
  });

  factory DashboardAnalytics.fromJson(Map<String, dynamic> json) {
    return DashboardAnalytics(
      totalRevenue: double.parse(json['total_revenue']?.toString() ?? '0'),
      totalProducts: json['total_products'] ?? 0,
      activeListings: json['active_listings'] ?? 0,
      unreadMessages: json['unread_messages'] ?? 0,
      totalInvestments: json['total_investments'] ?? 0,
      totalInvestmentValue: double.parse(json['total_investment_value']?.toString() ?? '0'),
      pendingKyc: json['pending_kyc'] ?? 0,
      completedTransactions: json['completed_transactions'] ?? 0,
    );
  }

  // Default analytics for demo
  factory DashboardAnalytics.defaultAnalytics() {
    return DashboardAnalytics(
      totalRevenue: 12450.0,
      totalProducts: 24,
      activeListings: 18,
      unreadMessages: 5,
      totalInvestments: 12,
      totalInvestmentValue: 45000.0,
      pendingKyc: 3,
      completedTransactions: 45,
    );
  }
}

// Dashboard State
class DashboardState {
  final DashboardAnalytics? analytics;
  final bool isLoading;
  final String? error;

  const DashboardState({
    this.analytics,
    this.isLoading = false,
    this.error,
  });

  DashboardState copyWith({
    DashboardAnalytics? analytics,
    bool? isLoading,
    String? error,
  }) {
    return DashboardState(
      analytics: analytics ?? this.analytics,
      isLoading: isLoading ?? this.isLoading,
      error: error ?? this.error,
    );
  }
}

// Dashboard Notifier
class DashboardNotifier extends StateNotifier<DashboardState> {
  final ApiService _apiService;

  DashboardNotifier(this._apiService) : super(const DashboardState());

  // Load Dashboard Analytics
  Future<void> loadAnalytics() async {
    state = state.copyWith(isLoading: true, error: null);
    
    try {
      final analyticsData = await _apiService.getDashboardStats();
      final analytics = DashboardAnalytics.fromJson(analyticsData);
      
      state = state.copyWith(
        analytics: analytics,
        isLoading: false,
      );
    } catch (e) {
      // For demo purposes, use default analytics if API fails
      state = state.copyWith(
        analytics: DashboardAnalytics.defaultAnalytics(),
        isLoading: false,
      );
    }
  }

  // Refresh Analytics
  Future<void> refreshAnalytics() async {
    await loadAnalytics();
  }

  // Clear error
  void clearError() {
    state = state.copyWith(error: null);
  }
}

// Providers
final dashboardProvider = StateNotifierProvider<DashboardNotifier, DashboardState>((ref) {
  return DashboardNotifier(apiService);
});

final analyticsProvider = Provider<DashboardAnalytics?>((ref) {
  return ref.watch(dashboardProvider).analytics;
});

final dashboardLoadingProvider = Provider<bool>((ref) {
  return ref.watch(dashboardProvider).isLoading;
});

final dashboardErrorProvider = Provider<String?>((ref) {
  return ref.watch(dashboardProvider).error;
});
