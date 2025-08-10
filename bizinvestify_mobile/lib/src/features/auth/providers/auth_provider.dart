import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/api_service.dart';

// User Model
class User {
  final int id;
  final String firstName;
  final String lastName;
  final String email;
  final String? phone;
  final String role;
  final bool emailVerified;
  final bool phoneVerified;
  final String kycStatus;
  final bool isActive;
  final String? profilePicture;
  final DateTime createdAt;

  User({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.email,
    this.phone,
    required this.role,
    required this.emailVerified,
    required this.phoneVerified,
    required this.kycStatus,
    required this.isActive,
    this.profilePicture,
    required this.createdAt,
  });

  String get fullName => '$firstName $lastName';
  String get displayName => firstName.isNotEmpty ? firstName : email;

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      firstName: json['first_name'] ?? '',
      lastName: json['last_name'] ?? '',
      email: json['email'],
      phone: json['phone'],
      role: json['role'] ?? 'buyer',
      emailVerified: json['email_verified_at'] != null,
      phoneVerified: json['phone_verified_at'] != null,
      kycStatus: json['kyc_status'] ?? 'pending',
      isActive: json['is_active'] ?? true,
      profilePicture: json['profile_picture'],
      createdAt: DateTime.parse(json['created_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'first_name': firstName,
      'last_name': lastName,
      'email': email,
      'phone': phone,
      'role': role,
      'email_verified_at': emailVerified ? createdAt.toIso8601String() : null,
      'phone_verified_at': phoneVerified ? createdAt.toIso8601String() : null,
      'kyc_status': kycStatus,
      'is_active': isActive,
      'profile_picture': profilePicture,
      'created_at': createdAt.toIso8601String(),
    };
  }

  User copyWith({
    int? id,
    String? firstName,
    String? lastName,
    String? email,
    String? phone,
    String? role,
    bool? emailVerified,
    bool? phoneVerified,
    String? kycStatus,
    bool? isActive,
    String? profilePicture,
    DateTime? createdAt,
  }) {
    return User(
      id: id ?? this.id,
      firstName: firstName ?? this.firstName,
      lastName: lastName ?? this.lastName,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      role: role ?? this.role,
      emailVerified: emailVerified ?? this.emailVerified,
      phoneVerified: phoneVerified ?? this.phoneVerified,
      kycStatus: kycStatus ?? this.kycStatus,
      isActive: isActive ?? this.isActive,
      profilePicture: profilePicture ?? this.profilePicture,
      createdAt: createdAt ?? this.createdAt,
    );
  }
}

// Auth State
class AuthState {
  final User? user;
  final bool isLoading;
  final String? error;
  final bool isAuthenticated;
  final bool isInitialized;

  const AuthState({
    this.user,
    this.isLoading = false,
    this.error,
    this.isAuthenticated = false,
    this.isInitialized = false,
  });

  AuthState copyWith({
    User? user,
    bool? isLoading,
    String? error,
    bool? isAuthenticated,
    bool? isInitialized,
  }) {
    return AuthState(
      user: user ?? this.user,
      isLoading: isLoading ?? this.isLoading,
      error: error ?? this.error,
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
      isInitialized: isInitialized ?? this.isInitialized,
    );
  }
}

// Auth Notifier
class AuthNotifier extends StateNotifier<AuthState> {
  final ApiService _apiService;

  AuthNotifier(this._apiService) : super(const AuthState());

  // Initialize auth state
  Future<void> initialize() async {
    state = state.copyWith(isLoading: true);
    
    try {
      final userData = await _apiService.getCurrentUser();
      final user = User.fromJson(userData);
      
      state = state.copyWith(
        user: user,
        isAuthenticated: true,
        isLoading: false,
        isInitialized: true,
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        isInitialized: true,
      );
    }
  }

  // Login
  Future<bool> login(String email, String password) async {
    state = state.copyWith(isLoading: true, error: null);
    
    try {
      final response = await _apiService.login(email, password);
      
      // Handle token storage - check different possible token field names
      if (response['token'] != null) {
        await _apiService.storeToken(response['token']);
      } else if (response['access_token'] != null) {
        await _apiService.storeToken(response['access_token']);
      } else if (response['data'] != null && response['data']['token'] != null) {
        await _apiService.storeToken(response['data']['token']);
      }
      
      // Extract user data - handle different response structures
      Map<String, dynamic> userData;
      if (response['user'] != null) {
        userData = response['user'];
      } else if (response['data'] != null && response['data']['user'] != null) {
        userData = response['data']['user'];
      } else {
        userData = response; // Assume the response itself is user data
      }
      
      final user = User.fromJson(userData);
      
      state = state.copyWith(
        user: user,
        isAuthenticated: true,
        isLoading: false,
      );
      
      return true;
    } catch (e) {
      state = state.copyWith(
        error: e.toString(),
        isLoading: false,
      );
      return false;
    }
  }

  // Register
  Future<bool> register(Map<String, dynamic> userData) async {
    state = state.copyWith(isLoading: true, error: null);
    
    try {
      final response = await _apiService.register(userData);
      final user = User.fromJson(response['user']);
      
      state = state.copyWith(
        user: user,
        isAuthenticated: true,
        isLoading: false,
      );
      
      return true;
    } catch (e) {
      state = state.copyWith(
        error: e.toString(),
        isLoading: false,
      );
      return false;
    }
  }

  // Logout
  Future<void> logout() async {
    state = state.copyWith(isLoading: true);
    
    try {
      await _apiService.logout();
    } catch (e) {
      // Continue with logout even if API call fails
    }
    
    state = const AuthState(isInitialized: true);
  }

  // Update user profile
  Future<bool> updateProfile(Map<String, dynamic> profileData) async {
    state = state.copyWith(isLoading: true, error: null);
    
    try {
      final response = await _apiService.updateProfile(profileData);
      final updatedUser = User.fromJson(response);
      
      state = state.copyWith(
        user: updatedUser,
        isLoading: false,
      );
      
      return true;
    } catch (e) {
      state = state.copyWith(
        error: e.toString(),
        isLoading: false,
      );
      return false;
    }
  }

  // Upload KYC documents
  Future<bool> uploadKycDocuments(Map<String, dynamic> documents) async {
    state = state.copyWith(isLoading: true, error: null);
    
    try {
      final response = await _apiService.uploadKyc(documents);
      final updatedUser = User.fromJson(response['user']);
      
      state = state.copyWith(
        user: updatedUser,
        isLoading: false,
      );
      
      return true;
    } catch (e) {
      state = state.copyWith(
        error: e.toString(),
        isLoading: false,
      );
      return false;
    }
  }

  // Clear error
  void clearError() {
    state = state.copyWith(error: null);
  }

  // Check if user has permission
  bool hasPermission(String permission) {
    if (state.user == null) return false;
    
    // TODO: Implement permission checking based on user role
    // For now, return true for admin users
    return state.user!.role == 'admin' || state.user!.role == 'super_admin';
  }

  // Check if user has role
  bool hasRole(String role) {
    return state.user?.role == role;
  }

  // Check if user can access feature
  bool canAccess(String feature) {
    if (state.user == null) return false;
    
    switch (feature) {
      case 'products':
        return ['seller', 'admin', 'super_admin'].contains(state.user!.role);
      case 'businesses':
        return ['seller', 'admin', 'super_admin'].contains(state.user!.role);
      case 'investments':
        return ['buyer', 'admin', 'super_admin'].contains(state.user!.role);
      case 'admin':
        return ['admin', 'super_admin'].contains(state.user!.role);
      default:
        return true;
    }
  }
}

// Providers
final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier(apiService);
});

final userProvider = Provider<User?>((ref) {
  return ref.watch(authProvider).user;
});

final isAuthenticatedProvider = Provider<bool>((ref) {
  return ref.watch(authProvider).isAuthenticated;
});

final isLoadingProvider = Provider<bool>((ref) {
  return ref.watch(authProvider).isLoading;
});

final authErrorProvider = Provider<String?>((ref) {
  return ref.watch(authProvider).error;
});
