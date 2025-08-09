import 'dart:convert';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../../../core/services/api_service.dart';
import '../models/auth_models.dart';

/// Authentication service handling all auth-related API calls
class AuthService {
  static const String _userKey = 'user_data';
  static const String _verificationProgressKey = 'verification_progress';
  
  final ApiService _apiService;
  final FlutterSecureStorage _secureStorage;
  
  AuthService({
    ApiService? apiService,
    FlutterSecureStorage? secureStorage,
  })  : _apiService = apiService ?? ApiService(),
        _secureStorage = secureStorage ?? const FlutterSecureStorage();
  
  /// Register a new user
  Future<ApiResponse<Map<String, dynamic>>> register(RegistrationData data) async {
    try {
      final response = await _apiService.post('/register', data: data.toJson());
      return ApiResponse.fromJson(
        response.data,
        (json) => json as Map<String, dynamic>,
      );
    } on ApiException catch (e) {
      return ApiResponse(
        success: false,
        message: e.message,
        errors: e.validationErrors,
      );
    }
  }
  
  /// Login user with optional 2FA
  Future<ApiResponse<AuthResponse>> login(LoginData data) async {
    try {
      final response = await _apiService.post('/login', data: data.toJson());
      final apiResponse = ApiResponse.fromJson(
        response.data,
        (json) => AuthResponse.fromJson(json as Map<String, dynamic>),
      );
      
      // Store token and user data on successful login
      if (apiResponse.success && apiResponse.data != null) {
        final authResponse = apiResponse.data!;
        await _apiService.setToken(authResponse.token);
        await setUser(authResponse.user);
      }
      
      return apiResponse;
    } on ApiException catch (e) {
      return ApiResponse(
        success: false,
        message: e.message,
        errors: e.validationErrors,
      );
    }
  }
  
  /// Logout current user
  Future<ApiResponse<void>> logout() async {
    try {
      await _apiService.post('/logout');
      await _clearStoredData();
      return const ApiResponse(success: true, message: 'Logged out successfully');
    } on ApiException catch (e) {
      // Clear local data even if API call fails
      await _clearStoredData();
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Get current user profile with verification status
  Future<ApiResponse<Map<String, dynamic>>> getCurrentUser() async {
    try {
      final response = await _apiService.get('/me');
      return ApiResponse.fromJson(
        response.data,
        (json) => json as Map<String, dynamic>,
      );
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Update user profile
  Future<ApiResponse<Map<String, dynamic>>> updateProfile(Map<String, dynamic> data) async {
    try {
      final response = await _apiService.put('/profile', data: data);
      final apiResponse = ApiResponse.fromJson(
        response.data,
        (json) => json as Map<String, dynamic>,
      );
      
      // Update stored user data
      if (apiResponse.success && apiResponse.data?['user'] != null) {
        final user = User.fromJson(apiResponse.data!['user']);
        await setUser(user);
      }
      
      return apiResponse;
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Change password
  Future<ApiResponse<void>> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    try {
      final response = await _apiService.post('/change-password', data: {
        'current_password': currentPassword,
        'new_password': newPassword,
        'new_password_confirmation': confirmPassword,
      });
      return ApiResponse.fromJson(response.data, (json) => {});
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Store user data securely
  Future<void> setUser(User user) async {
    await _secureStorage.write(key: _userKey, value: jsonEncode(user.toJson()));
  }
  
  /// Get stored user data
  Future<User?> getUser() async {
    final userData = await _secureStorage.read(key: _userKey);
    if (userData != null) {
      try {
        return User.fromJson(jsonDecode(userData));
      } catch (e) {
        // Clear invalid user data
        await _secureStorage.delete(key: _userKey);
      }
    }
    return null;
  }
  
  /// Clear stored user data
  Future<void> clearUser() async {
    await _secureStorage.delete(key: _userKey);
  }
  
  /// Check if user is authenticated
  Future<bool> isAuthenticated() async {
    return await _apiService.isAuthenticated();
  }

  /// Expose auth token (needed by provider without accessing private field)
  Future<String?> getToken() async {
    return await _apiService.getToken();
  }
  
  /// Store verification step progress
  Future<void> setVerificationProgress(VerificationStep step, Map<String, dynamic> data) async {
    final progress = {
      'step': step.name,
      'data': data,
      'timestamp': DateTime.now().millisecondsSinceEpoch,
    };
    await _secureStorage.write(
      key: _verificationProgressKey,
      value: jsonEncode(progress),
    );
  }
  
  /// Get verification step progress
  Future<Map<String, dynamic>?> getVerificationProgress() async {
    final progressData = await _secureStorage.read(key: _verificationProgressKey);
    if (progressData != null) {
      try {
        return jsonDecode(progressData);
      } catch (e) {
        await _secureStorage.delete(key: _verificationProgressKey);
      }
    }
    return null;
  }
  
  /// Clear verification progress
  Future<void> clearVerificationProgress() async {
    await _secureStorage.delete(key: _verificationProgressKey);
  }
  
  /// Clear all stored authentication data
  Future<void> _clearStoredData() async {
    await _apiService.clearToken();
    await clearUser();
    await clearVerificationProgress();
  }
}

/// Email verification service
class EmailVerificationService {
  final ApiService _apiService;
  
  EmailVerificationService({ApiService? apiService})
      : _apiService = apiService ?? ApiService();
  
  /// Verify email with token
  Future<ApiResponse<Map<String, dynamic>>> verifyEmail(String token, String email) async {
    try {
      final response = await _apiService.post('/verify-email', data: {
        'token': token,
        'email': email,
      });
      return ApiResponse.fromJson(
        response.data,
        (json) => json as Map<String, dynamic>,
      );
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Check email verification status
  Future<ApiResponse<Map<String, dynamic>>> checkEmailVerificationStatus(String email) async {
    try {
      final response = await _apiService.post('/check-email-verification-status', data: {
        'email': email,
      });
      return ApiResponse.fromJson(
        response.data,
        (json) => json as Map<String, dynamic>,
      );
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Resend email verification
  Future<ApiResponse<void>> resendVerification(String email) async {
    try {
      final response = await _apiService.post('/resend-email-verification', data: {
        'email': email,
      });
      return ApiResponse.fromJson(response.data, (json) => {});
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Send email verification OTP
  Future<ApiResponse<void>> sendEmailVerificationOTP(String email) async {
    try {
      final response = await _apiService.post('/send-email-verification-otp', data: {
        'email': email,
      });
      return ApiResponse.fromJson(response.data, (json) => {});
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Verify email with OTP
  Future<ApiResponse<Map<String, dynamic>>> verifyEmailOTP(String email, String otp) async {
    try {
      final response = await _apiService.post('/verify-email-otp', data: {
        'email': email,
        'otp': otp,
      });
      return ApiResponse.fromJson(
        response.data,
        (json) => json as Map<String, dynamic>,
      );
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
}

/// Phone verification service
class PhoneVerificationService {
  final ApiService _apiService;
  
  PhoneVerificationService({ApiService? apiService})
      : _apiService = apiService ?? ApiService();
  
  /// Send phone verification code
  Future<ApiResponse<Map<String, dynamic>>> sendCode(String email, String phone) async {
    try {
      final response = await _apiService.post('/send-phone-verification', data: {
        'email': email,
        'phone': phone,
      });
      return ApiResponse.fromJson(
        response.data,
        (json) => json as Map<String, dynamic>,
      );
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Verify phone with code
  Future<ApiResponse<Map<String, dynamic>>> verifyCode(String email, String code) async {
    try {
      final response = await _apiService.post('/verify-phone', data: {
        'email': email,
        'code': code,
      });
      return ApiResponse.fromJson(
        response.data,
        (json) => json as Map<String, dynamic>,
      );
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
}

/// Two-factor authentication service
class TwoFactorService {
  final ApiService _apiService;
  
  TwoFactorService({ApiService? apiService})
      : _apiService = apiService ?? ApiService();
  
  /// Setup Google Authenticator 2FA
  Future<ApiResponse<TwoFactorSetupData>> setup() async {
    try {
      final response = await _apiService.post('/setup-2fa');
      return ApiResponse.fromJson(
        response.data,
        (json) => TwoFactorSetupData.fromJson(json as Map<String, dynamic>),
      );
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Setup Email 2FA
  Future<ApiResponse<Map<String, dynamic>>> setupEmail() async {
    try {
      final response = await _apiService.post('/setup-email-2fa');
      return ApiResponse.fromJson(
        response.data,
        (json) => json as Map<String, dynamic>,
      );
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Skip 2FA setup
  Future<ApiResponse<Map<String, dynamic>>> skip() async {
    try {
      final response = await _apiService.post('/skip-2fa');
      return ApiResponse.fromJson(
        response.data,
        (json) => json as Map<String, dynamic>,
      );
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Confirm Google Authenticator 2FA setup
  Future<ApiResponse<Map<String, dynamic>>> confirm(String code) async {
    try {
      final response = await _apiService.post('/confirm-2fa', data: {'code': code});
      return ApiResponse.fromJson(
        response.data,
        (json) => json as Map<String, dynamic>,
      );
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Confirm Email 2FA setup
  Future<ApiResponse<Map<String, dynamic>>> confirmEmail(String code) async {
    try {
      final response = await _apiService.post('/confirm-email-2fa', data: {'code': code});
      return ApiResponse.fromJson(
        response.data,
        (json) => json as Map<String, dynamic>,
      );
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Verify 2FA code for authenticated users (post-login verification)
  Future<ApiResponse<Map<String, dynamic>>> verify2FAForSession(String code) async {
    try {
      final response = await _apiService.post('/verify-2fa-session', data: {'code': code});
      return ApiResponse.fromJson(
        response.data,
        (json) => json as Map<String, dynamic>,
      );
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Check if 2FA verification is required for the current session
  Future<ApiResponse<Map<String, dynamic>>> check2FAStatus() async {
    try {
      final response = await _apiService.get('/check-2fa-status');
      return ApiResponse.fromJson(
        response.data,
        (json) => json as Map<String, dynamic>,
      );
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
  
  /// Disable 2FA
  Future<ApiResponse<void>> disable() async {
    try {
      final response = await _apiService.post('/disable-2fa');
      return ApiResponse.fromJson(response.data, (json) => {});
    } on ApiException catch (e) {
      return ApiResponse(success: false, message: e.message);
    }
  }
}
