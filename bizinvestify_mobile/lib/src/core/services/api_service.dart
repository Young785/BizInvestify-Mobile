import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../constants/app_colors.dart';

class ApiService {
  static const String _baseUrl = 'https://api.bizinvestify.com/api';
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

  // Auth Methods
  Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await _dio.post('/auth/login', data: {
        'email': email,
        'password': password,
      });

      if (response.data['token'] != null) {
        await _storage.write(key: _tokenKey, value: response.data['token']);
      }

      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> register(Map<String, dynamic> userData) async {
    try {
      final response = await _dio.post('/auth/register', data: userData);
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> logout() async {
    try {
      await _dio.post('/auth/logout');
    } catch (e) {
      // Continue with logout even if API call fails
    } finally {
      await _storage.delete(key: _tokenKey);
    }
  }

  Future<Map<String, dynamic>> getCurrentUser() async {
    try {
      final response = await _dio.get('/auth/me');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // Dashboard Methods
  Future<Map<String, dynamic>> getDashboardStats() async {
    try {
      final response = await _dio.get('/dashboard/stats');
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // Products Methods
  Future<List<Map<String, dynamic>>> getProducts({
    int page = 1,
    int limit = 10,
    String? search,
    String? category,
  }) async {
    try {
      final response = await _dio.get('/products', queryParameters: {
        'page': page,
        'limit': limit,
        if (search != null) 'search': search,
        if (category != null) 'category': category,
      });
      return List<Map<String, dynamic>>.from(response.data['data']);
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

  Future<Map<String, dynamic>> updateProduct(int id, Map<String, dynamic> productData) async {
    try {
      final response = await _dio.put('/products/$id', data: productData);
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<void> deleteProduct(int id) async {
    try {
      await _dio.delete('/products/$id');
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // Businesses Methods
  Future<List<Map<String, dynamic>>> getBusinesses({
    int page = 1,
    int limit = 10,
    String? search,
    String? industry,
  }) async {
    try {
      final response = await _dio.get('/businesses', queryParameters: {
        'page': page,
        'limit': limit,
        if (search != null) 'search': search,
        if (industry != null) 'industry': industry,
      });
      return List<Map<String, dynamic>>.from(response.data['data']);
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

  // Messages Methods
  Future<List<Map<String, dynamic>>> getConversations() async {
    try {
      final response = await _dio.get('/messages/conversations');
      return List<Map<String, dynamic>>.from(response.data['data']);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<List<Map<String, dynamic>>> getMessages(int conversationId) async {
    try {
      final response = await _dio.get('/messages/conversations/$conversationId');
      return List<Map<String, dynamic>>.from(response.data['data']);
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> sendMessage(int conversationId, String message) async {
    try {
      final response = await _dio.post('/messages/conversations/$conversationId', data: {
        'message': message,
      });
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // Profile Methods
  Future<Map<String, dynamic>> updateProfile(Map<String, dynamic> profileData) async {
    try {
      final response = await _dio.put('/profile', data: profileData);
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  Future<Map<String, dynamic>> uploadKycDocuments(Map<String, dynamic> documents) async {
    try {
      final response = await _dio.post('/profile/kyc', data: documents);
      return response.data;
    } on DioException catch (e) {
      throw _handleDioError(e);
    }
  }

  // Error Handling
  String _handleDioError(DioException error) {
    switch (error.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
        return 'Connection timeout. Please check your internet connection.';
      
      case DioExceptionType.badResponse:
        final statusCode = error.response?.statusCode;
        final message = error.response?.data['message'] ?? 'An error occurred';
        
        switch (statusCode) {
          case 400:
            return 'Bad request: $message';
          case 401:
            return 'Unauthorized. Please login again.';
          case 403:
            return 'Access denied. You don\'t have permission to perform this action.';
          case 404:
            return 'Resource not found.';
          case 422:
            return 'Validation error: $message';
          case 500:
            return 'Server error. Please try again later.';
          default:
            return message;
        }
      
      case DioExceptionType.cancel:
        return 'Request was cancelled.';
      
      case DioExceptionType.connectionError:
        return 'No internet connection. Please check your network.';
      
      case DioExceptionType.badCertificate:
        return 'SSL certificate error.';
      
      case DioExceptionType.unknown:
      default:
        return 'An unexpected error occurred. Please try again.';
    }
  }
}

// Singleton instance
final apiService = ApiService();
