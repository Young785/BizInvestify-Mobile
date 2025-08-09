// import 'dart:convert';
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

/// Core API service for making HTTP requests
class ApiService {
  static const String _baseUrl = 'http://localhost:8000/api';
  static const String _tokenKey = 'auth_token';
  
  late final Dio _dio;
  late final FlutterSecureStorage _secureStorage;
  
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  
  ApiService._internal() {
    _secureStorage = const FlutterSecureStorage();
    _dio = Dio(BaseOptions(
      baseUrl: _baseUrl,
      connectTimeout: const Duration(seconds: 30),
      receiveTimeout: const Duration(seconds: 30),
      sendTimeout: const Duration(seconds: 30),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    ));
    
    _setupInterceptors();
  }
  
  void _setupInterceptors() {
    // Request interceptor to add auth token
    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token = await getToken();
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        
        if (kDebugMode) {
          print('🚀 ${options.method} ${options.path}');
          if (options.data != null) {
            print('📤 Request data: ${options.data}');
          }
        }
        
        handler.next(options);
      },
      onResponse: (response, handler) {
        if (kDebugMode) {
          print('✅ ${response.statusCode} ${response.requestOptions.path}');
          print('📥 Response data: ${response.data}');
        }
        handler.next(response);
      },
      onError: (error, handler) {
        if (kDebugMode) {
          print('❌ Error: ${error.message}');
          if (error.response != null) {
            print('📥 Error response: ${error.response?.data}');
          }
        }
        
        // Handle 401 unauthorized - clear token and redirect to login
        if (error.response?.statusCode == 401) {
          clearToken();
          // TODO: Navigate to login screen
        }
        
        // Handle 403 2FA required
        if (error.response?.statusCode == 403) {
          final data = error.response?.data;
          if (data is Map<String, dynamic> && 
              data['data']?['requires_2fa_verification'] == true) {
            // TODO: Navigate to 2FA verification screen
          }
        }
        
        handler.next(error);
      },
    ));
  }
  
  /// Store authentication token securely
  Future<void> setToken(String token) async {
    await _secureStorage.write(key: _tokenKey, value: token);
  }
  
  /// Retrieve stored authentication token
  Future<String?> getToken() async {
    return await _secureStorage.read(key: _tokenKey);
  }
  
  /// Clear stored authentication token
  Future<void> clearToken() async {
    await _secureStorage.delete(key: _tokenKey);
  }
  
  /// Check if user is authenticated
  Future<bool> isAuthenticated() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }
  
  /// Generic GET request
  Future<Response<T>> get<T>(
    String path, {
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await _dio.get<T>(
        path,
        queryParameters: queryParameters,
        options: options,
      );
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }
  
  /// Generic POST request
  Future<Response<T>> post<T>(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await _dio.post<T>(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
      );
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }
  
  /// Generic PUT request
  Future<Response<T>> put<T>(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await _dio.put<T>(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
      );
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }
  
  /// Generic DELETE request
  Future<Response<T>> delete<T>(
    String path, {
    dynamic data,
    Map<String, dynamic>? queryParameters,
    Options? options,
  }) async {
    try {
      return await _dio.delete<T>(
        path,
        data: data,
        queryParameters: queryParameters,
        options: options,
      );
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }
  
  /// Upload files with multipart/form-data
  Future<Response<T>> uploadFiles<T>(
    String path,
    Map<String, dynamic> fields,
    List<MapEntry<String, File>> files,
  ) async {
    try {
      final formData = FormData();
      
      // Add regular fields
      for (final entry in fields.entries) {
        formData.fields.add(MapEntry(entry.key, entry.value.toString()));
      }
      
      // Add files
      for (final file in files) {
        formData.files.add(MapEntry(
          file.key,
          await MultipartFile.fromFile(
            file.value.path,
            filename: file.value.path.split('/').last,
          ),
        ));
      }
      
      return await _dio.post<T>(
        path,
        data: formData,
        options: Options(
          contentType: 'multipart/form-data',
        ),
      );
    } on DioException catch (e) {
      throw _handleError(e);
    }
  }
  
  /// Handle Dio errors and convert to app-specific exceptions
  Exception _handleError(DioException error) {
    switch (error.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
        return ApiException(
          'Connection timeout. Please check your internet connection.',
          statusCode: 408,
        );
      
      case DioExceptionType.badResponse:
        final statusCode = error.response?.statusCode ?? 0;
        final data = error.response?.data;
        
        String message = 'An error occurred';
        if (data is Map<String, dynamic>) {
          message = data['message'] ?? message;
        }
        
        return ApiException(message, statusCode: statusCode, data: data);
      
      case DioExceptionType.cancel:
        return ApiException('Request was cancelled');
      
      case DioExceptionType.unknown:
        if (error.error is SocketException) {
          return ApiException(
            'No internet connection. Please check your network.',
          );
        }
        return ApiException('An unexpected error occurred');
      
      default:
        return ApiException('An unexpected error occurred');
    }
  }
}

/// Custom API exception
class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final dynamic data;
  
  const ApiException(
    this.message, {
    this.statusCode,
    this.data,
  });
  
  @override
  String toString() => 'ApiException: $message';
  
  /// Check if this is a validation error (422)
  bool get isValidationError => statusCode == 422;
  
  /// Check if this is an unauthorized error (401)
  bool get isUnauthorized => statusCode == 401;
  
  /// Check if this is a forbidden error (403)
  bool get isForbidden => statusCode == 403;
  
  /// Check if this is a not found error (404)
  bool get isNotFound => statusCode == 404;
  
  /// Check if this is a server error (5xx)
  bool get isServerError => statusCode != null && statusCode! >= 500;
  
  /// Get validation errors if this is a validation error
  Map<String, List<String>>? get validationErrors {
    if (!isValidationError || data is! Map<String, dynamic>) {
      return null;
    }
    
    final errors = data['errors'] as Map<String, dynamic>?;
    if (errors == null) return null;
    
    final result = <String, List<String>>{};
    for (final entry in errors.entries) {
      if (entry.value is List) {
        result[entry.key] = (entry.value as List).cast<String>();
      } else {
        result[entry.key] = [entry.value.toString()];
      }
    }
    
    return result;
  }
}

/// Riverpod provider for ApiService
final apiServiceProvider = Provider<ApiService>((ref) {
  return ApiService();
});
