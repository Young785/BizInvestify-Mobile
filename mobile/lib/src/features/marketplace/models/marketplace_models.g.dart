// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'marketplace_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$BusinessImpl _$$BusinessImplFromJson(Map<String, dynamic> json) =>
    _$BusinessImpl(
      id: json['id'] as String,
      sellerId: json['seller_id'] as String,
      name: json['name'] as String,
      description: json['description'] as String,
      industry: json['industry'] as String,
      valuation: (json['valuation'] as num).toDouble(),
      fundingGoal: (json['funding_goal'] as num).toDouble(),
      equityOffered: (json['equity_offered'] as num).toDouble(),
      pitchDeckUrl: json['pitch_deck_url'] as String?,
      businessPlan: json['business_plan'] as String?,
      revenue: (json['revenue'] as num).toDouble(),
      profitMargin: (json['profit_margin'] as num).toDouble(),
      employeesCount: (json['employees_count'] as num).toInt(),
      foundedYear: (json['founded_year'] as num).toInt(),
      location: json['location'] as String,
      images: (json['images'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          const [],
      status: json['status'] as String? ?? 'active',
      viewsCount: (json['views_count'] as num?)?.toInt() ?? 0,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
      seller: json['seller'] == null
          ? null
          : BusinessSeller.fromJson(json['seller'] as Map<String, dynamic>),
      investments: (json['investments'] as List<dynamic>?)
              ?.map((e) => Investment.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const [],
    );

Map<String, dynamic> _$$BusinessImplToJson(_$BusinessImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'seller_id': instance.sellerId,
      'name': instance.name,
      'description': instance.description,
      'industry': instance.industry,
      'valuation': instance.valuation,
      'funding_goal': instance.fundingGoal,
      'equity_offered': instance.equityOffered,
      'pitch_deck_url': instance.pitchDeckUrl,
      'business_plan': instance.businessPlan,
      'revenue': instance.revenue,
      'profit_margin': instance.profitMargin,
      'employees_count': instance.employeesCount,
      'founded_year': instance.foundedYear,
      'location': instance.location,
      'images': instance.images,
      'status': instance.status,
      'views_count': instance.viewsCount,
      'created_at': instance.createdAt.toIso8601String(),
      'updated_at': instance.updatedAt.toIso8601String(),
      'seller': instance.seller,
      'investments': instance.investments,
    };

_$BusinessSellerImpl _$$BusinessSellerImplFromJson(Map<String, dynamic> json) =>
    _$BusinessSellerImpl(
      id: json['id'] as String,
      name: json['name'] as String,
      firstName: json['first_name'] as String?,
      lastName: json['last_name'] as String?,
      email: json['email'] as String,
      avatarUrl: json['avatar_url'] as String?,
      trustScore: (json['trust_score'] as num?)?.toDouble() ?? 0.0,
      isVerified: json['is_verified'] as bool? ?? false,
    );

Map<String, dynamic> _$$BusinessSellerImplToJson(
        _$BusinessSellerImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'first_name': instance.firstName,
      'last_name': instance.lastName,
      'email': instance.email,
      'avatar_url': instance.avatarUrl,
      'trust_score': instance.trustScore,
      'is_verified': instance.isVerified,
    };

_$InvestmentImpl _$$InvestmentImplFromJson(Map<String, dynamic> json) =>
    _$InvestmentImpl(
      id: json['id'] as String,
      businessId: json['business_id'] as String,
      investorId: json['investor_id'] as String,
      amount: (json['amount'] as num).toDouble(),
      equityPercentage: (json['equity_percentage'] as num).toDouble(),
      status: json['status'] as String? ?? 'pending',
      message: json['message'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
      business: json['business'] == null
          ? null
          : Business.fromJson(json['business'] as Map<String, dynamic>),
      investor: json['investor'] == null
          ? null
          : Investor.fromJson(json['investor'] as Map<String, dynamic>),
    );

Map<String, dynamic> _$$InvestmentImplToJson(_$InvestmentImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'business_id': instance.businessId,
      'investor_id': instance.investorId,
      'amount': instance.amount,
      'equity_percentage': instance.equityPercentage,
      'status': instance.status,
      'message': instance.message,
      'created_at': instance.createdAt.toIso8601String(),
      'updated_at': instance.updatedAt.toIso8601String(),
      'business': instance.business,
      'investor': instance.investor,
    };

_$InvestorImpl _$$InvestorImplFromJson(Map<String, dynamic> json) =>
    _$InvestorImpl(
      id: json['id'] as String,
      name: json['name'] as String,
      firstName: json['first_name'] as String?,
      lastName: json['last_name'] as String?,
      email: json['email'] as String,
      avatarUrl: json['avatar_url'] as String?,
      trustScore: (json['trust_score'] as num?)?.toDouble() ?? 0.0,
      isVerified: json['is_verified'] as bool? ?? false,
    );

Map<String, dynamic> _$$InvestorImplToJson(_$InvestorImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'first_name': instance.firstName,
      'last_name': instance.lastName,
      'email': instance.email,
      'avatar_url': instance.avatarUrl,
      'trust_score': instance.trustScore,
      'is_verified': instance.isVerified,
    };

_$PortfolioImpl _$$PortfolioImplFromJson(Map<String, dynamic> json) =>
    _$PortfolioImpl(
      totalInvested: (json['total_invested'] as num?)?.toDouble() ?? 0.0,
      totalReturns: (json['total_returns'] as num?)?.toDouble() ?? 0.0,
      activeInvestments: (json['active_investments'] as num?)?.toInt() ?? 0,
      portfolioValue: (json['portfolio_value'] as num?)?.toDouble() ?? 0.0,
      investments: (json['investments'] as List<dynamic>?)
              ?.map((e) => Investment.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const [],
    );

Map<String, dynamic> _$$PortfolioImplToJson(_$PortfolioImpl instance) =>
    <String, dynamic>{
      'total_invested': instance.totalInvested,
      'total_returns': instance.totalReturns,
      'active_investments': instance.activeInvestments,
      'portfolio_value': instance.portfolioValue,
      'investments': instance.investments,
    };

_$CreateBusinessRequestImpl _$$CreateBusinessRequestImplFromJson(
        Map<String, dynamic> json) =>
    _$CreateBusinessRequestImpl(
      name: json['name'] as String,
      description: json['description'] as String,
      industry: json['industry'] as String,
      valuation: (json['valuation'] as num).toDouble(),
      fundingGoal: (json['funding_goal'] as num).toDouble(),
      equityOffered: (json['equity_offered'] as num).toDouble(),
      businessPlan: json['businessPlan'] as String?,
      revenue: (json['revenue'] as num).toDouble(),
      profitMargin: (json['profit_margin'] as num).toDouble(),
      employeesCount: (json['employees_count'] as num).toInt(),
      foundedYear: (json['founded_year'] as num).toInt(),
      location: json['location'] as String,
      images: (json['images'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          const [],
    );

Map<String, dynamic> _$$CreateBusinessRequestImplToJson(
        _$CreateBusinessRequestImpl instance) =>
    <String, dynamic>{
      'name': instance.name,
      'description': instance.description,
      'industry': instance.industry,
      'valuation': instance.valuation,
      'funding_goal': instance.fundingGoal,
      'equity_offered': instance.equityOffered,
      'businessPlan': instance.businessPlan,
      'revenue': instance.revenue,
      'profit_margin': instance.profitMargin,
      'employees_count': instance.employeesCount,
      'founded_year': instance.foundedYear,
      'location': instance.location,
      'images': instance.images,
    };

_$CreateInvestmentRequestImpl _$$CreateInvestmentRequestImplFromJson(
        Map<String, dynamic> json) =>
    _$CreateInvestmentRequestImpl(
      businessId: json['business_id'] as String,
      amount: (json['amount'] as num).toDouble(),
      message: json['message'] as String?,
    );

Map<String, dynamic> _$$CreateInvestmentRequestImplToJson(
        _$CreateInvestmentRequestImpl instance) =>
    <String, dynamic>{
      'business_id': instance.businessId,
      'amount': instance.amount,
      'message': instance.message,
    };

_$BusinessFiltersImpl _$$BusinessFiltersImplFromJson(
        Map<String, dynamic> json) =>
    _$BusinessFiltersImpl(
      industry: json['industry'] as String?,
      minValuation: (json['min_valuation'] as num?)?.toDouble(),
      maxValuation: (json['max_valuation'] as num?)?.toDouble(),
      minFunding: (json['min_funding'] as num?)?.toDouble(),
      maxFunding: (json['max_funding'] as num?)?.toDouble(),
      location: json['location'] as String?,
      search: json['search'] as String?,
      sortBy: json['sort_by'] as String? ?? 'created_at',
      sortOrder: json['sort_order'] as String? ?? 'desc',
    );

Map<String, dynamic> _$$BusinessFiltersImplToJson(
        _$BusinessFiltersImpl instance) =>
    <String, dynamic>{
      'industry': instance.industry,
      'min_valuation': instance.minValuation,
      'max_valuation': instance.maxValuation,
      'min_funding': instance.minFunding,
      'max_funding': instance.maxFunding,
      'location': instance.location,
      'search': instance.search,
      'sort_by': instance.sortBy,
      'sort_order': instance.sortOrder,
    };

_$MarketplaceStatsImpl _$$MarketplaceStatsImplFromJson(
        Map<String, dynamic> json) =>
    _$MarketplaceStatsImpl(
      totalBusinesses: (json['total_businesses'] as num?)?.toInt() ?? 0,
      totalInvestments: (json['total_investments'] as num?)?.toInt() ?? 0,
      totalInvestmentVolume:
          (json['total_investment_volume'] as num?)?.toDouble() ?? 0.0,
      activeInvestors: (json['active_investors'] as num?)?.toInt() ?? 0,
      averageInvestment:
          (json['average_investment'] as num?)?.toDouble() ?? 0.0,
      topIndustries: (json['top_industries'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          const [],
    );

Map<String, dynamic> _$$MarketplaceStatsImplToJson(
        _$MarketplaceStatsImpl instance) =>
    <String, dynamic>{
      'total_businesses': instance.totalBusinesses,
      'total_investments': instance.totalInvestments,
      'total_investment_volume': instance.totalInvestmentVolume,
      'active_investors': instance.activeInvestors,
      'average_investment': instance.averageInvestment,
      'top_industries': instance.topIndustries,
    };

_$PaymentIntentImpl _$$PaymentIntentImplFromJson(Map<String, dynamic> json) =>
    _$PaymentIntentImpl(
      id: json['id'] as String,
      clientSecret: json['client_secret'] as String,
      amount: (json['amount'] as num).toDouble(),
      currency: json['currency'] as String,
      status: json['status'] as String,
      paymentMethodTypes: (json['payment_method_types'] as List<dynamic>?)
              ?.map((e) => e as String)
              .toList() ??
          const [],
    );

Map<String, dynamic> _$$PaymentIntentImplToJson(_$PaymentIntentImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'client_secret': instance.clientSecret,
      'amount': instance.amount,
      'currency': instance.currency,
      'status': instance.status,
      'payment_method_types': instance.paymentMethodTypes,
    };

_$TransactionImpl _$$TransactionImplFromJson(Map<String, dynamic> json) =>
    _$TransactionImpl(
      id: json['id'] as String,
      userId: json['user_id'] as String,
      listingId: json['listing_id'] as String?,
      listingType: json['listing_type'] as String?,
      amount: (json['amount'] as num).toDouble(),
      currency: json['currency'] as String,
      type: json['type'] as String,
      status: json['status'] as String,
      stripePaymentIntentId: json['stripe_payment_intent_id'] as String?,
      paymentMethod: json['payment_method'] as String?,
      description: json['description'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
    );

Map<String, dynamic> _$$TransactionImplToJson(_$TransactionImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'user_id': instance.userId,
      'listing_id': instance.listingId,
      'listing_type': instance.listingType,
      'amount': instance.amount,
      'currency': instance.currency,
      'type': instance.type,
      'status': instance.status,
      'stripe_payment_intent_id': instance.stripePaymentIntentId,
      'payment_method': instance.paymentMethod,
      'description': instance.description,
      'created_at': instance.createdAt.toIso8601String(),
      'updated_at': instance.updatedAt.toIso8601String(),
    };

_$MarketplaceApiResponseImpl<T> _$$MarketplaceApiResponseImplFromJson<T>(
  Map<String, dynamic> json,
  T Function(Object? json) fromJsonT,
) =>
    _$MarketplaceApiResponseImpl<T>(
      success: json['success'] as bool,
      message: json['message'] as String?,
      data: _$nullableGenericFromJson(json['data'], fromJsonT),
      errors: json['errors'] as Map<String, dynamic>?,
      currentPage: (json['current_page'] as num?)?.toInt(),
      lastPage: (json['last_page'] as num?)?.toInt(),
      perPage: (json['per_page'] as num?)?.toInt(),
      total: (json['total'] as num?)?.toInt(),
    );

Map<String, dynamic> _$$MarketplaceApiResponseImplToJson<T>(
  _$MarketplaceApiResponseImpl<T> instance,
  Object? Function(T value) toJsonT,
) =>
    <String, dynamic>{
      'success': instance.success,
      'message': instance.message,
      'data': _$nullableGenericToJson(instance.data, toJsonT),
      'errors': instance.errors,
      'current_page': instance.currentPage,
      'last_page': instance.lastPage,
      'per_page': instance.perPage,
      'total': instance.total,
    };

T? _$nullableGenericFromJson<T>(
  Object? input,
  T Function(Object? json) fromJson,
) =>
    input == null ? null : fromJson(input);

Object? _$nullableGenericToJson<T>(
  T? input,
  Object? Function(T value) toJson,
) =>
    input == null ? null : toJson(input);
