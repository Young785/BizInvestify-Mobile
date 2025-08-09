// ignore_for_file: invalid_annotation_target

import 'package:freezed_annotation/freezed_annotation.dart';

part 'marketplace_models.freezed.dart';
part 'marketplace_models.g.dart';

// Business Model
@freezed
class Business with _$Business {
  const factory Business({
    required String id,
    @JsonKey(name: 'seller_id') required String sellerId,
    required String name,
    required String description,
    required String industry,
    required double valuation,
    @JsonKey(name: 'funding_goal') required double fundingGoal,
    @JsonKey(name: 'equity_offered') required double equityOffered,
    @JsonKey(name: 'pitch_deck_url') String? pitchDeckUrl,
    @JsonKey(name: 'business_plan') String? businessPlan,
    required double revenue,
    @JsonKey(name: 'profit_margin') required double profitMargin,
    @JsonKey(name: 'employees_count') required int employeesCount,
    @JsonKey(name: 'founded_year') required int foundedYear,
    required String location,
    @Default([]) List<String> images,
    @Default('active') String status,
    @JsonKey(name: 'views_count') @Default(0) int viewsCount,
    @JsonKey(name: 'created_at') required DateTime createdAt,
    @JsonKey(name: 'updated_at') required DateTime updatedAt,
    BusinessSeller? seller,
    @Default([]) List<Investment> investments,
  }) = _Business;

  factory Business.fromJson(Map<String, dynamic> json) => _$BusinessFromJson(json);
}

// Business Seller Model (simplified user for business listings)
@freezed
class BusinessSeller with _$BusinessSeller {
  const factory BusinessSeller({
    required String id,
    required String name,
    @JsonKey(name: 'first_name') String? firstName,
    @JsonKey(name: 'last_name') String? lastName,
    required String email,
    @JsonKey(name: 'avatar_url') String? avatarUrl,
    @JsonKey(name: 'trust_score') @Default(0.0) double trustScore,
    @JsonKey(name: 'is_verified') @Default(false) bool isVerified,
  }) = _BusinessSeller;

  factory BusinessSeller.fromJson(Map<String, dynamic> json) => _$BusinessSellerFromJson(json);
}

// Investment Model
@freezed
class Investment with _$Investment {
  const factory Investment({
    required String id,
    @JsonKey(name: 'business_id') required String businessId,
    @JsonKey(name: 'investor_id') required String investorId,
    required double amount,
    @JsonKey(name: 'equity_percentage') required double equityPercentage,
    @Default('pending') String status,
    String? message,
    @JsonKey(name: 'created_at') required DateTime createdAt,
    @JsonKey(name: 'updated_at') required DateTime updatedAt,
    Business? business,
    Investor? investor,
  }) = _Investment;

  factory Investment.fromJson(Map<String, dynamic> json) => _$InvestmentFromJson(json);
}

// Investor Model (simplified user for investments)
@freezed
class Investor with _$Investor {
  const factory Investor({
    required String id,
    required String name,
    @JsonKey(name: 'first_name') String? firstName,
    @JsonKey(name: 'last_name') String? lastName,
    required String email,
    @JsonKey(name: 'avatar_url') String? avatarUrl,
    @JsonKey(name: 'trust_score') @Default(0.0) double trustScore,
    @JsonKey(name: 'is_verified') @Default(false) bool isVerified,
  }) = _Investor;

  factory Investor.fromJson(Map<String, dynamic> json) => _$InvestorFromJson(json);
}

// Portfolio Model
@freezed
class Portfolio with _$Portfolio {
  const factory Portfolio({
    @JsonKey(name: 'total_invested') @Default(0.0) double totalInvested,
    @JsonKey(name: 'total_returns') @Default(0.0) double totalReturns,
    @JsonKey(name: 'active_investments') @Default(0) int activeInvestments,
    @JsonKey(name: 'portfolio_value') @Default(0.0) double portfolioValue,
    @Default([]) List<Investment> investments,
  }) = _Portfolio;

  factory Portfolio.fromJson(Map<String, dynamic> json) => _$PortfolioFromJson(json);
}

// Business Listing Request Model
@freezed
class CreateBusinessRequest with _$CreateBusinessRequest {
  const factory CreateBusinessRequest({
    required String name,
    required String description,
    required String industry,
    required double valuation,
    @JsonKey(name: 'funding_goal') required double fundingGoal,
    @JsonKey(name: 'equity_offered') required double equityOffered,
    String? businessPlan,
    required double revenue,
    @JsonKey(name: 'profit_margin') required double profitMargin,
    @JsonKey(name: 'employees_count') required int employeesCount,
    @JsonKey(name: 'founded_year') required int foundedYear,
    required String location,
    @Default([]) List<String> images,
  }) = _CreateBusinessRequest;

  factory CreateBusinessRequest.fromJson(Map<String, dynamic> json) => _$CreateBusinessRequestFromJson(json);
}

// Investment Request Model
@freezed
class CreateInvestmentRequest with _$CreateInvestmentRequest {
  const factory CreateInvestmentRequest({
    @JsonKey(name: 'business_id') required String businessId,
    required double amount,
    String? message,
  }) = _CreateInvestmentRequest;

  factory CreateInvestmentRequest.fromJson(Map<String, dynamic> json) => _$CreateInvestmentRequestFromJson(json);
}

// Business Filters Model
@freezed
class BusinessFilters with _$BusinessFilters {
  const factory BusinessFilters({
    String? industry,
    @JsonKey(name: 'min_valuation') double? minValuation,
    @JsonKey(name: 'max_valuation') double? maxValuation,
    @JsonKey(name: 'min_funding') double? minFunding,
    @JsonKey(name: 'max_funding') double? maxFunding,
    String? location,
    String? search,
    @JsonKey(name: 'sort_by') @Default('created_at') String sortBy,
    @JsonKey(name: 'sort_order') @Default('desc') String sortOrder,
  }) = _BusinessFilters;

  factory BusinessFilters.fromJson(Map<String, dynamic> json) => _$BusinessFiltersFromJson(json);
}

// Marketplace Statistics Model
@freezed
class MarketplaceStats with _$MarketplaceStats {
  const factory MarketplaceStats({
    @JsonKey(name: 'total_businesses') @Default(0) int totalBusinesses,
    @JsonKey(name: 'total_investments') @Default(0) int totalInvestments,
    @JsonKey(name: 'total_investment_volume') @Default(0.0) double totalInvestmentVolume,
    @JsonKey(name: 'active_investors') @Default(0) int activeInvestors,
    @JsonKey(name: 'average_investment') @Default(0.0) double averageInvestment,
    @JsonKey(name: 'top_industries') @Default([]) List<String> topIndustries,
  }) = _MarketplaceStats;

  factory MarketplaceStats.fromJson(Map<String, dynamic> json) => _$MarketplaceStatsFromJson(json);
}

// Payment Intent Model
@freezed
class PaymentIntent with _$PaymentIntent {
  const factory PaymentIntent({
    required String id,
    @JsonKey(name: 'client_secret') required String clientSecret,
    required double amount,
    required String currency,
    required String status,
    @JsonKey(name: 'payment_method_types') @Default([]) List<String> paymentMethodTypes,
  }) = _PaymentIntent;

  factory PaymentIntent.fromJson(Map<String, dynamic> json) => _$PaymentIntentFromJson(json);
}

// Transaction Model
@freezed
class Transaction with _$Transaction {
  const factory Transaction({
    required String id,
    @JsonKey(name: 'user_id') required String userId,
    @JsonKey(name: 'listing_id') String? listingId,
    @JsonKey(name: 'listing_type') String? listingType,
    required double amount,
    required String currency,
    required String type,
    required String status,
    @JsonKey(name: 'stripe_payment_intent_id') String? stripePaymentIntentId,
    @JsonKey(name: 'payment_method') String? paymentMethod,
    String? description,
    @JsonKey(name: 'created_at') required DateTime createdAt,
    @JsonKey(name: 'updated_at') required DateTime updatedAt,
  }) = _Transaction;

  factory Transaction.fromJson(Map<String, dynamic> json) => _$TransactionFromJson(json);
}

// API Response wrapper for marketplace data
@Freezed(genericArgumentFactories: true)
class MarketplaceApiResponse<T> with _$MarketplaceApiResponse<T> {
  const factory MarketplaceApiResponse({
    required bool success,
    String? message,
    T? data,
    Map<String, dynamic>? errors,
    @JsonKey(name: 'current_page') int? currentPage,
    @JsonKey(name: 'last_page') int? lastPage,
    @JsonKey(name: 'per_page') int? perPage,
    int? total,
  }) = _MarketplaceApiResponse<T>;

  factory MarketplaceApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(Object?) fromJsonT,
  ) => _$MarketplaceApiResponseFromJson(json, fromJsonT);
}

// Industry options enum
enum Industry {
  technology('Technology'),
  healthcare('Healthcare'),
  finance('Finance'),
  retail('Retail'),
  manufacturing('Manufacturing'),
  realestate('Real Estate'),
  food('Food & Beverage'),
  education('Education'),
  transportation('Transportation'),
  energy('Energy'),
  agriculture('Agriculture'),
  entertainment('Entertainment'),
  consulting('Consulting'),
  other('Other');

  const Industry(this.displayName);
  final String displayName;
}

// Investment status enum
enum InvestmentStatus {
  pending('pending'),
  approved('approved'),
  rejected('rejected'),
  completed('completed'),
  cancelled('cancelled');

  const InvestmentStatus(this.value);
  final String value;
}

// Business status enum
enum BusinessStatus {
  active('active'),
  inactive('inactive'),
  pending('pending'),
  rejected('rejected'),
  sold('sold');

  const BusinessStatus(this.value);
  final String value;
}
