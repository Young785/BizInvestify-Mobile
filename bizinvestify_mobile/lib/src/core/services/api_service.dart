import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiService {
  static const String _baseUrl = 'http://127.0.0.1:8000/api';
  static const String _tokenKey = 'auth_token';

  late final Dio _dio;
  final FlutterSecureStorage _storage = const FlutterSecureStorage();

  ApiService() {
    _dio = Dio(BaseOptions(
      baseUrl: _baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ));

    _setupInterceptors();
  }

  void _setupInterceptors() {
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          // Add auth token to requests
          final token = await _storage.read(key: _tokenKey);
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        },
        onResponse: (response, handler) {
          handler.next(response);
        },
        onError: (error, handler) {
          // Handle common errors
          if (error.response?.statusCode == 401) {
            // Token expired or invalid
            _handleUnauthorized();
          }
          handler.next(error);
        },
      ),
    );
  }

  void _handleUnauthorized() async {
    // Clear stored token and redirect to login
    await _storage.delete(key: _tokenKey);
    // TODO: Navigate to login screen
  }

  // Public method for token storage
  Future<void> storeToken(String token) async {
    await _storage.write(key: _tokenKey, value: token);
  }

  // ========================================
  // AUTHENTICATION ENDPOINTS
  // ========================================

  // Public auth routes
  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await _dio.post('/login', data: {
        'email': email,
        'password': password,
      });
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> register(Map<String, dynamic> userData) async {
    try {
      final response = await _dio.post('/register', data: userData);
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> forgotPassword(String email) async {
    try {
      await _dio.post('/forgot-password', data: {'email': email});
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> resetPassword(String token, String password, String passwordConfirmation) async {
    try {
      await _dio.post('/reset-password', data: {
        'token': token,
        'password': password,
        'password_confirmation': passwordConfirmation,
      });
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // Email verification
  Future<void> verifyEmail(String token) async {
    try {
      await _dio.post('/verify-email', data: {'token': token});
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> checkEmailVerificationStatus() async {
    try {
      final response = await _dio.post('/check-email-verification-status');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> resendEmailVerification() async {
    try {
      await _dio.post('/resend-email-verification');
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // Phone verification
  Future<void> sendPhoneVerification(String phone) async {
    try {
      await _dio.post('/send-phone-verification', data: {'phone': phone});
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> verifyPhone(String phone, String code) async {
    try {
      await _dio.post('/verify-phone', data: {
        'phone': phone,
        'code': code,
      });
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // Protected auth routes
  Future<void> logout() async {
    try {
      await _dio.post('/logout');
      await _storage.delete(key: _tokenKey);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> getCurrentUser() async {
    try {
      final response = await _dio.get('/me');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> updateProfile(Map<String, dynamic> profileData) async {
    try {
      final response = await _dio.put('/profile', data: profileData);
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> uploadProfileImage(String imagePath) async {
    try {
      final formData = FormData.fromMap({
        'image': await MultipartFile.fromFile(imagePath),
      });
      final response = await _dio.post('/profile/upload-image', data: formData);
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> changePassword(String currentPassword, String newPassword, String newPasswordConfirmation) async {
    try {
      await _dio.post('/change-password', data: {
        'current_password': currentPassword,
        'password': newPassword,
        'password_confirmation': newPasswordConfirmation,
      });
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> getStats() async {
    try {
      final response = await _dio.get('/stats');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // KYC routes
  Future<Map<String, dynamic>> uploadKyc(Map<String, dynamic> kycData) async {
    try {
      final response = await _dio.post('/kyc-upload', data: kycData);
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> getMyKyc() async {
    try {
      final response = await _dio.get('/kyc/my-application');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> submitKyc() async {
    try {
      await _dio.post('/kyc/submit');
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // 2FA routes
  Future<Map<String, dynamic>> setup2FA() async {
    try {
      final response = await _dio.post('/setup-2fa');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> confirm2FA(String code) async {
    try {
      await _dio.post('/confirm-2fa', data: {'code': code});
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> verify2FA(String code) async {
    try {
      await _dio.post('/verify-2fa', data: {'code': code});
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> disable2FA(String code) async {
    try {
      await _dio.post('/disable-2fa', data: {'code': code});
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // ========================================
  // PRODUCTS ENDPOINTS
  // ========================================

  Future<List<Map<String, dynamic>>> getProducts({String? search, String? category}) async {
    try {
      final queryParameters = <String, dynamic>{};
      if (search != null) queryParameters['search'] = search;
      if (category != null) queryParameters['category'] = category;

      final response = await _dio.get('/products', queryParameters: queryParameters);
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getUserProducts() async {
    try {
      final response = await _dio.get('/products/user');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getTrendingProducts() async {
    try {
      final response = await _dio.get('/products/trending');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> getProduct(int productId) async {
    try {
      final response = await _dio.get('/products/$productId');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getRelatedProducts(int productId) async {
    try {
      final response = await _dio.get('/products/$productId/related');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> createProduct(Map<String, dynamic> productData) async {
    try {
      final response = await _dio.post('/products', data: productData);
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> updateProduct(int productId, Map<String, dynamic> productData) async {
    try {
      final response = await _dio.put('/products/$productId', data: productData);
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> deleteProduct(int productId) async {
    try {
      await _dio.delete('/products/$productId');
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // ========================================
  // BUSINESSES ENDPOINTS
  // ========================================

  Future<List<Map<String, dynamic>>> getBusinesses({String? search, String? industry}) async {
    try {
      final queryParameters = <String, dynamic>{};
      if (search != null) queryParameters['search'] = search;
      if (industry != null) queryParameters['industry'] = industry;

      final response = await _dio.get('/businesses', queryParameters: queryParameters);
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getUserBusinesses() async {
    try {
      final response = await _dio.get('/businesses/user');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getFeaturedBusinesses() async {
    try {
      final response = await _dio.get('/businesses/featured');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getTrendingBusinesses() async {
    try {
      final response = await _dio.get('/businesses/trending');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> getBusiness(int businessId) async {
    try {
      final response = await _dio.get('/businesses/$businessId');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getRelatedBusinesses(int businessId) async {
    try {
      final response = await _dio.get('/businesses/$businessId/related');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> createBusiness(Map<String, dynamic> businessData) async {
    try {
      final response = await _dio.post('/businesses', data: businessData);
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> updateBusiness(int businessId, Map<String, dynamic> businessData) async {
    try {
      final response = await _dio.put('/businesses/$businessId', data: businessData);
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> deleteBusiness(int businessId) async {
    try {
      await _dio.delete('/businesses/$businessId');
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // ========================================
  // MESSAGING ENDPOINTS
  // ========================================

  Future<List<Map<String, dynamic>>> getConversations() async {
    try {
      final response = await _dio.get('/conversations');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> createConversation(Map<String, dynamic> conversationData) async {
    try {
      final response = await _dio.post('/conversations', data: conversationData);
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> searchConversations(String query) async {
    try {
      final response = await _dio.get('/conversations/search', queryParameters: {'q': query});
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getMessages(int conversationId) async {
    try {
      final response = await _dio.get('/conversations/$conversationId/messages');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> sendMessage(Map<String, dynamic> messageData) async {
    try {
      final response = await _dio.post('/messages', data: messageData);
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> markConversationAsRead(int conversationId) async {
    try {
      await _dio.post('/conversations/$conversationId/mark-read');
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<int> getUnreadCount() async {
    try {
      final response = await _dio.get('/messages/unread-count');
      return response.data['count'] ?? 0;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // ========================================
  // DASHBOARD & ANALYTICS ENDPOINTS
  // ========================================

  Future<Map<String, dynamic>> getDashboardStats() async {
    try {
      final response = await _dio.get('/stats');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> getSellerAnalytics() async {
    try {
      final response = await _dio.get('/analytics/seller');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> getBuyerAnalytics() async {
    try {
      final response = await _dio.get('/analytics/buyer');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> getOverviewAnalytics() async {
    try {
      final response = await _dio.get('/analytics/overview');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // ========================================
  // MARKETPLACE ENDPOINTS
  // ========================================

  Future<Map<String, dynamic>> getMarketplaceStats() async {
    try {
      final response = await _dio.get('/marketplace/stats');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getMarketplaceRecommendations() async {
    try {
      final response = await _dio.get('/marketplace/recommendations');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getFeaturedListings() async {
    try {
      final response = await _dio.get('/marketplace/featured');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getTrendingItems() async {
    try {
      final response = await _dio.get('/marketplace/trending');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getCategories() async {
    try {
      final response = await _dio.get('/marketplace/categories');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // ========================================
  // SEARCH ENDPOINTS
  // ========================================

  Future<List<Map<String, dynamic>>> searchProducts(String query) async {
    try {
      final response = await _dio.get('/search/products', queryParameters: {'q': query});
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> searchBusinesses(String query) async {
    try {
      final response = await _dio.get('/search/businesses', queryParameters: {'q': query});
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<String>> getSearchSuggestions(String query) async {
    try {
      final response = await _dio.get('/search/suggestions', queryParameters: {'q': query});
      return List<String>.from(response.data['suggestions'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // ========================================
  // NOTIFICATIONS ENDPOINTS
  // ========================================

  Future<List<Map<String, dynamic>>> getNotifications() async {
    try {
      final response = await _dio.get('/notifications');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<int> getNotificationUnreadCount() async {
    try {
      final response = await _dio.get('/notifications/unread-count');
      return response.data['count'] ?? 0;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> markNotificationAsRead(int notificationId) async {
    try {
      await _dio.put('/notifications/$notificationId/read');
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> markAllNotificationsAsRead() async {
    try {
      await _dio.put('/notifications/mark-all-read');
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // ========================================
  // PUBLIC MARKETPLACE ENDPOINTS (No Auth Required)
  // ========================================

  Future<List<Map<String, dynamic>>> getPublicProducts({String? search, String? category}) async {
    try {
      final queryParameters = <String, dynamic>{};
      if (search != null) queryParameters['search'] = search;
      if (category != null) queryParameters['category'] = category;

      final response = await _dio.get('/public/products', queryParameters: queryParameters);
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getPublicBusinesses({String? search, String? industry}) async {
    try {
      final queryParameters = <String, dynamic>{};
      if (search != null) queryParameters['search'] = search;
      if (industry != null) queryParameters['industry'] = industry;

      final response = await _dio.get('/public/businesses', queryParameters: queryParameters);
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> getPublicProduct(int productId) async {
    try {
      final response = await _dio.get('/public/products/$productId');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> getPublicBusiness(int businessId) async {
    try {
      final response = await _dio.get('/public/businesses/$businessId');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getPublicCategories() async {
    try {
      final response = await _dio.get('/public/categories');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getPublicFeatured() async {
    try {
      final response = await _dio.get('/public/featured');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getPublicTrending() async {
    try {
      final response = await _dio.get('/public/trending');
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> publicSearch(String query) async {
    try {
      final response = await _dio.get('/public/search', queryParameters: {'q': query});
      return List<Map<String, dynamic>>.from(response.data['data'] ?? []);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // ========================================
  // ERROR HANDLING
  // ========================================

  String _handleDioError(DioException error) {
    if (error.response != null) {
      // Server responded with error status
      final statusCode = error.response!.statusCode;
      final data = error.response!.data;

      if (data is Map<String, dynamic>) {
        // Check for validation errors
        if (data.containsKey('errors')) {
          final errors = data['errors'] as Map<String, dynamic>;
          final firstError = errors.values.first;
          if (firstError is List && firstError.isNotEmpty) {
            return firstError.first.toString();
          }
        }

        // Check for message
        if (data.containsKey('message')) {
          return data['message'].toString();
        }

        // Check for error
        if (data.containsKey('error')) {
          return data['error'].toString();
        }
      }

      // Handle specific status codes
      switch (statusCode) {
        case 401:
          return 'Unauthorized. Please login again.';
        case 403:
          return 'Access denied. You don\'t have permission to perform this action.';
        case 404:
          return 'Resource not found.';
        case 422:
          return 'Validation failed. Please check your input.';
        case 429:
          return 'Too many requests. Please try again later.';
        case 500:
          return 'Server error. Please try again later.';
        default:
          return 'An error occurred. Please try again.';
      }
    } else if (error.type == DioExceptionType.connectionTimeout) {
      return 'Connection timeout. Please check your internet connection.';
    } else if (error.type == DioExceptionType.receiveTimeout) {
      return 'Request timeout. Please try again.';
    } else if (error.type == DioExceptionType.connectionError) {
      return 'Connection error. Please check your internet connection.';
    } else {
      return 'An unexpected error occurred. Please try again.';
    }
  }
}

// Singleton instance
final apiService = ApiService();
