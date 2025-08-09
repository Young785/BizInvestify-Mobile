import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/auth_models.dart';
import '../services/auth_service.dart';

/// Auth service provider
final authServiceProvider = Provider<AuthService>((ref) {
  return AuthService();
});

/// Email verification service provider
final emailVerificationServiceProvider = Provider<EmailVerificationService>((ref) {
  return EmailVerificationService();
});

/// Phone verification service provider
final phoneVerificationServiceProvider = Provider<PhoneVerificationService>((ref) {
  return PhoneVerificationService();
});

/// Two-factor authentication service provider
final twoFactorServiceProvider = Provider<TwoFactorService>((ref) {
  return TwoFactorService();
});

/// Auth state notifier
class AuthNotifier extends StateNotifier<AuthState> {
  final AuthService _authService;
  final EmailVerificationService _emailVerificationService;
  final PhoneVerificationService _phoneVerificationService;
  final TwoFactorService _twoFactorService;

  AuthNotifier(
    this._authService,
    this._emailVerificationService,
    this._phoneVerificationService,
    this._twoFactorService,
  ) : super(const AuthState()) {
    _initializeAuth();
  }

  /// Initialize authentication state on app start
  Future<void> _initializeAuth() async {
    state = state.copyWith(isLoading: true);

    try {
      final isAuthenticated = await _authService.isAuthenticated();
      if (isAuthenticated) {
        final user = await _authService.getUser();
        final token = await _authService.getToken();

        if (user != null && token != null) {
          // Get current user data from server to ensure it's up to date
          final response = await _authService.getCurrentUser();
          if (response.success && response.data != null) {
            final userData = response.data!['user'];
            final verificationStatus = response.data!['verification_status'];
            final verificationProgress = response.data!['verification_progress'] ?? 0;

            state = state.copyWith(
              user: User.fromJson(userData),
              token: token,
              isAuthenticated: true,
              verificationStatus: VerificationStatus.fromJson(verificationStatus),
              verificationProgress: verificationProgress,
              isLoading: false,
              error: null,
            );
          } else {
            // Token might be invalid, clear it
            await _authService.logout();
            state = const AuthState();
          }
        } else {
          state = const AuthState();
        }
      } else {
        state = const AuthState();
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }

  /// Register a new user
  Future<bool> register(RegistrationData data) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final response = await _authService.register(data);
      
      if (response.success && response.data != null) {
        // Store registration progress for email verification
        await _authService.setVerificationProgress(
          VerificationStep.email,
          {
            'email': data.email,
            'user_id': response.data!['user_id'] ?? 0,
          },
        );

        state = state.copyWith(
          isLoading: false,
          error: null,
        );
        return true;
      } else {
        state = state.copyWith(
          isLoading: false,
          error: response.message,
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      return false;
    }
  }

  /// Login user with optional 2FA
  Future<LoginResult> login(LoginData data) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final response = await _authService.login(data);
      
      if (response.success) {
        if (response.data?.requires2FA == true) {
          // 2FA required
          state = state.copyWith(
            isLoading: false,
            error: null,
          );
          return LoginResult.requires2FA;
        } else if (response.data != null) {
          // Successful login
          final authResponse = response.data!;
          
          state = state.copyWith(
            user: authResponse.user,
            token: authResponse.token,
            isAuthenticated: true,
            verificationProgress: authResponse.verificationProgress ?? 0,
            isLoading: false,
            error: null,
          );
          
          // Get detailed verification status; if it fails, still treat login as success
          try {
            await refreshUserData();
          } catch (_) {}
          
          return LoginResult.success;
        }
      }
      
      state = state.copyWith(
        isLoading: false,
        error: response.message,
      );
      return LoginResult.failed;
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: 'Login failed. Please check your credentials and try again.',
      );
      return LoginResult.failed;
    }
  }

  /// Logout current user
  Future<void> logout() async {
    state = state.copyWith(isLoading: true);

    try {
      await _authService.logout();
      state = const AuthState();
    } catch (e) {
      // Clear state even if logout fails
      state = AuthState(error: e.toString());
    }
  }

  /// Refresh user data from server
  Future<void> refreshUserData() async {
    if (!state.isAuthenticated) return;

    try {
      final response = await _authService.getCurrentUser();
      if (response.success && response.data != null) {
        final userData = response.data!['user'];
        final verificationStatus = response.data!['verification_status'];
        final verificationProgress = response.data!['verification_progress'] ?? 0;

        state = state.copyWith(
          user: User.fromJson(userData),
          verificationStatus: VerificationStatus.fromJson(verificationStatus),
          verificationProgress: verificationProgress,
        );
      } else if (!response.success && (response.message.isNotEmpty)) {
        // Keep user logged in; surface a friendly message without breaking flow
        state = state.copyWith(error: response.message);
      }
    } catch (e) {
      // Don't kick user out on a transient error
      state = state.copyWith(error: 'Could not refresh profile at the moment.');
    }
  }

  /// Update user profile
  Future<bool> updateProfile(Map<String, dynamic> data) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final response = await _authService.updateProfile(data);
      
      if (response.success) {
        await refreshUserData();
        state = state.copyWith(isLoading: false);
        return true;
      } else {
        state = state.copyWith(
          isLoading: false,
          error: response.message,
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      return false;
    }
  }

  /// Change password
  Future<bool> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final response = await _authService.changePassword(
        currentPassword: currentPassword,
        newPassword: newPassword,
        confirmPassword: confirmPassword,
      );

      if (response.success) {
        state = state.copyWith(isLoading: false);
        return true;
      } else {
        state = state.copyWith(
          isLoading: false,
          error: response.message,
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      return false;
    }
  }

  /// Verify email with token
  Future<bool> verifyEmail(String token, String email) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final response = await _emailVerificationService.verifyEmail(token, email);
      
      if (response.success) {
        await refreshUserData();
        state = state.copyWith(isLoading: false);
        return true;
      } else {
        state = state.copyWith(
          isLoading: false,
          error: response.message,
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      return false;
    }
  }

  /// Resend email verification
  Future<bool> resendEmailVerification(String email) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final response = await _emailVerificationService.resendVerification(email);
      
      state = state.copyWith(
        isLoading: false,
        error: response.success ? null : response.message,
      );
      
      return response.success;
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      return false;
    }
  }

  /// Send phone verification code
  Future<bool> sendPhoneVerificationCode(String email, String phone) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final response = await _phoneVerificationService.sendCode(email, phone);
      
      state = state.copyWith(
        isLoading: false,
        error: response.success ? null : response.message,
      );
      
      return response.success;
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      return false;
    }
  }

  /// Verify phone with code
  Future<bool> verifyPhone(String email, String code) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final response = await _phoneVerificationService.verifyCode(email, code);
      
      if (response.success) {
        await refreshUserData();
        state = state.copyWith(isLoading: false);
        return true;
      } else {
        state = state.copyWith(
          isLoading: false,
          error: response.message,
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      return false;
    }
  }

  /// Setup 2FA
  Future<TwoFactorSetupData?> setup2FA() async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final response = await _twoFactorService.setup();
      
      if (response.success && response.data != null) {
        state = state.copyWith(isLoading: false);
        return response.data!;
      } else {
        state = state.copyWith(
          isLoading: false,
          error: response.message,
        );
        return null;
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      return null;
    }
  }

  /// Confirm 2FA setup
  Future<bool> confirm2FA(String code) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final response = await _twoFactorService.confirm(code);
      
      if (response.success) {
        await refreshUserData();
        state = state.copyWith(isLoading: false);
        return true;
      } else {
        state = state.copyWith(
          isLoading: false,
          error: response.message,
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      return false;
    }
  }

  /// Skip 2FA setup
  Future<bool> skip2FA() async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final response = await _twoFactorService.skip();
      
      if (response.success) {
        await refreshUserData();
        state = state.copyWith(isLoading: false);
        return true;
      } else {
        state = state.copyWith(
          isLoading: false,
          error: response.message,
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      return false;
    }
  }

  /// Verify 2FA for session
  Future<bool> verify2FAForSession(String code) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final response = await _twoFactorService.verify2FAForSession(code);
      
      if (response.success) {
        await refreshUserData();
        state = state.copyWith(isLoading: false);
        return true;
      } else {
        state = state.copyWith(
          isLoading: false,
          error: response.message,
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      return false;
    }
  }

  /// Clear error state
  void clearError() {
    state = state.copyWith(error: null);
  }

  /// Set verification progress
  Future<void> setVerificationProgress(VerificationStep step, Map<String, dynamic> data) async {
    await _authService.setVerificationProgress(step, data);
  }

  /// Get verification progress
  Future<Map<String, dynamic>?> getVerificationProgress() async {
    return await _authService.getVerificationProgress();
  }

  /// Clear verification progress
  Future<void> clearVerificationProgress() async {
    await _authService.clearVerificationProgress();
  }
}

/// Auth state provider
final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier(
    ref.read(authServiceProvider),
    ref.read(emailVerificationServiceProvider),
    ref.read(phoneVerificationServiceProvider),
    ref.read(twoFactorServiceProvider),
  );
});

/// Login result enum
enum LoginResult {
  success,
  requires2FA,
  failed,
}

/// Computed providers for common auth checks
final isAuthenticatedProvider = Provider<bool>((ref) {
  return ref.watch(authProvider).isAuthenticated;
});

final currentUserProvider = Provider<User?>((ref) {
  return ref.watch(authProvider).user;
});

final isEmailVerifiedProvider = Provider<bool>((ref) {
  final verificationStatus = ref.watch(authProvider).verificationStatus;
  return verificationStatus?.emailVerified ?? false;
});

final isPhoneVerifiedProvider = Provider<bool>((ref) {
  final verificationStatus = ref.watch(authProvider).verificationStatus;
  return verificationStatus?.phoneVerified ?? false;
});

final isKycVerifiedProvider = Provider<bool>((ref) {
  final verificationStatus = ref.watch(authProvider).verificationStatus;
  return verificationStatus?.kycVerified ?? false;
});

final is2FAEnabledProvider = Provider<bool>((ref) {
  final verificationStatus = ref.watch(authProvider).verificationStatus;
  return verificationStatus?.twoFactorEnabled ?? false;
});

final isFullyVerifiedProvider = Provider<bool>((ref) {
  final verificationStatus = ref.watch(authProvider).verificationStatus;
  return verificationStatus?.fullyVerified ?? false;
});

final verificationProgressProvider = Provider<int>((ref) {
  return ref.watch(authProvider).verificationProgress;
});

final authErrorProvider = Provider<String?>((ref) {
  return ref.watch(authProvider).error;
});

final authLoadingProvider = Provider<bool>((ref) {
  return ref.watch(authProvider).isLoading;
});
