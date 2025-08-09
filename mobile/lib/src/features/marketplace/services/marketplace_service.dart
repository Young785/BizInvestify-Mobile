import 'package:dio/dio.dart';
import '../../../core/services/api_service.dart';
import '../models/marketplace_models.dart';

class MarketplaceService {
  final ApiService _apiService;

  MarketplaceService(this._apiService);

  // Business Operations
  Future<MarketplaceApiResponse<List<Business>>> getBusinesses({
    BusinessFilters? filters,
    int page = 1,
    int perPage = 10,
  }) async {
    try {
      final queryParams = <String, dynamic>{
        'page': page,
        'per_page': perPage,
        if (filters?.industry != null) 'industry': filters!.industry,
        if (filters?.minValuation != null) 'min_valuation': filters!.minValuation,
        if (filters?.maxValuation != null) 'max_valuation': filters!.maxValuation,
        if (filters?.minFunding != null) 'min_funding': filters!.minFunding,
        if (filters?.maxFunding != null) 'max_funding': filters!.maxFunding,
        if (filters?.location != null) 'location': filters!.location,
        if (filters?.search != null) 'search': filters!.search,
        if (filters?.sortBy != null) 'sort_by': filters!.sortBy,
        if (filters?.sortOrder != null) 'sort_order': filters!.sortOrder,
      };

      final response = await _apiService.get(
        '/businesses',
        queryParameters: queryParams,
      );

      if (response.data['success'] == true) {
        final businesses = (response.data['data'] as List)
            .map((json) => Business.fromJson(json))
            .toList();
        
        return MarketplaceApiResponse<List<Business>>(
          success: true,
          data: businesses,
          currentPage: response.data['current_page'],
          lastPage: response.data['last_page'],
          perPage: response.data['per_page'],
          total: response.data['total'],
        );
      }

      return MarketplaceApiResponse<List<Business>>(
        success: false,
        message: response.data['message'] ?? 'Failed to fetch businesses',
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<List<Business>>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
      );
    }
  }

  Future<MarketplaceApiResponse<Business>> getBusiness(String businessId) async {
    try {
      final response = await _apiService.get('/businesses/$businessId');

      if (response.data['success'] == true) {
        final business = Business.fromJson(response.data['data']);
        return MarketplaceApiResponse<Business>(
          success: true,
          data: business,
        );
      }

      return MarketplaceApiResponse<Business>(
        success: false,
        message: response.data['message'] ?? 'Failed to fetch business',
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<Business>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
      );
    }
  }

  Future<MarketplaceApiResponse<List<Business>>> getFeaturedBusinesses() async {
    try {
      final response = await _apiService.get('/businesses/featured');

      if (response.data['success'] == true) {
        final businesses = (response.data['data'] as List)
            .map((json) => Business.fromJson(json))
            .toList();
        
        return MarketplaceApiResponse<List<Business>>(
          success: true,
          data: businesses,
        );
      }

      return MarketplaceApiResponse<List<Business>>(
        success: false,
        message: response.data['message'] ?? 'Failed to fetch featured businesses',
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<List<Business>>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
      );
    }
  }

  Future<MarketplaceApiResponse<List<Business>>> getTrendingBusinesses() async {
    try {
      final response = await _apiService.get('/businesses/trending');

      if (response.data['success'] == true) {
        final businesses = (response.data['data'] as List)
            .map((json) => Business.fromJson(json))
            .toList();
        
        return MarketplaceApiResponse<List<Business>>(
          success: true,
          data: businesses,
        );
      }

      return MarketplaceApiResponse<List<Business>>(
        success: false,
        message: response.data['message'] ?? 'Failed to fetch trending businesses',
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<List<Business>>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
      );
    }
  }

  Future<MarketplaceApiResponse<Business>> createBusiness(CreateBusinessRequest request) async {
    try {
      final response = await _apiService.post(
        '/businesses',
        data: request.toJson(),
      );

      if (response.data['success'] == true) {
        final business = Business.fromJson(response.data['data']);
        return MarketplaceApiResponse<Business>(
          success: true,
          data: business,
          message: 'Business created successfully',
        );
      }

      return MarketplaceApiResponse<Business>(
        success: false,
        message: response.data['message'] ?? 'Failed to create business',
        errors: response.data['errors'],
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<Business>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
        errors: e.response?.data['errors'],
      );
    }
  }

  Future<MarketplaceApiResponse<List<Business>>> getUserBusinesses() async {
    try {
      final response = await _apiService.get('/businesses/user');

      if (response.data['success'] == true) {
        final businesses = (response.data['data'] as List)
            .map((json) => Business.fromJson(json))
            .toList();
        
        return MarketplaceApiResponse<List<Business>>(
          success: true,
          data: businesses,
        );
      }

      return MarketplaceApiResponse<List<Business>>(
        success: false,
        message: response.data['message'] ?? 'Failed to fetch user businesses',
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<List<Business>>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
      );
    }
  }

  // Investment Operations
  Future<MarketplaceApiResponse<List<Investment>>> getInvestments({
    bool asBusinessOwner = false,
    String? status,
    int page = 1,
    int perPage = 10,
  }) async {
    try {
      final queryParams = <String, dynamic>{
        'page': page,
        'per_page': perPage,
        if (asBusinessOwner) 'as_business_owner': true,
        if (status != null) 'status': status,
      };

      final response = await _apiService.get(
        '/investments',
        queryParameters: queryParams,
      );

      if (response.data['success'] == true) {
        final investments = (response.data['data'] as List)
            .map((json) => Investment.fromJson(json))
            .toList();
        
        return MarketplaceApiResponse<List<Investment>>(
          success: true,
          data: investments,
          currentPage: response.data['current_page'],
          lastPage: response.data['last_page'],
          perPage: response.data['per_page'],
          total: response.data['total'],
        );
      }

      return MarketplaceApiResponse<List<Investment>>(
        success: false,
        message: response.data['message'] ?? 'Failed to fetch investments',
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<List<Investment>>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
      );
    }
  }

  Future<MarketplaceApiResponse<Investment>> createInvestment(CreateInvestmentRequest request) async {
    try {
      final response = await _apiService.post(
        '/investments',
        data: request.toJson(),
      );

      if (response.data['success'] == true) {
        final investment = Investment.fromJson(response.data['data']);
        return MarketplaceApiResponse<Investment>(
          success: true,
          data: investment,
          message: 'Investment created successfully',
        );
      }

      return MarketplaceApiResponse<Investment>(
        success: false,
        message: response.data['message'] ?? 'Failed to create investment',
        errors: response.data['errors'],
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<Investment>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
        errors: e.response?.data['errors'],
      );
    }
  }

  Future<MarketplaceApiResponse<Portfolio>> getUserPortfolio() async {
    try {
      final response = await _apiService.get('/investments/user/portfolio');

      if (response.data['success'] == true) {
        final portfolio = Portfolio.fromJson(response.data['data']);
        return MarketplaceApiResponse<Portfolio>(
          success: true,
          data: portfolio,
        );
      }

      return MarketplaceApiResponse<Portfolio>(
        success: false,
        message: response.data['message'] ?? 'Failed to fetch portfolio',
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<Portfolio>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
      );
    }
  }

  Future<MarketplaceApiResponse<List<Investment>>> getMyInvestments() async {
    try {
      final response = await _apiService.get('/investments/my-investments');

      if (response.data['success'] == true) {
        final investments = (response.data['data'] as List)
            .map((json) => Investment.fromJson(json))
            .toList();
        
        return MarketplaceApiResponse<List<Investment>>(
          success: true,
          data: investments,
        );
      }

      return MarketplaceApiResponse<List<Investment>>(
        success: false,
        message: response.data['message'] ?? 'Failed to fetch investments',
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<List<Investment>>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
      );
    }
  }

  Future<MarketplaceApiResponse<List<Investment>>> getBusinessInvestments(String businessId) async {
    try {
      final response = await _apiService.get('/businesses/$businessId/investments');

      if (response.data['success'] == true) {
        final investments = (response.data['data'] as List)
            .map((json) => Investment.fromJson(json))
            .toList();
        
        return MarketplaceApiResponse<List<Investment>>(
          success: true,
          data: investments,
        );
      }

      return MarketplaceApiResponse<List<Investment>>(
        success: false,
        message: response.data['message'] ?? 'Failed to fetch business investments',
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<List<Investment>>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
      );
    }
  }

  // Payment Operations
  Future<MarketplaceApiResponse<PaymentIntent>> createInvestmentPaymentIntent({
    required String businessId,
    required double amount,
    String? message,
  }) async {
    try {
      final response = await _apiService.post('/payments/investment-intent', data: {
        'business_id': businessId,
        'amount': amount,
        if (message != null) 'message': message,
      });

      if (response.data['success'] == true) {
        final paymentIntent = PaymentIntent.fromJson(response.data['data']);
        return MarketplaceApiResponse<PaymentIntent>(
          success: true,
          data: paymentIntent,
        );
      }

      return MarketplaceApiResponse<PaymentIntent>(
        success: false,
        message: response.data['message'] ?? 'Failed to create payment intent',
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<PaymentIntent>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
      );
    }
  }

  Future<MarketplaceApiResponse<bool>> confirmPayment(String paymentIntentId) async {
    try {
      final response = await _apiService.post('/payments/confirm', data: {
        'payment_intent_id': paymentIntentId,
      });

      return MarketplaceApiResponse<bool>(
        success: response.data['success'] == true,
        data: response.data['success'] == true,
        message: response.data['message'],
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<bool>(
        success: false,
        data: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
      );
    }
  }

  // Search Operations
  Future<MarketplaceApiResponse<List<Business>>> searchBusinesses({
    required String query,
    BusinessFilters? filters,
    int page = 1,
    int perPage = 10,
  }) async {
    try {
      final queryParams = <String, dynamic>{
        'query': query,
        'page': page,
        'per_page': perPage,
        if (filters?.industry != null) 'industry': filters!.industry,
        if (filters?.minValuation != null) 'min_valuation': filters!.minValuation,
        if (filters?.maxValuation != null) 'max_valuation': filters!.maxValuation,
        if (filters?.minFunding != null) 'min_funding': filters!.minFunding,
        if (filters?.maxFunding != null) 'max_funding': filters!.maxFunding,
        if (filters?.location != null) 'location': filters!.location,
      };

      final response = await _apiService.get(
        '/search/businesses',
        queryParameters: queryParams,
      );

      if (response.data['success'] == true) {
        final businesses = (response.data['data'] as List)
            .map((json) => Business.fromJson(json))
            .toList();
        
        return MarketplaceApiResponse<List<Business>>(
          success: true,
          data: businesses,
          currentPage: response.data['current_page'],
          lastPage: response.data['last_page'],
          perPage: response.data['per_page'],
          total: response.data['total'],
        );
      }

      return MarketplaceApiResponse<List<Business>>(
        success: false,
        message: response.data['message'] ?? 'Failed to search businesses',
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<List<Business>>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
      );
    }
  }

  // Marketplace Statistics
  Future<MarketplaceApiResponse<MarketplaceStats>> getMarketplaceStats() async {
    try {
      final response = await _apiService.get('/marketplace/stats');

      if (response.data['success'] == true) {
        final stats = MarketplaceStats.fromJson(response.data['data']);
        return MarketplaceApiResponse<MarketplaceStats>(
          success: true,
          data: stats,
        );
      }

      return MarketplaceApiResponse<MarketplaceStats>(
        success: false,
        message: response.data['message'] ?? 'Failed to fetch marketplace stats',
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<MarketplaceStats>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
      );
    }
  }

  // Transaction History
  Future<MarketplaceApiResponse<List<Transaction>>> getTransactionHistory({
    int page = 1,
    int perPage = 10,
  }) async {
    try {
      final queryParams = <String, dynamic>{
        'page': page,
        'per_page': perPage,
      };

      final response = await _apiService.get(
        '/payments/transaction-history',
        queryParameters: queryParams,
      );

      if (response.data['success'] == true) {
        final transactions = (response.data['data'] as List)
            .map((json) => Transaction.fromJson(json))
            .toList();
        
        return MarketplaceApiResponse<List<Transaction>>(
          success: true,
          data: transactions,
          currentPage: response.data['current_page'],
          lastPage: response.data['last_page'],
          perPage: response.data['per_page'],
          total: response.data['total'],
        );
      }

      return MarketplaceApiResponse<List<Transaction>>(
        success: false,
        message: response.data['message'] ?? 'Failed to fetch transactions',
      );
    } on DioException catch (e) {
      return MarketplaceApiResponse<List<Transaction>>(
        success: false,
        message: e.response?.data['message'] ?? 'Network error occurred',
      );
    }
  }
}
