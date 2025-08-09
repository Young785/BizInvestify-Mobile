// ignore_for_file: invalid_annotation_target
import 'package:freezed_annotation/freezed_annotation.dart';

part 'auth_models.freezed.dart';
part 'auth_models.g.dart';

/// User model matching backend API
@freezed
class User with _$User {
  const factory User({
    required int id,
    @JsonKey(name: 'first_name') required String firstName,
    @JsonKey(name: 'last_name') required String lastName,
    required String name,
    required String email,
    String? phone,
    required String role, // 'seller', 'investor', 'admin'
    @JsonKey(name: 'avatar_url') String? avatarUrl,
    @JsonKey(name: 'kyc_status') required String kycStatus, // 'pending', 'verified', 'rejected'
    @JsonKey(name: 'trust_score') required double trustScore,
    @JsonKey(name: 'is_verified') required bool isVerified,
    @JsonKey(name: 'email_verified_at') String? emailVerifiedAt,
    @JsonKey(name: 'phone_verified_at') String? phoneVerifiedAt,
    @JsonKey(name: 'two_factor_confirmed_at') String? twoFactorConfirmedAt,
    @JsonKey(name: 'created_at') required String createdAt,
    @JsonKey(name: 'updated_at') required String updatedAt,
    @JsonKey(name: 'business_name') String? businessName,
    @JsonKey(name: 'business_type') String? businessType,
    @JsonKey(name: 'investment_amount') String? investmentAmount,
    @JsonKey(name: 'investment_focus') String? investmentFocus,
  }) = _User;

  factory User.fromJson(Map<String, dynamic> json) => _$UserFromJson(json);
}

/// Registration request model
@freezed
class RegistrationData with _$RegistrationData {
  const factory RegistrationData({
    required String role, // 'seller' or 'investor'
    required String firstName,
    required String lastName,
    required String email,
    required String phone,
    required String password,
    @JsonKey(name: 'password_confirmation') required String passwordConfirmation,
    String? businessName,
    String? businessType,
    String? investmentAmount,
    String? investmentFocus,
    required bool agreeToTerms,
    required bool agreeToPrivacy,
    required bool confirmAge,
    required bool confirmIdentity,
  }) = _RegistrationData;

  factory RegistrationData.fromJson(Map<String, dynamic> json) =>
      _$RegistrationDataFromJson(json);
}

/// Login request model
@freezed
class LoginData with _$LoginData {
  const factory LoginData({
    required String email,
    required String password,
    @JsonKey(name: 'two_factor_code') String? twoFactorCode,
    @JsonKey(name: 'remember_device') @Default(false) bool rememberDevice,
  }) = _LoginData;

  factory LoginData.fromJson(Map<String, dynamic> json) =>
      _$LoginDataFromJson(json);
}

/// Authentication response model
@freezed
class AuthResponse with _$AuthResponse {
  const factory AuthResponse({
    required User user,
    required String token,
    @JsonKey(name: 'token_type') @Default('Bearer') String tokenType,
    @JsonKey(name: 'verification_progress') int? verificationProgress,
    @JsonKey(name: 'requires_verification') bool? requiresVerification,
    @JsonKey(name: 'requires_2fa') bool? requires2FA,
  }) = _AuthResponse;

  factory AuthResponse.fromJson(Map<String, dynamic> json) =>
      _$AuthResponseFromJson(json);
}

/// Verification status model
@freezed
class VerificationStatus with _$VerificationStatus {
  const factory VerificationStatus({
    @JsonKey(name: 'email_verified') required bool emailVerified,
    @JsonKey(name: 'phone_verified') required bool phoneVerified,
    @JsonKey(name: 'kyc_verified') required bool kycVerified,
    @JsonKey(name: 'kyc_submitted') required bool kycSubmitted,
    @JsonKey(name: 'kyc_status') required String kycStatus,
    @JsonKey(name: 'two_factor_enabled') required bool twoFactorEnabled,
    @JsonKey(name: 'fully_verified') required bool fullyVerified,
  }) = _VerificationStatus;

  factory VerificationStatus.fromJson(Map<String, dynamic> json) =>
      _$VerificationStatusFromJson(json);
}

/// Two-factor setup data model
@freezed
class TwoFactorSetupData with _$TwoFactorSetupData {
  const factory TwoFactorSetupData({
    @JsonKey(name: 'secret_key') required String secretKey,
    @JsonKey(name: 'qr_code_url') required String qrCodeUrl,
    @JsonKey(name: 'backup_codes') required List<String> backupCodes,
    @JsonKey(name: 'manual_entry_key') required String manualEntryKey,
  }) = _TwoFactorSetupData;

  factory TwoFactorSetupData.fromJson(Map<String, dynamic> json) =>
      _$TwoFactorSetupDataFromJson(json);
}

/// API response wrapper
@Freezed(genericArgumentFactories: true)
class ApiResponse<T> with _$ApiResponse<T> {
  const factory ApiResponse({
    required bool success,
    required String message,
    T? data,
    Map<String, dynamic>? errors,
  }) = _ApiResponse<T>;

  factory ApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(Object? json) fromJsonT,
  ) =>
      _$ApiResponseFromJson(json, fromJsonT);
}

/// Authentication state
@freezed
class AuthState with _$AuthState {
  const factory AuthState({
    User? user,
    String? token,
    @Default(false) bool isAuthenticated,
    @Default(false) bool isLoading,
    String? error,
    VerificationStatus? verificationStatus,
    @Default(0) int verificationProgress,
  }) = _AuthState;

  factory AuthState.fromJson(Map<String, dynamic> json) =>
      _$AuthStateFromJson(json);
}

/// Login step for multi-step authentication
enum LoginStep {
  credentials,
  twoFactor,
  success,
}

/// Registration step for multi-step registration
enum RegistrationStep {
  roleSelection,
  basicInfo,
  professionalInfo,
  verification,
}

/// Verification step for onboarding flow
enum VerificationStep {
  email,
  phone,
  kyc,
  twoFactor,
  completed,
}

/// User role enum
enum UserRole {
  seller,
  investor,
  admin;

  String get value => name;

  static UserRole fromString(String value) {
    return UserRole.values.firstWhere(
      (role) => role.value == value,
      orElse: () => UserRole.seller,
    );
  }
}

/// KYC status enum
enum KycStatus {
  pending,
  verified,
  rejected;

  String get value => name;

  static KycStatus fromString(String value) {
    return KycStatus.values.firstWhere(
      (status) => status.value == value,
      orElse: () => KycStatus.pending,
    );
  }
}
