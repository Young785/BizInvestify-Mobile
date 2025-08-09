// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'auth_models.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$UserImpl _$$UserImplFromJson(Map<String, dynamic> json) => _$UserImpl(
      id: (json['id'] as num).toInt(),
      firstName: json['first_name'] as String,
      lastName: json['last_name'] as String,
      name: json['name'] as String,
      email: json['email'] as String,
      phone: json['phone'] as String?,
      role: json['role'] as String,
      avatarUrl: json['avatar_url'] as String?,
      kycStatus: json['kyc_status'] as String,
      trustScore: (json['trust_score'] as num).toDouble(),
      isVerified: json['is_verified'] as bool,
      emailVerifiedAt: json['email_verified_at'] as String?,
      phoneVerifiedAt: json['phone_verified_at'] as String?,
      twoFactorConfirmedAt: json['two_factor_confirmed_at'] as String?,
      createdAt: json['created_at'] as String,
      updatedAt: json['updated_at'] as String,
      businessName: json['business_name'] as String?,
      businessType: json['business_type'] as String?,
      investmentAmount: json['investment_amount'] as String?,
      investmentFocus: json['investment_focus'] as String?,
    );

Map<String, dynamic> _$$UserImplToJson(_$UserImpl instance) =>
    <String, dynamic>{
      'id': instance.id,
      'first_name': instance.firstName,
      'last_name': instance.lastName,
      'name': instance.name,
      'email': instance.email,
      'phone': instance.phone,
      'role': instance.role,
      'avatar_url': instance.avatarUrl,
      'kyc_status': instance.kycStatus,
      'trust_score': instance.trustScore,
      'is_verified': instance.isVerified,
      'email_verified_at': instance.emailVerifiedAt,
      'phone_verified_at': instance.phoneVerifiedAt,
      'two_factor_confirmed_at': instance.twoFactorConfirmedAt,
      'created_at': instance.createdAt,
      'updated_at': instance.updatedAt,
      'business_name': instance.businessName,
      'business_type': instance.businessType,
      'investment_amount': instance.investmentAmount,
      'investment_focus': instance.investmentFocus,
    };

_$RegistrationDataImpl _$$RegistrationDataImplFromJson(
        Map<String, dynamic> json) =>
    _$RegistrationDataImpl(
      role: json['role'] as String,
      firstName: json['firstName'] as String,
      lastName: json['lastName'] as String,
      email: json['email'] as String,
      phone: json['phone'] as String,
      password: json['password'] as String,
      passwordConfirmation: json['password_confirmation'] as String,
      businessName: json['businessName'] as String?,
      businessType: json['businessType'] as String?,
      investmentAmount: json['investmentAmount'] as String?,
      investmentFocus: json['investmentFocus'] as String?,
      agreeToTerms: json['agreeToTerms'] as bool,
      agreeToPrivacy: json['agreeToPrivacy'] as bool,
      confirmAge: json['confirmAge'] as bool,
      confirmIdentity: json['confirmIdentity'] as bool,
    );

Map<String, dynamic> _$$RegistrationDataImplToJson(
        _$RegistrationDataImpl instance) =>
    <String, dynamic>{
      'role': instance.role,
      'firstName': instance.firstName,
      'lastName': instance.lastName,
      'email': instance.email,
      'phone': instance.phone,
      'password': instance.password,
      'password_confirmation': instance.passwordConfirmation,
      'businessName': instance.businessName,
      'businessType': instance.businessType,
      'investmentAmount': instance.investmentAmount,
      'investmentFocus': instance.investmentFocus,
      'agreeToTerms': instance.agreeToTerms,
      'agreeToPrivacy': instance.agreeToPrivacy,
      'confirmAge': instance.confirmAge,
      'confirmIdentity': instance.confirmIdentity,
    };

_$LoginDataImpl _$$LoginDataImplFromJson(Map<String, dynamic> json) =>
    _$LoginDataImpl(
      email: json['email'] as String,
      password: json['password'] as String,
      twoFactorCode: json['two_factor_code'] as String?,
      rememberDevice: json['remember_device'] as bool? ?? false,
    );

Map<String, dynamic> _$$LoginDataImplToJson(_$LoginDataImpl instance) =>
    <String, dynamic>{
      'email': instance.email,
      'password': instance.password,
      'two_factor_code': instance.twoFactorCode,
      'remember_device': instance.rememberDevice,
    };

_$AuthResponseImpl _$$AuthResponseImplFromJson(Map<String, dynamic> json) =>
    _$AuthResponseImpl(
      user: User.fromJson(json['user'] as Map<String, dynamic>),
      token: json['token'] as String,
      tokenType: json['token_type'] as String? ?? 'Bearer',
      verificationProgress: (json['verification_progress'] as num?)?.toInt(),
      requiresVerification: json['requires_verification'] as bool?,
      requires2FA: json['requires_2fa'] as bool?,
    );

Map<String, dynamic> _$$AuthResponseImplToJson(_$AuthResponseImpl instance) =>
    <String, dynamic>{
      'user': instance.user,
      'token': instance.token,
      'token_type': instance.tokenType,
      'verification_progress': instance.verificationProgress,
      'requires_verification': instance.requiresVerification,
      'requires_2fa': instance.requires2FA,
    };

_$VerificationStatusImpl _$$VerificationStatusImplFromJson(
        Map<String, dynamic> json) =>
    _$VerificationStatusImpl(
      emailVerified: json['email_verified'] as bool,
      phoneVerified: json['phone_verified'] as bool,
      kycVerified: json['kyc_verified'] as bool,
      kycSubmitted: json['kyc_submitted'] as bool,
      kycStatus: json['kyc_status'] as String,
      twoFactorEnabled: json['two_factor_enabled'] as bool,
      fullyVerified: json['fully_verified'] as bool,
    );

Map<String, dynamic> _$$VerificationStatusImplToJson(
        _$VerificationStatusImpl instance) =>
    <String, dynamic>{
      'email_verified': instance.emailVerified,
      'phone_verified': instance.phoneVerified,
      'kyc_verified': instance.kycVerified,
      'kyc_submitted': instance.kycSubmitted,
      'kyc_status': instance.kycStatus,
      'two_factor_enabled': instance.twoFactorEnabled,
      'fully_verified': instance.fullyVerified,
    };

_$TwoFactorSetupDataImpl _$$TwoFactorSetupDataImplFromJson(
        Map<String, dynamic> json) =>
    _$TwoFactorSetupDataImpl(
      secretKey: json['secret_key'] as String,
      qrCodeUrl: json['qr_code_url'] as String,
      backupCodes: (json['backup_codes'] as List<dynamic>)
          .map((e) => e as String)
          .toList(),
      manualEntryKey: json['manual_entry_key'] as String,
    );

Map<String, dynamic> _$$TwoFactorSetupDataImplToJson(
        _$TwoFactorSetupDataImpl instance) =>
    <String, dynamic>{
      'secret_key': instance.secretKey,
      'qr_code_url': instance.qrCodeUrl,
      'backup_codes': instance.backupCodes,
      'manual_entry_key': instance.manualEntryKey,
    };

_$ApiResponseImpl<T> _$$ApiResponseImplFromJson<T>(
  Map<String, dynamic> json,
  T Function(Object? json) fromJsonT,
) =>
    _$ApiResponseImpl<T>(
      success: json['success'] as bool,
      message: json['message'] as String,
      data: _$nullableGenericFromJson(json['data'], fromJsonT),
      errors: json['errors'] as Map<String, dynamic>?,
    );

Map<String, dynamic> _$$ApiResponseImplToJson<T>(
  _$ApiResponseImpl<T> instance,
  Object? Function(T value) toJsonT,
) =>
    <String, dynamic>{
      'success': instance.success,
      'message': instance.message,
      'data': _$nullableGenericToJson(instance.data, toJsonT),
      'errors': instance.errors,
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

_$AuthStateImpl _$$AuthStateImplFromJson(Map<String, dynamic> json) =>
    _$AuthStateImpl(
      user: json['user'] == null
          ? null
          : User.fromJson(json['user'] as Map<String, dynamic>),
      token: json['token'] as String?,
      isAuthenticated: json['isAuthenticated'] as bool? ?? false,
      isLoading: json['isLoading'] as bool? ?? false,
      error: json['error'] as String?,
      verificationStatus: json['verificationStatus'] == null
          ? null
          : VerificationStatus.fromJson(
              json['verificationStatus'] as Map<String, dynamic>),
      verificationProgress:
          (json['verificationProgress'] as num?)?.toInt() ?? 0,
    );

Map<String, dynamic> _$$AuthStateImplToJson(_$AuthStateImpl instance) =>
    <String, dynamic>{
      'user': instance.user,
      'token': instance.token,
      'isAuthenticated': instance.isAuthenticated,
      'isLoading': instance.isLoading,
      'error': instance.error,
      'verificationStatus': instance.verificationStatus,
      'verificationProgress': instance.verificationProgress,
    };
