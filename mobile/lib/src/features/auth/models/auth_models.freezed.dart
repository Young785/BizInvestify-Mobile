// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'auth_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
    'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models');

User _$UserFromJson(Map<String, dynamic> json) {
  return _User.fromJson(json);
}

/// @nodoc
mixin _$User {
  int get id => throw _privateConstructorUsedError;
  @JsonKey(name: 'first_name')
  String get firstName => throw _privateConstructorUsedError;
  @JsonKey(name: 'last_name')
  String get lastName => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get email => throw _privateConstructorUsedError;
  String? get phone => throw _privateConstructorUsedError;
  String get role =>
      throw _privateConstructorUsedError; // 'seller', 'investor', 'admin'
  @JsonKey(name: 'avatar_url')
  String? get avatarUrl => throw _privateConstructorUsedError;
  @JsonKey(name: 'kyc_status')
  String get kycStatus =>
      throw _privateConstructorUsedError; // 'pending', 'verified', 'rejected'
  @JsonKey(name: 'trust_score')
  double get trustScore => throw _privateConstructorUsedError;
  @JsonKey(name: 'is_verified')
  bool get isVerified => throw _privateConstructorUsedError;
  @JsonKey(name: 'email_verified_at')
  String? get emailVerifiedAt => throw _privateConstructorUsedError;
  @JsonKey(name: 'phone_verified_at')
  String? get phoneVerifiedAt => throw _privateConstructorUsedError;
  @JsonKey(name: 'two_factor_confirmed_at')
  String? get twoFactorConfirmedAt => throw _privateConstructorUsedError;
  @JsonKey(name: 'created_at')
  String get createdAt => throw _privateConstructorUsedError;
  @JsonKey(name: 'updated_at')
  String get updatedAt => throw _privateConstructorUsedError;
  @JsonKey(name: 'business_name')
  String? get businessName => throw _privateConstructorUsedError;
  @JsonKey(name: 'business_type')
  String? get businessType => throw _privateConstructorUsedError;
  @JsonKey(name: 'investment_amount')
  String? get investmentAmount => throw _privateConstructorUsedError;
  @JsonKey(name: 'investment_focus')
  String? get investmentFocus => throw _privateConstructorUsedError;

  /// Serializes this User to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of User
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $UserCopyWith<User> get copyWith => throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $UserCopyWith<$Res> {
  factory $UserCopyWith(User value, $Res Function(User) then) =
      _$UserCopyWithImpl<$Res, User>;
  @useResult
  $Res call(
      {int id,
      @JsonKey(name: 'first_name') String firstName,
      @JsonKey(name: 'last_name') String lastName,
      String name,
      String email,
      String? phone,
      String role,
      @JsonKey(name: 'avatar_url') String? avatarUrl,
      @JsonKey(name: 'kyc_status') String kycStatus,
      @JsonKey(name: 'trust_score') double trustScore,
      @JsonKey(name: 'is_verified') bool isVerified,
      @JsonKey(name: 'email_verified_at') String? emailVerifiedAt,
      @JsonKey(name: 'phone_verified_at') String? phoneVerifiedAt,
      @JsonKey(name: 'two_factor_confirmed_at') String? twoFactorConfirmedAt,
      @JsonKey(name: 'created_at') String createdAt,
      @JsonKey(name: 'updated_at') String updatedAt,
      @JsonKey(name: 'business_name') String? businessName,
      @JsonKey(name: 'business_type') String? businessType,
      @JsonKey(name: 'investment_amount') String? investmentAmount,
      @JsonKey(name: 'investment_focus') String? investmentFocus});
}

/// @nodoc
class _$UserCopyWithImpl<$Res, $Val extends User>
    implements $UserCopyWith<$Res> {
  _$UserCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of User
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? firstName = null,
    Object? lastName = null,
    Object? name = null,
    Object? email = null,
    Object? phone = freezed,
    Object? role = null,
    Object? avatarUrl = freezed,
    Object? kycStatus = null,
    Object? trustScore = null,
    Object? isVerified = null,
    Object? emailVerifiedAt = freezed,
    Object? phoneVerifiedAt = freezed,
    Object? twoFactorConfirmedAt = freezed,
    Object? createdAt = null,
    Object? updatedAt = null,
    Object? businessName = freezed,
    Object? businessType = freezed,
    Object? investmentAmount = freezed,
    Object? investmentFocus = freezed,
  }) {
    return _then(_value.copyWith(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as int,
      firstName: null == firstName
          ? _value.firstName
          : firstName // ignore: cast_nullable_to_non_nullable
              as String,
      lastName: null == lastName
          ? _value.lastName
          : lastName // ignore: cast_nullable_to_non_nullable
              as String,
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      email: null == email
          ? _value.email
          : email // ignore: cast_nullable_to_non_nullable
              as String,
      phone: freezed == phone
          ? _value.phone
          : phone // ignore: cast_nullable_to_non_nullable
              as String?,
      role: null == role
          ? _value.role
          : role // ignore: cast_nullable_to_non_nullable
              as String,
      avatarUrl: freezed == avatarUrl
          ? _value.avatarUrl
          : avatarUrl // ignore: cast_nullable_to_non_nullable
              as String?,
      kycStatus: null == kycStatus
          ? _value.kycStatus
          : kycStatus // ignore: cast_nullable_to_non_nullable
              as String,
      trustScore: null == trustScore
          ? _value.trustScore
          : trustScore // ignore: cast_nullable_to_non_nullable
              as double,
      isVerified: null == isVerified
          ? _value.isVerified
          : isVerified // ignore: cast_nullable_to_non_nullable
              as bool,
      emailVerifiedAt: freezed == emailVerifiedAt
          ? _value.emailVerifiedAt
          : emailVerifiedAt // ignore: cast_nullable_to_non_nullable
              as String?,
      phoneVerifiedAt: freezed == phoneVerifiedAt
          ? _value.phoneVerifiedAt
          : phoneVerifiedAt // ignore: cast_nullable_to_non_nullable
              as String?,
      twoFactorConfirmedAt: freezed == twoFactorConfirmedAt
          ? _value.twoFactorConfirmedAt
          : twoFactorConfirmedAt // ignore: cast_nullable_to_non_nullable
              as String?,
      createdAt: null == createdAt
          ? _value.createdAt
          : createdAt // ignore: cast_nullable_to_non_nullable
              as String,
      updatedAt: null == updatedAt
          ? _value.updatedAt
          : updatedAt // ignore: cast_nullable_to_non_nullable
              as String,
      businessName: freezed == businessName
          ? _value.businessName
          : businessName // ignore: cast_nullable_to_non_nullable
              as String?,
      businessType: freezed == businessType
          ? _value.businessType
          : businessType // ignore: cast_nullable_to_non_nullable
              as String?,
      investmentAmount: freezed == investmentAmount
          ? _value.investmentAmount
          : investmentAmount // ignore: cast_nullable_to_non_nullable
              as String?,
      investmentFocus: freezed == investmentFocus
          ? _value.investmentFocus
          : investmentFocus // ignore: cast_nullable_to_non_nullable
              as String?,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$UserImplCopyWith<$Res> implements $UserCopyWith<$Res> {
  factory _$$UserImplCopyWith(
          _$UserImpl value, $Res Function(_$UserImpl) then) =
      __$$UserImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {int id,
      @JsonKey(name: 'first_name') String firstName,
      @JsonKey(name: 'last_name') String lastName,
      String name,
      String email,
      String? phone,
      String role,
      @JsonKey(name: 'avatar_url') String? avatarUrl,
      @JsonKey(name: 'kyc_status') String kycStatus,
      @JsonKey(name: 'trust_score') double trustScore,
      @JsonKey(name: 'is_verified') bool isVerified,
      @JsonKey(name: 'email_verified_at') String? emailVerifiedAt,
      @JsonKey(name: 'phone_verified_at') String? phoneVerifiedAt,
      @JsonKey(name: 'two_factor_confirmed_at') String? twoFactorConfirmedAt,
      @JsonKey(name: 'created_at') String createdAt,
      @JsonKey(name: 'updated_at') String updatedAt,
      @JsonKey(name: 'business_name') String? businessName,
      @JsonKey(name: 'business_type') String? businessType,
      @JsonKey(name: 'investment_amount') String? investmentAmount,
      @JsonKey(name: 'investment_focus') String? investmentFocus});
}

/// @nodoc
class __$$UserImplCopyWithImpl<$Res>
    extends _$UserCopyWithImpl<$Res, _$UserImpl>
    implements _$$UserImplCopyWith<$Res> {
  __$$UserImplCopyWithImpl(_$UserImpl _value, $Res Function(_$UserImpl) _then)
      : super(_value, _then);

  /// Create a copy of User
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? firstName = null,
    Object? lastName = null,
    Object? name = null,
    Object? email = null,
    Object? phone = freezed,
    Object? role = null,
    Object? avatarUrl = freezed,
    Object? kycStatus = null,
    Object? trustScore = null,
    Object? isVerified = null,
    Object? emailVerifiedAt = freezed,
    Object? phoneVerifiedAt = freezed,
    Object? twoFactorConfirmedAt = freezed,
    Object? createdAt = null,
    Object? updatedAt = null,
    Object? businessName = freezed,
    Object? businessType = freezed,
    Object? investmentAmount = freezed,
    Object? investmentFocus = freezed,
  }) {
    return _then(_$UserImpl(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as int,
      firstName: null == firstName
          ? _value.firstName
          : firstName // ignore: cast_nullable_to_non_nullable
              as String,
      lastName: null == lastName
          ? _value.lastName
          : lastName // ignore: cast_nullable_to_non_nullable
              as String,
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      email: null == email
          ? _value.email
          : email // ignore: cast_nullable_to_non_nullable
              as String,
      phone: freezed == phone
          ? _value.phone
          : phone // ignore: cast_nullable_to_non_nullable
              as String?,
      role: null == role
          ? _value.role
          : role // ignore: cast_nullable_to_non_nullable
              as String,
      avatarUrl: freezed == avatarUrl
          ? _value.avatarUrl
          : avatarUrl // ignore: cast_nullable_to_non_nullable
              as String?,
      kycStatus: null == kycStatus
          ? _value.kycStatus
          : kycStatus // ignore: cast_nullable_to_non_nullable
              as String,
      trustScore: null == trustScore
          ? _value.trustScore
          : trustScore // ignore: cast_nullable_to_non_nullable
              as double,
      isVerified: null == isVerified
          ? _value.isVerified
          : isVerified // ignore: cast_nullable_to_non_nullable
              as bool,
      emailVerifiedAt: freezed == emailVerifiedAt
          ? _value.emailVerifiedAt
          : emailVerifiedAt // ignore: cast_nullable_to_non_nullable
              as String?,
      phoneVerifiedAt: freezed == phoneVerifiedAt
          ? _value.phoneVerifiedAt
          : phoneVerifiedAt // ignore: cast_nullable_to_non_nullable
              as String?,
      twoFactorConfirmedAt: freezed == twoFactorConfirmedAt
          ? _value.twoFactorConfirmedAt
          : twoFactorConfirmedAt // ignore: cast_nullable_to_non_nullable
              as String?,
      createdAt: null == createdAt
          ? _value.createdAt
          : createdAt // ignore: cast_nullable_to_non_nullable
              as String,
      updatedAt: null == updatedAt
          ? _value.updatedAt
          : updatedAt // ignore: cast_nullable_to_non_nullable
              as String,
      businessName: freezed == businessName
          ? _value.businessName
          : businessName // ignore: cast_nullable_to_non_nullable
              as String?,
      businessType: freezed == businessType
          ? _value.businessType
          : businessType // ignore: cast_nullable_to_non_nullable
              as String?,
      investmentAmount: freezed == investmentAmount
          ? _value.investmentAmount
          : investmentAmount // ignore: cast_nullable_to_non_nullable
              as String?,
      investmentFocus: freezed == investmentFocus
          ? _value.investmentFocus
          : investmentFocus // ignore: cast_nullable_to_non_nullable
              as String?,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$UserImpl implements _User {
  const _$UserImpl(
      {required this.id,
      @JsonKey(name: 'first_name') required this.firstName,
      @JsonKey(name: 'last_name') required this.lastName,
      required this.name,
      required this.email,
      this.phone,
      required this.role,
      @JsonKey(name: 'avatar_url') this.avatarUrl,
      @JsonKey(name: 'kyc_status') required this.kycStatus,
      @JsonKey(name: 'trust_score') required this.trustScore,
      @JsonKey(name: 'is_verified') required this.isVerified,
      @JsonKey(name: 'email_verified_at') this.emailVerifiedAt,
      @JsonKey(name: 'phone_verified_at') this.phoneVerifiedAt,
      @JsonKey(name: 'two_factor_confirmed_at') this.twoFactorConfirmedAt,
      @JsonKey(name: 'created_at') required this.createdAt,
      @JsonKey(name: 'updated_at') required this.updatedAt,
      @JsonKey(name: 'business_name') this.businessName,
      @JsonKey(name: 'business_type') this.businessType,
      @JsonKey(name: 'investment_amount') this.investmentAmount,
      @JsonKey(name: 'investment_focus') this.investmentFocus});

  factory _$UserImpl.fromJson(Map<String, dynamic> json) =>
      _$$UserImplFromJson(json);

  @override
  final int id;
  @override
  @JsonKey(name: 'first_name')
  final String firstName;
  @override
  @JsonKey(name: 'last_name')
  final String lastName;
  @override
  final String name;
  @override
  final String email;
  @override
  final String? phone;
  @override
  final String role;
// 'seller', 'investor', 'admin'
  @override
  @JsonKey(name: 'avatar_url')
  final String? avatarUrl;
  @override
  @JsonKey(name: 'kyc_status')
  final String kycStatus;
// 'pending', 'verified', 'rejected'
  @override
  @JsonKey(name: 'trust_score')
  final double trustScore;
  @override
  @JsonKey(name: 'is_verified')
  final bool isVerified;
  @override
  @JsonKey(name: 'email_verified_at')
  final String? emailVerifiedAt;
  @override
  @JsonKey(name: 'phone_verified_at')
  final String? phoneVerifiedAt;
  @override
  @JsonKey(name: 'two_factor_confirmed_at')
  final String? twoFactorConfirmedAt;
  @override
  @JsonKey(name: 'created_at')
  final String createdAt;
  @override
  @JsonKey(name: 'updated_at')
  final String updatedAt;
  @override
  @JsonKey(name: 'business_name')
  final String? businessName;
  @override
  @JsonKey(name: 'business_type')
  final String? businessType;
  @override
  @JsonKey(name: 'investment_amount')
  final String? investmentAmount;
  @override
  @JsonKey(name: 'investment_focus')
  final String? investmentFocus;

  @override
  String toString() {
    return 'User(id: $id, firstName: $firstName, lastName: $lastName, name: $name, email: $email, phone: $phone, role: $role, avatarUrl: $avatarUrl, kycStatus: $kycStatus, trustScore: $trustScore, isVerified: $isVerified, emailVerifiedAt: $emailVerifiedAt, phoneVerifiedAt: $phoneVerifiedAt, twoFactorConfirmedAt: $twoFactorConfirmedAt, createdAt: $createdAt, updatedAt: $updatedAt, businessName: $businessName, businessType: $businessType, investmentAmount: $investmentAmount, investmentFocus: $investmentFocus)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$UserImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.firstName, firstName) ||
                other.firstName == firstName) &&
            (identical(other.lastName, lastName) ||
                other.lastName == lastName) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.email, email) || other.email == email) &&
            (identical(other.phone, phone) || other.phone == phone) &&
            (identical(other.role, role) || other.role == role) &&
            (identical(other.avatarUrl, avatarUrl) ||
                other.avatarUrl == avatarUrl) &&
            (identical(other.kycStatus, kycStatus) ||
                other.kycStatus == kycStatus) &&
            (identical(other.trustScore, trustScore) ||
                other.trustScore == trustScore) &&
            (identical(other.isVerified, isVerified) ||
                other.isVerified == isVerified) &&
            (identical(other.emailVerifiedAt, emailVerifiedAt) ||
                other.emailVerifiedAt == emailVerifiedAt) &&
            (identical(other.phoneVerifiedAt, phoneVerifiedAt) ||
                other.phoneVerifiedAt == phoneVerifiedAt) &&
            (identical(other.twoFactorConfirmedAt, twoFactorConfirmedAt) ||
                other.twoFactorConfirmedAt == twoFactorConfirmedAt) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt) &&
            (identical(other.updatedAt, updatedAt) ||
                other.updatedAt == updatedAt) &&
            (identical(other.businessName, businessName) ||
                other.businessName == businessName) &&
            (identical(other.businessType, businessType) ||
                other.businessType == businessType) &&
            (identical(other.investmentAmount, investmentAmount) ||
                other.investmentAmount == investmentAmount) &&
            (identical(other.investmentFocus, investmentFocus) ||
                other.investmentFocus == investmentFocus));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hashAll([
        runtimeType,
        id,
        firstName,
        lastName,
        name,
        email,
        phone,
        role,
        avatarUrl,
        kycStatus,
        trustScore,
        isVerified,
        emailVerifiedAt,
        phoneVerifiedAt,
        twoFactorConfirmedAt,
        createdAt,
        updatedAt,
        businessName,
        businessType,
        investmentAmount,
        investmentFocus
      ]);

  /// Create a copy of User
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$UserImplCopyWith<_$UserImpl> get copyWith =>
      __$$UserImplCopyWithImpl<_$UserImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$UserImplToJson(
      this,
    );
  }
}

abstract class _User implements User {
  const factory _User(
          {required final int id,
          @JsonKey(name: 'first_name') required final String firstName,
          @JsonKey(name: 'last_name') required final String lastName,
          required final String name,
          required final String email,
          final String? phone,
          required final String role,
          @JsonKey(name: 'avatar_url') final String? avatarUrl,
          @JsonKey(name: 'kyc_status') required final String kycStatus,
          @JsonKey(name: 'trust_score') required final double trustScore,
          @JsonKey(name: 'is_verified') required final bool isVerified,
          @JsonKey(name: 'email_verified_at') final String? emailVerifiedAt,
          @JsonKey(name: 'phone_verified_at') final String? phoneVerifiedAt,
          @JsonKey(name: 'two_factor_confirmed_at')
          final String? twoFactorConfirmedAt,
          @JsonKey(name: 'created_at') required final String createdAt,
          @JsonKey(name: 'updated_at') required final String updatedAt,
          @JsonKey(name: 'business_name') final String? businessName,
          @JsonKey(name: 'business_type') final String? businessType,
          @JsonKey(name: 'investment_amount') final String? investmentAmount,
          @JsonKey(name: 'investment_focus') final String? investmentFocus}) =
      _$UserImpl;

  factory _User.fromJson(Map<String, dynamic> json) = _$UserImpl.fromJson;

  @override
  int get id;
  @override
  @JsonKey(name: 'first_name')
  String get firstName;
  @override
  @JsonKey(name: 'last_name')
  String get lastName;
  @override
  String get name;
  @override
  String get email;
  @override
  String? get phone;
  @override
  String get role; // 'seller', 'investor', 'admin'
  @override
  @JsonKey(name: 'avatar_url')
  String? get avatarUrl;
  @override
  @JsonKey(name: 'kyc_status')
  String get kycStatus; // 'pending', 'verified', 'rejected'
  @override
  @JsonKey(name: 'trust_score')
  double get trustScore;
  @override
  @JsonKey(name: 'is_verified')
  bool get isVerified;
  @override
  @JsonKey(name: 'email_verified_at')
  String? get emailVerifiedAt;
  @override
  @JsonKey(name: 'phone_verified_at')
  String? get phoneVerifiedAt;
  @override
  @JsonKey(name: 'two_factor_confirmed_at')
  String? get twoFactorConfirmedAt;
  @override
  @JsonKey(name: 'created_at')
  String get createdAt;
  @override
  @JsonKey(name: 'updated_at')
  String get updatedAt;
  @override
  @JsonKey(name: 'business_name')
  String? get businessName;
  @override
  @JsonKey(name: 'business_type')
  String? get businessType;
  @override
  @JsonKey(name: 'investment_amount')
  String? get investmentAmount;
  @override
  @JsonKey(name: 'investment_focus')
  String? get investmentFocus;

  /// Create a copy of User
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$UserImplCopyWith<_$UserImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

RegistrationData _$RegistrationDataFromJson(Map<String, dynamic> json) {
  return _RegistrationData.fromJson(json);
}

/// @nodoc
mixin _$RegistrationData {
  String get role =>
      throw _privateConstructorUsedError; // 'seller' or 'investor'
  String get firstName => throw _privateConstructorUsedError;
  String get lastName => throw _privateConstructorUsedError;
  String get email => throw _privateConstructorUsedError;
  String get phone => throw _privateConstructorUsedError;
  String get password => throw _privateConstructorUsedError;
  @JsonKey(name: 'password_confirmation')
  String get passwordConfirmation => throw _privateConstructorUsedError;
  String? get businessName => throw _privateConstructorUsedError;
  String? get businessType => throw _privateConstructorUsedError;
  String? get investmentAmount => throw _privateConstructorUsedError;
  String? get investmentFocus => throw _privateConstructorUsedError;
  bool get agreeToTerms => throw _privateConstructorUsedError;
  bool get agreeToPrivacy => throw _privateConstructorUsedError;
  bool get confirmAge => throw _privateConstructorUsedError;
  bool get confirmIdentity => throw _privateConstructorUsedError;

  /// Serializes this RegistrationData to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of RegistrationData
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $RegistrationDataCopyWith<RegistrationData> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $RegistrationDataCopyWith<$Res> {
  factory $RegistrationDataCopyWith(
          RegistrationData value, $Res Function(RegistrationData) then) =
      _$RegistrationDataCopyWithImpl<$Res, RegistrationData>;
  @useResult
  $Res call(
      {String role,
      String firstName,
      String lastName,
      String email,
      String phone,
      String password,
      @JsonKey(name: 'password_confirmation') String passwordConfirmation,
      String? businessName,
      String? businessType,
      String? investmentAmount,
      String? investmentFocus,
      bool agreeToTerms,
      bool agreeToPrivacy,
      bool confirmAge,
      bool confirmIdentity});
}

/// @nodoc
class _$RegistrationDataCopyWithImpl<$Res, $Val extends RegistrationData>
    implements $RegistrationDataCopyWith<$Res> {
  _$RegistrationDataCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of RegistrationData
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? role = null,
    Object? firstName = null,
    Object? lastName = null,
    Object? email = null,
    Object? phone = null,
    Object? password = null,
    Object? passwordConfirmation = null,
    Object? businessName = freezed,
    Object? businessType = freezed,
    Object? investmentAmount = freezed,
    Object? investmentFocus = freezed,
    Object? agreeToTerms = null,
    Object? agreeToPrivacy = null,
    Object? confirmAge = null,
    Object? confirmIdentity = null,
  }) {
    return _then(_value.copyWith(
      role: null == role
          ? _value.role
          : role // ignore: cast_nullable_to_non_nullable
              as String,
      firstName: null == firstName
          ? _value.firstName
          : firstName // ignore: cast_nullable_to_non_nullable
              as String,
      lastName: null == lastName
          ? _value.lastName
          : lastName // ignore: cast_nullable_to_non_nullable
              as String,
      email: null == email
          ? _value.email
          : email // ignore: cast_nullable_to_non_nullable
              as String,
      phone: null == phone
          ? _value.phone
          : phone // ignore: cast_nullable_to_non_nullable
              as String,
      password: null == password
          ? _value.password
          : password // ignore: cast_nullable_to_non_nullable
              as String,
      passwordConfirmation: null == passwordConfirmation
          ? _value.passwordConfirmation
          : passwordConfirmation // ignore: cast_nullable_to_non_nullable
              as String,
      businessName: freezed == businessName
          ? _value.businessName
          : businessName // ignore: cast_nullable_to_non_nullable
              as String?,
      businessType: freezed == businessType
          ? _value.businessType
          : businessType // ignore: cast_nullable_to_non_nullable
              as String?,
      investmentAmount: freezed == investmentAmount
          ? _value.investmentAmount
          : investmentAmount // ignore: cast_nullable_to_non_nullable
              as String?,
      investmentFocus: freezed == investmentFocus
          ? _value.investmentFocus
          : investmentFocus // ignore: cast_nullable_to_non_nullable
              as String?,
      agreeToTerms: null == agreeToTerms
          ? _value.agreeToTerms
          : agreeToTerms // ignore: cast_nullable_to_non_nullable
              as bool,
      agreeToPrivacy: null == agreeToPrivacy
          ? _value.agreeToPrivacy
          : agreeToPrivacy // ignore: cast_nullable_to_non_nullable
              as bool,
      confirmAge: null == confirmAge
          ? _value.confirmAge
          : confirmAge // ignore: cast_nullable_to_non_nullable
              as bool,
      confirmIdentity: null == confirmIdentity
          ? _value.confirmIdentity
          : confirmIdentity // ignore: cast_nullable_to_non_nullable
              as bool,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$RegistrationDataImplCopyWith<$Res>
    implements $RegistrationDataCopyWith<$Res> {
  factory _$$RegistrationDataImplCopyWith(_$RegistrationDataImpl value,
          $Res Function(_$RegistrationDataImpl) then) =
      __$$RegistrationDataImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {String role,
      String firstName,
      String lastName,
      String email,
      String phone,
      String password,
      @JsonKey(name: 'password_confirmation') String passwordConfirmation,
      String? businessName,
      String? businessType,
      String? investmentAmount,
      String? investmentFocus,
      bool agreeToTerms,
      bool agreeToPrivacy,
      bool confirmAge,
      bool confirmIdentity});
}

/// @nodoc
class __$$RegistrationDataImplCopyWithImpl<$Res>
    extends _$RegistrationDataCopyWithImpl<$Res, _$RegistrationDataImpl>
    implements _$$RegistrationDataImplCopyWith<$Res> {
  __$$RegistrationDataImplCopyWithImpl(_$RegistrationDataImpl _value,
      $Res Function(_$RegistrationDataImpl) _then)
      : super(_value, _then);

  /// Create a copy of RegistrationData
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? role = null,
    Object? firstName = null,
    Object? lastName = null,
    Object? email = null,
    Object? phone = null,
    Object? password = null,
    Object? passwordConfirmation = null,
    Object? businessName = freezed,
    Object? businessType = freezed,
    Object? investmentAmount = freezed,
    Object? investmentFocus = freezed,
    Object? agreeToTerms = null,
    Object? agreeToPrivacy = null,
    Object? confirmAge = null,
    Object? confirmIdentity = null,
  }) {
    return _then(_$RegistrationDataImpl(
      role: null == role
          ? _value.role
          : role // ignore: cast_nullable_to_non_nullable
              as String,
      firstName: null == firstName
          ? _value.firstName
          : firstName // ignore: cast_nullable_to_non_nullable
              as String,
      lastName: null == lastName
          ? _value.lastName
          : lastName // ignore: cast_nullable_to_non_nullable
              as String,
      email: null == email
          ? _value.email
          : email // ignore: cast_nullable_to_non_nullable
              as String,
      phone: null == phone
          ? _value.phone
          : phone // ignore: cast_nullable_to_non_nullable
              as String,
      password: null == password
          ? _value.password
          : password // ignore: cast_nullable_to_non_nullable
              as String,
      passwordConfirmation: null == passwordConfirmation
          ? _value.passwordConfirmation
          : passwordConfirmation // ignore: cast_nullable_to_non_nullable
              as String,
      businessName: freezed == businessName
          ? _value.businessName
          : businessName // ignore: cast_nullable_to_non_nullable
              as String?,
      businessType: freezed == businessType
          ? _value.businessType
          : businessType // ignore: cast_nullable_to_non_nullable
              as String?,
      investmentAmount: freezed == investmentAmount
          ? _value.investmentAmount
          : investmentAmount // ignore: cast_nullable_to_non_nullable
              as String?,
      investmentFocus: freezed == investmentFocus
          ? _value.investmentFocus
          : investmentFocus // ignore: cast_nullable_to_non_nullable
              as String?,
      agreeToTerms: null == agreeToTerms
          ? _value.agreeToTerms
          : agreeToTerms // ignore: cast_nullable_to_non_nullable
              as bool,
      agreeToPrivacy: null == agreeToPrivacy
          ? _value.agreeToPrivacy
          : agreeToPrivacy // ignore: cast_nullable_to_non_nullable
              as bool,
      confirmAge: null == confirmAge
          ? _value.confirmAge
          : confirmAge // ignore: cast_nullable_to_non_nullable
              as bool,
      confirmIdentity: null == confirmIdentity
          ? _value.confirmIdentity
          : confirmIdentity // ignore: cast_nullable_to_non_nullable
              as bool,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$RegistrationDataImpl implements _RegistrationData {
  const _$RegistrationDataImpl(
      {required this.role,
      required this.firstName,
      required this.lastName,
      required this.email,
      required this.phone,
      required this.password,
      @JsonKey(name: 'password_confirmation')
      required this.passwordConfirmation,
      this.businessName,
      this.businessType,
      this.investmentAmount,
      this.investmentFocus,
      required this.agreeToTerms,
      required this.agreeToPrivacy,
      required this.confirmAge,
      required this.confirmIdentity});

  factory _$RegistrationDataImpl.fromJson(Map<String, dynamic> json) =>
      _$$RegistrationDataImplFromJson(json);

  @override
  final String role;
// 'seller' or 'investor'
  @override
  final String firstName;
  @override
  final String lastName;
  @override
  final String email;
  @override
  final String phone;
  @override
  final String password;
  @override
  @JsonKey(name: 'password_confirmation')
  final String passwordConfirmation;
  @override
  final String? businessName;
  @override
  final String? businessType;
  @override
  final String? investmentAmount;
  @override
  final String? investmentFocus;
  @override
  final bool agreeToTerms;
  @override
  final bool agreeToPrivacy;
  @override
  final bool confirmAge;
  @override
  final bool confirmIdentity;

  @override
  String toString() {
    return 'RegistrationData(role: $role, firstName: $firstName, lastName: $lastName, email: $email, phone: $phone, password: $password, passwordConfirmation: $passwordConfirmation, businessName: $businessName, businessType: $businessType, investmentAmount: $investmentAmount, investmentFocus: $investmentFocus, agreeToTerms: $agreeToTerms, agreeToPrivacy: $agreeToPrivacy, confirmAge: $confirmAge, confirmIdentity: $confirmIdentity)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$RegistrationDataImpl &&
            (identical(other.role, role) || other.role == role) &&
            (identical(other.firstName, firstName) ||
                other.firstName == firstName) &&
            (identical(other.lastName, lastName) ||
                other.lastName == lastName) &&
            (identical(other.email, email) || other.email == email) &&
            (identical(other.phone, phone) || other.phone == phone) &&
            (identical(other.password, password) ||
                other.password == password) &&
            (identical(other.passwordConfirmation, passwordConfirmation) ||
                other.passwordConfirmation == passwordConfirmation) &&
            (identical(other.businessName, businessName) ||
                other.businessName == businessName) &&
            (identical(other.businessType, businessType) ||
                other.businessType == businessType) &&
            (identical(other.investmentAmount, investmentAmount) ||
                other.investmentAmount == investmentAmount) &&
            (identical(other.investmentFocus, investmentFocus) ||
                other.investmentFocus == investmentFocus) &&
            (identical(other.agreeToTerms, agreeToTerms) ||
                other.agreeToTerms == agreeToTerms) &&
            (identical(other.agreeToPrivacy, agreeToPrivacy) ||
                other.agreeToPrivacy == agreeToPrivacy) &&
            (identical(other.confirmAge, confirmAge) ||
                other.confirmAge == confirmAge) &&
            (identical(other.confirmIdentity, confirmIdentity) ||
                other.confirmIdentity == confirmIdentity));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      role,
      firstName,
      lastName,
      email,
      phone,
      password,
      passwordConfirmation,
      businessName,
      businessType,
      investmentAmount,
      investmentFocus,
      agreeToTerms,
      agreeToPrivacy,
      confirmAge,
      confirmIdentity);

  /// Create a copy of RegistrationData
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$RegistrationDataImplCopyWith<_$RegistrationDataImpl> get copyWith =>
      __$$RegistrationDataImplCopyWithImpl<_$RegistrationDataImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$RegistrationDataImplToJson(
      this,
    );
  }
}

abstract class _RegistrationData implements RegistrationData {
  const factory _RegistrationData(
      {required final String role,
      required final String firstName,
      required final String lastName,
      required final String email,
      required final String phone,
      required final String password,
      @JsonKey(name: 'password_confirmation')
      required final String passwordConfirmation,
      final String? businessName,
      final String? businessType,
      final String? investmentAmount,
      final String? investmentFocus,
      required final bool agreeToTerms,
      required final bool agreeToPrivacy,
      required final bool confirmAge,
      required final bool confirmIdentity}) = _$RegistrationDataImpl;

  factory _RegistrationData.fromJson(Map<String, dynamic> json) =
      _$RegistrationDataImpl.fromJson;

  @override
  String get role; // 'seller' or 'investor'
  @override
  String get firstName;
  @override
  String get lastName;
  @override
  String get email;
  @override
  String get phone;
  @override
  String get password;
  @override
  @JsonKey(name: 'password_confirmation')
  String get passwordConfirmation;
  @override
  String? get businessName;
  @override
  String? get businessType;
  @override
  String? get investmentAmount;
  @override
  String? get investmentFocus;
  @override
  bool get agreeToTerms;
  @override
  bool get agreeToPrivacy;
  @override
  bool get confirmAge;
  @override
  bool get confirmIdentity;

  /// Create a copy of RegistrationData
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$RegistrationDataImplCopyWith<_$RegistrationDataImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

LoginData _$LoginDataFromJson(Map<String, dynamic> json) {
  return _LoginData.fromJson(json);
}

/// @nodoc
mixin _$LoginData {
  String get email => throw _privateConstructorUsedError;
  String get password => throw _privateConstructorUsedError;
  @JsonKey(name: 'two_factor_code')
  String? get twoFactorCode => throw _privateConstructorUsedError;
  @JsonKey(name: 'remember_device')
  bool get rememberDevice => throw _privateConstructorUsedError;

  /// Serializes this LoginData to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of LoginData
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $LoginDataCopyWith<LoginData> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $LoginDataCopyWith<$Res> {
  factory $LoginDataCopyWith(LoginData value, $Res Function(LoginData) then) =
      _$LoginDataCopyWithImpl<$Res, LoginData>;
  @useResult
  $Res call(
      {String email,
      String password,
      @JsonKey(name: 'two_factor_code') String? twoFactorCode,
      @JsonKey(name: 'remember_device') bool rememberDevice});
}

/// @nodoc
class _$LoginDataCopyWithImpl<$Res, $Val extends LoginData>
    implements $LoginDataCopyWith<$Res> {
  _$LoginDataCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of LoginData
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? email = null,
    Object? password = null,
    Object? twoFactorCode = freezed,
    Object? rememberDevice = null,
  }) {
    return _then(_value.copyWith(
      email: null == email
          ? _value.email
          : email // ignore: cast_nullable_to_non_nullable
              as String,
      password: null == password
          ? _value.password
          : password // ignore: cast_nullable_to_non_nullable
              as String,
      twoFactorCode: freezed == twoFactorCode
          ? _value.twoFactorCode
          : twoFactorCode // ignore: cast_nullable_to_non_nullable
              as String?,
      rememberDevice: null == rememberDevice
          ? _value.rememberDevice
          : rememberDevice // ignore: cast_nullable_to_non_nullable
              as bool,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$LoginDataImplCopyWith<$Res>
    implements $LoginDataCopyWith<$Res> {
  factory _$$LoginDataImplCopyWith(
          _$LoginDataImpl value, $Res Function(_$LoginDataImpl) then) =
      __$$LoginDataImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {String email,
      String password,
      @JsonKey(name: 'two_factor_code') String? twoFactorCode,
      @JsonKey(name: 'remember_device') bool rememberDevice});
}

/// @nodoc
class __$$LoginDataImplCopyWithImpl<$Res>
    extends _$LoginDataCopyWithImpl<$Res, _$LoginDataImpl>
    implements _$$LoginDataImplCopyWith<$Res> {
  __$$LoginDataImplCopyWithImpl(
      _$LoginDataImpl _value, $Res Function(_$LoginDataImpl) _then)
      : super(_value, _then);

  /// Create a copy of LoginData
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? email = null,
    Object? password = null,
    Object? twoFactorCode = freezed,
    Object? rememberDevice = null,
  }) {
    return _then(_$LoginDataImpl(
      email: null == email
          ? _value.email
          : email // ignore: cast_nullable_to_non_nullable
              as String,
      password: null == password
          ? _value.password
          : password // ignore: cast_nullable_to_non_nullable
              as String,
      twoFactorCode: freezed == twoFactorCode
          ? _value.twoFactorCode
          : twoFactorCode // ignore: cast_nullable_to_non_nullable
              as String?,
      rememberDevice: null == rememberDevice
          ? _value.rememberDevice
          : rememberDevice // ignore: cast_nullable_to_non_nullable
              as bool,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$LoginDataImpl implements _LoginData {
  const _$LoginDataImpl(
      {required this.email,
      required this.password,
      @JsonKey(name: 'two_factor_code') this.twoFactorCode,
      @JsonKey(name: 'remember_device') this.rememberDevice = false});

  factory _$LoginDataImpl.fromJson(Map<String, dynamic> json) =>
      _$$LoginDataImplFromJson(json);

  @override
  final String email;
  @override
  final String password;
  @override
  @JsonKey(name: 'two_factor_code')
  final String? twoFactorCode;
  @override
  @JsonKey(name: 'remember_device')
  final bool rememberDevice;

  @override
  String toString() {
    return 'LoginData(email: $email, password: $password, twoFactorCode: $twoFactorCode, rememberDevice: $rememberDevice)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$LoginDataImpl &&
            (identical(other.email, email) || other.email == email) &&
            (identical(other.password, password) ||
                other.password == password) &&
            (identical(other.twoFactorCode, twoFactorCode) ||
                other.twoFactorCode == twoFactorCode) &&
            (identical(other.rememberDevice, rememberDevice) ||
                other.rememberDevice == rememberDevice));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode =>
      Object.hash(runtimeType, email, password, twoFactorCode, rememberDevice);

  /// Create a copy of LoginData
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$LoginDataImplCopyWith<_$LoginDataImpl> get copyWith =>
      __$$LoginDataImplCopyWithImpl<_$LoginDataImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$LoginDataImplToJson(
      this,
    );
  }
}

abstract class _LoginData implements LoginData {
  const factory _LoginData(
          {required final String email,
          required final String password,
          @JsonKey(name: 'two_factor_code') final String? twoFactorCode,
          @JsonKey(name: 'remember_device') final bool rememberDevice}) =
      _$LoginDataImpl;

  factory _LoginData.fromJson(Map<String, dynamic> json) =
      _$LoginDataImpl.fromJson;

  @override
  String get email;
  @override
  String get password;
  @override
  @JsonKey(name: 'two_factor_code')
  String? get twoFactorCode;
  @override
  @JsonKey(name: 'remember_device')
  bool get rememberDevice;

  /// Create a copy of LoginData
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$LoginDataImplCopyWith<_$LoginDataImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

AuthResponse _$AuthResponseFromJson(Map<String, dynamic> json) {
  return _AuthResponse.fromJson(json);
}

/// @nodoc
mixin _$AuthResponse {
  User get user => throw _privateConstructorUsedError;
  String get token => throw _privateConstructorUsedError;
  @JsonKey(name: 'token_type')
  String get tokenType => throw _privateConstructorUsedError;
  @JsonKey(name: 'verification_progress')
  int? get verificationProgress => throw _privateConstructorUsedError;
  @JsonKey(name: 'requires_verification')
  bool? get requiresVerification => throw _privateConstructorUsedError;
  @JsonKey(name: 'requires_2fa')
  bool? get requires2FA => throw _privateConstructorUsedError;

  /// Serializes this AuthResponse to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of AuthResponse
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $AuthResponseCopyWith<AuthResponse> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $AuthResponseCopyWith<$Res> {
  factory $AuthResponseCopyWith(
          AuthResponse value, $Res Function(AuthResponse) then) =
      _$AuthResponseCopyWithImpl<$Res, AuthResponse>;
  @useResult
  $Res call(
      {User user,
      String token,
      @JsonKey(name: 'token_type') String tokenType,
      @JsonKey(name: 'verification_progress') int? verificationProgress,
      @JsonKey(name: 'requires_verification') bool? requiresVerification,
      @JsonKey(name: 'requires_2fa') bool? requires2FA});

  $UserCopyWith<$Res> get user;
}

/// @nodoc
class _$AuthResponseCopyWithImpl<$Res, $Val extends AuthResponse>
    implements $AuthResponseCopyWith<$Res> {
  _$AuthResponseCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of AuthResponse
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? user = null,
    Object? token = null,
    Object? tokenType = null,
    Object? verificationProgress = freezed,
    Object? requiresVerification = freezed,
    Object? requires2FA = freezed,
  }) {
    return _then(_value.copyWith(
      user: null == user
          ? _value.user
          : user // ignore: cast_nullable_to_non_nullable
              as User,
      token: null == token
          ? _value.token
          : token // ignore: cast_nullable_to_non_nullable
              as String,
      tokenType: null == tokenType
          ? _value.tokenType
          : tokenType // ignore: cast_nullable_to_non_nullable
              as String,
      verificationProgress: freezed == verificationProgress
          ? _value.verificationProgress
          : verificationProgress // ignore: cast_nullable_to_non_nullable
              as int?,
      requiresVerification: freezed == requiresVerification
          ? _value.requiresVerification
          : requiresVerification // ignore: cast_nullable_to_non_nullable
              as bool?,
      requires2FA: freezed == requires2FA
          ? _value.requires2FA
          : requires2FA // ignore: cast_nullable_to_non_nullable
              as bool?,
    ) as $Val);
  }

  /// Create a copy of AuthResponse
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $UserCopyWith<$Res> get user {
    return $UserCopyWith<$Res>(_value.user, (value) {
      return _then(_value.copyWith(user: value) as $Val);
    });
  }
}

/// @nodoc
abstract class _$$AuthResponseImplCopyWith<$Res>
    implements $AuthResponseCopyWith<$Res> {
  factory _$$AuthResponseImplCopyWith(
          _$AuthResponseImpl value, $Res Function(_$AuthResponseImpl) then) =
      __$$AuthResponseImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {User user,
      String token,
      @JsonKey(name: 'token_type') String tokenType,
      @JsonKey(name: 'verification_progress') int? verificationProgress,
      @JsonKey(name: 'requires_verification') bool? requiresVerification,
      @JsonKey(name: 'requires_2fa') bool? requires2FA});

  @override
  $UserCopyWith<$Res> get user;
}

/// @nodoc
class __$$AuthResponseImplCopyWithImpl<$Res>
    extends _$AuthResponseCopyWithImpl<$Res, _$AuthResponseImpl>
    implements _$$AuthResponseImplCopyWith<$Res> {
  __$$AuthResponseImplCopyWithImpl(
      _$AuthResponseImpl _value, $Res Function(_$AuthResponseImpl) _then)
      : super(_value, _then);

  /// Create a copy of AuthResponse
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? user = null,
    Object? token = null,
    Object? tokenType = null,
    Object? verificationProgress = freezed,
    Object? requiresVerification = freezed,
    Object? requires2FA = freezed,
  }) {
    return _then(_$AuthResponseImpl(
      user: null == user
          ? _value.user
          : user // ignore: cast_nullable_to_non_nullable
              as User,
      token: null == token
          ? _value.token
          : token // ignore: cast_nullable_to_non_nullable
              as String,
      tokenType: null == tokenType
          ? _value.tokenType
          : tokenType // ignore: cast_nullable_to_non_nullable
              as String,
      verificationProgress: freezed == verificationProgress
          ? _value.verificationProgress
          : verificationProgress // ignore: cast_nullable_to_non_nullable
              as int?,
      requiresVerification: freezed == requiresVerification
          ? _value.requiresVerification
          : requiresVerification // ignore: cast_nullable_to_non_nullable
              as bool?,
      requires2FA: freezed == requires2FA
          ? _value.requires2FA
          : requires2FA // ignore: cast_nullable_to_non_nullable
              as bool?,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$AuthResponseImpl implements _AuthResponse {
  const _$AuthResponseImpl(
      {required this.user,
      required this.token,
      @JsonKey(name: 'token_type') this.tokenType = 'Bearer',
      @JsonKey(name: 'verification_progress') this.verificationProgress,
      @JsonKey(name: 'requires_verification') this.requiresVerification,
      @JsonKey(name: 'requires_2fa') this.requires2FA});

  factory _$AuthResponseImpl.fromJson(Map<String, dynamic> json) =>
      _$$AuthResponseImplFromJson(json);

  @override
  final User user;
  @override
  final String token;
  @override
  @JsonKey(name: 'token_type')
  final String tokenType;
  @override
  @JsonKey(name: 'verification_progress')
  final int? verificationProgress;
  @override
  @JsonKey(name: 'requires_verification')
  final bool? requiresVerification;
  @override
  @JsonKey(name: 'requires_2fa')
  final bool? requires2FA;

  @override
  String toString() {
    return 'AuthResponse(user: $user, token: $token, tokenType: $tokenType, verificationProgress: $verificationProgress, requiresVerification: $requiresVerification, requires2FA: $requires2FA)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$AuthResponseImpl &&
            (identical(other.user, user) || other.user == user) &&
            (identical(other.token, token) || other.token == token) &&
            (identical(other.tokenType, tokenType) ||
                other.tokenType == tokenType) &&
            (identical(other.verificationProgress, verificationProgress) ||
                other.verificationProgress == verificationProgress) &&
            (identical(other.requiresVerification, requiresVerification) ||
                other.requiresVerification == requiresVerification) &&
            (identical(other.requires2FA, requires2FA) ||
                other.requires2FA == requires2FA));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(runtimeType, user, token, tokenType,
      verificationProgress, requiresVerification, requires2FA);

  /// Create a copy of AuthResponse
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$AuthResponseImplCopyWith<_$AuthResponseImpl> get copyWith =>
      __$$AuthResponseImplCopyWithImpl<_$AuthResponseImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$AuthResponseImplToJson(
      this,
    );
  }
}

abstract class _AuthResponse implements AuthResponse {
  const factory _AuthResponse(
      {required final User user,
      required final String token,
      @JsonKey(name: 'token_type') final String tokenType,
      @JsonKey(name: 'verification_progress') final int? verificationProgress,
      @JsonKey(name: 'requires_verification') final bool? requiresVerification,
      @JsonKey(name: 'requires_2fa')
      final bool? requires2FA}) = _$AuthResponseImpl;

  factory _AuthResponse.fromJson(Map<String, dynamic> json) =
      _$AuthResponseImpl.fromJson;

  @override
  User get user;
  @override
  String get token;
  @override
  @JsonKey(name: 'token_type')
  String get tokenType;
  @override
  @JsonKey(name: 'verification_progress')
  int? get verificationProgress;
  @override
  @JsonKey(name: 'requires_verification')
  bool? get requiresVerification;
  @override
  @JsonKey(name: 'requires_2fa')
  bool? get requires2FA;

  /// Create a copy of AuthResponse
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$AuthResponseImplCopyWith<_$AuthResponseImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

VerificationStatus _$VerificationStatusFromJson(Map<String, dynamic> json) {
  return _VerificationStatus.fromJson(json);
}

/// @nodoc
mixin _$VerificationStatus {
  @JsonKey(name: 'email_verified')
  bool get emailVerified => throw _privateConstructorUsedError;
  @JsonKey(name: 'phone_verified')
  bool get phoneVerified => throw _privateConstructorUsedError;
  @JsonKey(name: 'kyc_verified')
  bool get kycVerified => throw _privateConstructorUsedError;
  @JsonKey(name: 'kyc_submitted')
  bool get kycSubmitted => throw _privateConstructorUsedError;
  @JsonKey(name: 'kyc_status')
  String get kycStatus => throw _privateConstructorUsedError;
  @JsonKey(name: 'two_factor_enabled')
  bool get twoFactorEnabled => throw _privateConstructorUsedError;
  @JsonKey(name: 'fully_verified')
  bool get fullyVerified => throw _privateConstructorUsedError;

  /// Serializes this VerificationStatus to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of VerificationStatus
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $VerificationStatusCopyWith<VerificationStatus> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $VerificationStatusCopyWith<$Res> {
  factory $VerificationStatusCopyWith(
          VerificationStatus value, $Res Function(VerificationStatus) then) =
      _$VerificationStatusCopyWithImpl<$Res, VerificationStatus>;
  @useResult
  $Res call(
      {@JsonKey(name: 'email_verified') bool emailVerified,
      @JsonKey(name: 'phone_verified') bool phoneVerified,
      @JsonKey(name: 'kyc_verified') bool kycVerified,
      @JsonKey(name: 'kyc_submitted') bool kycSubmitted,
      @JsonKey(name: 'kyc_status') String kycStatus,
      @JsonKey(name: 'two_factor_enabled') bool twoFactorEnabled,
      @JsonKey(name: 'fully_verified') bool fullyVerified});
}

/// @nodoc
class _$VerificationStatusCopyWithImpl<$Res, $Val extends VerificationStatus>
    implements $VerificationStatusCopyWith<$Res> {
  _$VerificationStatusCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of VerificationStatus
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? emailVerified = null,
    Object? phoneVerified = null,
    Object? kycVerified = null,
    Object? kycSubmitted = null,
    Object? kycStatus = null,
    Object? twoFactorEnabled = null,
    Object? fullyVerified = null,
  }) {
    return _then(_value.copyWith(
      emailVerified: null == emailVerified
          ? _value.emailVerified
          : emailVerified // ignore: cast_nullable_to_non_nullable
              as bool,
      phoneVerified: null == phoneVerified
          ? _value.phoneVerified
          : phoneVerified // ignore: cast_nullable_to_non_nullable
              as bool,
      kycVerified: null == kycVerified
          ? _value.kycVerified
          : kycVerified // ignore: cast_nullable_to_non_nullable
              as bool,
      kycSubmitted: null == kycSubmitted
          ? _value.kycSubmitted
          : kycSubmitted // ignore: cast_nullable_to_non_nullable
              as bool,
      kycStatus: null == kycStatus
          ? _value.kycStatus
          : kycStatus // ignore: cast_nullable_to_non_nullable
              as String,
      twoFactorEnabled: null == twoFactorEnabled
          ? _value.twoFactorEnabled
          : twoFactorEnabled // ignore: cast_nullable_to_non_nullable
              as bool,
      fullyVerified: null == fullyVerified
          ? _value.fullyVerified
          : fullyVerified // ignore: cast_nullable_to_non_nullable
              as bool,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$VerificationStatusImplCopyWith<$Res>
    implements $VerificationStatusCopyWith<$Res> {
  factory _$$VerificationStatusImplCopyWith(_$VerificationStatusImpl value,
          $Res Function(_$VerificationStatusImpl) then) =
      __$$VerificationStatusImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {@JsonKey(name: 'email_verified') bool emailVerified,
      @JsonKey(name: 'phone_verified') bool phoneVerified,
      @JsonKey(name: 'kyc_verified') bool kycVerified,
      @JsonKey(name: 'kyc_submitted') bool kycSubmitted,
      @JsonKey(name: 'kyc_status') String kycStatus,
      @JsonKey(name: 'two_factor_enabled') bool twoFactorEnabled,
      @JsonKey(name: 'fully_verified') bool fullyVerified});
}

/// @nodoc
class __$$VerificationStatusImplCopyWithImpl<$Res>
    extends _$VerificationStatusCopyWithImpl<$Res, _$VerificationStatusImpl>
    implements _$$VerificationStatusImplCopyWith<$Res> {
  __$$VerificationStatusImplCopyWithImpl(_$VerificationStatusImpl _value,
      $Res Function(_$VerificationStatusImpl) _then)
      : super(_value, _then);

  /// Create a copy of VerificationStatus
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? emailVerified = null,
    Object? phoneVerified = null,
    Object? kycVerified = null,
    Object? kycSubmitted = null,
    Object? kycStatus = null,
    Object? twoFactorEnabled = null,
    Object? fullyVerified = null,
  }) {
    return _then(_$VerificationStatusImpl(
      emailVerified: null == emailVerified
          ? _value.emailVerified
          : emailVerified // ignore: cast_nullable_to_non_nullable
              as bool,
      phoneVerified: null == phoneVerified
          ? _value.phoneVerified
          : phoneVerified // ignore: cast_nullable_to_non_nullable
              as bool,
      kycVerified: null == kycVerified
          ? _value.kycVerified
          : kycVerified // ignore: cast_nullable_to_non_nullable
              as bool,
      kycSubmitted: null == kycSubmitted
          ? _value.kycSubmitted
          : kycSubmitted // ignore: cast_nullable_to_non_nullable
              as bool,
      kycStatus: null == kycStatus
          ? _value.kycStatus
          : kycStatus // ignore: cast_nullable_to_non_nullable
              as String,
      twoFactorEnabled: null == twoFactorEnabled
          ? _value.twoFactorEnabled
          : twoFactorEnabled // ignore: cast_nullable_to_non_nullable
              as bool,
      fullyVerified: null == fullyVerified
          ? _value.fullyVerified
          : fullyVerified // ignore: cast_nullable_to_non_nullable
              as bool,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$VerificationStatusImpl implements _VerificationStatus {
  const _$VerificationStatusImpl(
      {@JsonKey(name: 'email_verified') required this.emailVerified,
      @JsonKey(name: 'phone_verified') required this.phoneVerified,
      @JsonKey(name: 'kyc_verified') required this.kycVerified,
      @JsonKey(name: 'kyc_submitted') required this.kycSubmitted,
      @JsonKey(name: 'kyc_status') required this.kycStatus,
      @JsonKey(name: 'two_factor_enabled') required this.twoFactorEnabled,
      @JsonKey(name: 'fully_verified') required this.fullyVerified});

  factory _$VerificationStatusImpl.fromJson(Map<String, dynamic> json) =>
      _$$VerificationStatusImplFromJson(json);

  @override
  @JsonKey(name: 'email_verified')
  final bool emailVerified;
  @override
  @JsonKey(name: 'phone_verified')
  final bool phoneVerified;
  @override
  @JsonKey(name: 'kyc_verified')
  final bool kycVerified;
  @override
  @JsonKey(name: 'kyc_submitted')
  final bool kycSubmitted;
  @override
  @JsonKey(name: 'kyc_status')
  final String kycStatus;
  @override
  @JsonKey(name: 'two_factor_enabled')
  final bool twoFactorEnabled;
  @override
  @JsonKey(name: 'fully_verified')
  final bool fullyVerified;

  @override
  String toString() {
    return 'VerificationStatus(emailVerified: $emailVerified, phoneVerified: $phoneVerified, kycVerified: $kycVerified, kycSubmitted: $kycSubmitted, kycStatus: $kycStatus, twoFactorEnabled: $twoFactorEnabled, fullyVerified: $fullyVerified)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$VerificationStatusImpl &&
            (identical(other.emailVerified, emailVerified) ||
                other.emailVerified == emailVerified) &&
            (identical(other.phoneVerified, phoneVerified) ||
                other.phoneVerified == phoneVerified) &&
            (identical(other.kycVerified, kycVerified) ||
                other.kycVerified == kycVerified) &&
            (identical(other.kycSubmitted, kycSubmitted) ||
                other.kycSubmitted == kycSubmitted) &&
            (identical(other.kycStatus, kycStatus) ||
                other.kycStatus == kycStatus) &&
            (identical(other.twoFactorEnabled, twoFactorEnabled) ||
                other.twoFactorEnabled == twoFactorEnabled) &&
            (identical(other.fullyVerified, fullyVerified) ||
                other.fullyVerified == fullyVerified));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(runtimeType, emailVerified, phoneVerified,
      kycVerified, kycSubmitted, kycStatus, twoFactorEnabled, fullyVerified);

  /// Create a copy of VerificationStatus
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$VerificationStatusImplCopyWith<_$VerificationStatusImpl> get copyWith =>
      __$$VerificationStatusImplCopyWithImpl<_$VerificationStatusImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$VerificationStatusImplToJson(
      this,
    );
  }
}

abstract class _VerificationStatus implements VerificationStatus {
  const factory _VerificationStatus(
      {@JsonKey(name: 'email_verified') required final bool emailVerified,
      @JsonKey(name: 'phone_verified') required final bool phoneVerified,
      @JsonKey(name: 'kyc_verified') required final bool kycVerified,
      @JsonKey(name: 'kyc_submitted') required final bool kycSubmitted,
      @JsonKey(name: 'kyc_status') required final String kycStatus,
      @JsonKey(name: 'two_factor_enabled') required final bool twoFactorEnabled,
      @JsonKey(name: 'fully_verified')
      required final bool fullyVerified}) = _$VerificationStatusImpl;

  factory _VerificationStatus.fromJson(Map<String, dynamic> json) =
      _$VerificationStatusImpl.fromJson;

  @override
  @JsonKey(name: 'email_verified')
  bool get emailVerified;
  @override
  @JsonKey(name: 'phone_verified')
  bool get phoneVerified;
  @override
  @JsonKey(name: 'kyc_verified')
  bool get kycVerified;
  @override
  @JsonKey(name: 'kyc_submitted')
  bool get kycSubmitted;
  @override
  @JsonKey(name: 'kyc_status')
  String get kycStatus;
  @override
  @JsonKey(name: 'two_factor_enabled')
  bool get twoFactorEnabled;
  @override
  @JsonKey(name: 'fully_verified')
  bool get fullyVerified;

  /// Create a copy of VerificationStatus
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$VerificationStatusImplCopyWith<_$VerificationStatusImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

TwoFactorSetupData _$TwoFactorSetupDataFromJson(Map<String, dynamic> json) {
  return _TwoFactorSetupData.fromJson(json);
}

/// @nodoc
mixin _$TwoFactorSetupData {
  @JsonKey(name: 'secret_key')
  String get secretKey => throw _privateConstructorUsedError;
  @JsonKey(name: 'qr_code_url')
  String get qrCodeUrl => throw _privateConstructorUsedError;
  @JsonKey(name: 'backup_codes')
  List<String> get backupCodes => throw _privateConstructorUsedError;
  @JsonKey(name: 'manual_entry_key')
  String get manualEntryKey => throw _privateConstructorUsedError;

  /// Serializes this TwoFactorSetupData to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of TwoFactorSetupData
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $TwoFactorSetupDataCopyWith<TwoFactorSetupData> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $TwoFactorSetupDataCopyWith<$Res> {
  factory $TwoFactorSetupDataCopyWith(
          TwoFactorSetupData value, $Res Function(TwoFactorSetupData) then) =
      _$TwoFactorSetupDataCopyWithImpl<$Res, TwoFactorSetupData>;
  @useResult
  $Res call(
      {@JsonKey(name: 'secret_key') String secretKey,
      @JsonKey(name: 'qr_code_url') String qrCodeUrl,
      @JsonKey(name: 'backup_codes') List<String> backupCodes,
      @JsonKey(name: 'manual_entry_key') String manualEntryKey});
}

/// @nodoc
class _$TwoFactorSetupDataCopyWithImpl<$Res, $Val extends TwoFactorSetupData>
    implements $TwoFactorSetupDataCopyWith<$Res> {
  _$TwoFactorSetupDataCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of TwoFactorSetupData
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? secretKey = null,
    Object? qrCodeUrl = null,
    Object? backupCodes = null,
    Object? manualEntryKey = null,
  }) {
    return _then(_value.copyWith(
      secretKey: null == secretKey
          ? _value.secretKey
          : secretKey // ignore: cast_nullable_to_non_nullable
              as String,
      qrCodeUrl: null == qrCodeUrl
          ? _value.qrCodeUrl
          : qrCodeUrl // ignore: cast_nullable_to_non_nullable
              as String,
      backupCodes: null == backupCodes
          ? _value.backupCodes
          : backupCodes // ignore: cast_nullable_to_non_nullable
              as List<String>,
      manualEntryKey: null == manualEntryKey
          ? _value.manualEntryKey
          : manualEntryKey // ignore: cast_nullable_to_non_nullable
              as String,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$TwoFactorSetupDataImplCopyWith<$Res>
    implements $TwoFactorSetupDataCopyWith<$Res> {
  factory _$$TwoFactorSetupDataImplCopyWith(_$TwoFactorSetupDataImpl value,
          $Res Function(_$TwoFactorSetupDataImpl) then) =
      __$$TwoFactorSetupDataImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {@JsonKey(name: 'secret_key') String secretKey,
      @JsonKey(name: 'qr_code_url') String qrCodeUrl,
      @JsonKey(name: 'backup_codes') List<String> backupCodes,
      @JsonKey(name: 'manual_entry_key') String manualEntryKey});
}

/// @nodoc
class __$$TwoFactorSetupDataImplCopyWithImpl<$Res>
    extends _$TwoFactorSetupDataCopyWithImpl<$Res, _$TwoFactorSetupDataImpl>
    implements _$$TwoFactorSetupDataImplCopyWith<$Res> {
  __$$TwoFactorSetupDataImplCopyWithImpl(_$TwoFactorSetupDataImpl _value,
      $Res Function(_$TwoFactorSetupDataImpl) _then)
      : super(_value, _then);

  /// Create a copy of TwoFactorSetupData
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? secretKey = null,
    Object? qrCodeUrl = null,
    Object? backupCodes = null,
    Object? manualEntryKey = null,
  }) {
    return _then(_$TwoFactorSetupDataImpl(
      secretKey: null == secretKey
          ? _value.secretKey
          : secretKey // ignore: cast_nullable_to_non_nullable
              as String,
      qrCodeUrl: null == qrCodeUrl
          ? _value.qrCodeUrl
          : qrCodeUrl // ignore: cast_nullable_to_non_nullable
              as String,
      backupCodes: null == backupCodes
          ? _value._backupCodes
          : backupCodes // ignore: cast_nullable_to_non_nullable
              as List<String>,
      manualEntryKey: null == manualEntryKey
          ? _value.manualEntryKey
          : manualEntryKey // ignore: cast_nullable_to_non_nullable
              as String,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$TwoFactorSetupDataImpl implements _TwoFactorSetupData {
  const _$TwoFactorSetupDataImpl(
      {@JsonKey(name: 'secret_key') required this.secretKey,
      @JsonKey(name: 'qr_code_url') required this.qrCodeUrl,
      @JsonKey(name: 'backup_codes') required final List<String> backupCodes,
      @JsonKey(name: 'manual_entry_key') required this.manualEntryKey})
      : _backupCodes = backupCodes;

  factory _$TwoFactorSetupDataImpl.fromJson(Map<String, dynamic> json) =>
      _$$TwoFactorSetupDataImplFromJson(json);

  @override
  @JsonKey(name: 'secret_key')
  final String secretKey;
  @override
  @JsonKey(name: 'qr_code_url')
  final String qrCodeUrl;
  final List<String> _backupCodes;
  @override
  @JsonKey(name: 'backup_codes')
  List<String> get backupCodes {
    if (_backupCodes is EqualUnmodifiableListView) return _backupCodes;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_backupCodes);
  }

  @override
  @JsonKey(name: 'manual_entry_key')
  final String manualEntryKey;

  @override
  String toString() {
    return 'TwoFactorSetupData(secretKey: $secretKey, qrCodeUrl: $qrCodeUrl, backupCodes: $backupCodes, manualEntryKey: $manualEntryKey)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$TwoFactorSetupDataImpl &&
            (identical(other.secretKey, secretKey) ||
                other.secretKey == secretKey) &&
            (identical(other.qrCodeUrl, qrCodeUrl) ||
                other.qrCodeUrl == qrCodeUrl) &&
            const DeepCollectionEquality()
                .equals(other._backupCodes, _backupCodes) &&
            (identical(other.manualEntryKey, manualEntryKey) ||
                other.manualEntryKey == manualEntryKey));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(runtimeType, secretKey, qrCodeUrl,
      const DeepCollectionEquality().hash(_backupCodes), manualEntryKey);

  /// Create a copy of TwoFactorSetupData
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$TwoFactorSetupDataImplCopyWith<_$TwoFactorSetupDataImpl> get copyWith =>
      __$$TwoFactorSetupDataImplCopyWithImpl<_$TwoFactorSetupDataImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$TwoFactorSetupDataImplToJson(
      this,
    );
  }
}

abstract class _TwoFactorSetupData implements TwoFactorSetupData {
  const factory _TwoFactorSetupData(
      {@JsonKey(name: 'secret_key') required final String secretKey,
      @JsonKey(name: 'qr_code_url') required final String qrCodeUrl,
      @JsonKey(name: 'backup_codes') required final List<String> backupCodes,
      @JsonKey(name: 'manual_entry_key')
      required final String manualEntryKey}) = _$TwoFactorSetupDataImpl;

  factory _TwoFactorSetupData.fromJson(Map<String, dynamic> json) =
      _$TwoFactorSetupDataImpl.fromJson;

  @override
  @JsonKey(name: 'secret_key')
  String get secretKey;
  @override
  @JsonKey(name: 'qr_code_url')
  String get qrCodeUrl;
  @override
  @JsonKey(name: 'backup_codes')
  List<String> get backupCodes;
  @override
  @JsonKey(name: 'manual_entry_key')
  String get manualEntryKey;

  /// Create a copy of TwoFactorSetupData
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$TwoFactorSetupDataImplCopyWith<_$TwoFactorSetupDataImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

ApiResponse<T> _$ApiResponseFromJson<T>(
    Map<String, dynamic> json, T Function(Object?) fromJsonT) {
  return _ApiResponse<T>.fromJson(json, fromJsonT);
}

/// @nodoc
mixin _$ApiResponse<T> {
  bool get success => throw _privateConstructorUsedError;
  String get message => throw _privateConstructorUsedError;
  T? get data => throw _privateConstructorUsedError;
  Map<String, dynamic>? get errors => throw _privateConstructorUsedError;

  /// Serializes this ApiResponse to a JSON map.
  Map<String, dynamic> toJson(Object? Function(T) toJsonT) =>
      throw _privateConstructorUsedError;

  /// Create a copy of ApiResponse
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $ApiResponseCopyWith<T, ApiResponse<T>> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $ApiResponseCopyWith<T, $Res> {
  factory $ApiResponseCopyWith(
          ApiResponse<T> value, $Res Function(ApiResponse<T>) then) =
      _$ApiResponseCopyWithImpl<T, $Res, ApiResponse<T>>;
  @useResult
  $Res call(
      {bool success, String message, T? data, Map<String, dynamic>? errors});
}

/// @nodoc
class _$ApiResponseCopyWithImpl<T, $Res, $Val extends ApiResponse<T>>
    implements $ApiResponseCopyWith<T, $Res> {
  _$ApiResponseCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of ApiResponse
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? success = null,
    Object? message = null,
    Object? data = freezed,
    Object? errors = freezed,
  }) {
    return _then(_value.copyWith(
      success: null == success
          ? _value.success
          : success // ignore: cast_nullable_to_non_nullable
              as bool,
      message: null == message
          ? _value.message
          : message // ignore: cast_nullable_to_non_nullable
              as String,
      data: freezed == data
          ? _value.data
          : data // ignore: cast_nullable_to_non_nullable
              as T?,
      errors: freezed == errors
          ? _value.errors
          : errors // ignore: cast_nullable_to_non_nullable
              as Map<String, dynamic>?,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$ApiResponseImplCopyWith<T, $Res>
    implements $ApiResponseCopyWith<T, $Res> {
  factory _$$ApiResponseImplCopyWith(_$ApiResponseImpl<T> value,
          $Res Function(_$ApiResponseImpl<T>) then) =
      __$$ApiResponseImplCopyWithImpl<T, $Res>;
  @override
  @useResult
  $Res call(
      {bool success, String message, T? data, Map<String, dynamic>? errors});
}

/// @nodoc
class __$$ApiResponseImplCopyWithImpl<T, $Res>
    extends _$ApiResponseCopyWithImpl<T, $Res, _$ApiResponseImpl<T>>
    implements _$$ApiResponseImplCopyWith<T, $Res> {
  __$$ApiResponseImplCopyWithImpl(
      _$ApiResponseImpl<T> _value, $Res Function(_$ApiResponseImpl<T>) _then)
      : super(_value, _then);

  /// Create a copy of ApiResponse
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? success = null,
    Object? message = null,
    Object? data = freezed,
    Object? errors = freezed,
  }) {
    return _then(_$ApiResponseImpl<T>(
      success: null == success
          ? _value.success
          : success // ignore: cast_nullable_to_non_nullable
              as bool,
      message: null == message
          ? _value.message
          : message // ignore: cast_nullable_to_non_nullable
              as String,
      data: freezed == data
          ? _value.data
          : data // ignore: cast_nullable_to_non_nullable
              as T?,
      errors: freezed == errors
          ? _value._errors
          : errors // ignore: cast_nullable_to_non_nullable
              as Map<String, dynamic>?,
    ));
  }
}

/// @nodoc
@JsonSerializable(genericArgumentFactories: true)
class _$ApiResponseImpl<T> implements _ApiResponse<T> {
  const _$ApiResponseImpl(
      {required this.success,
      required this.message,
      this.data,
      final Map<String, dynamic>? errors})
      : _errors = errors;

  factory _$ApiResponseImpl.fromJson(
          Map<String, dynamic> json, T Function(Object?) fromJsonT) =>
      _$$ApiResponseImplFromJson(json, fromJsonT);

  @override
  final bool success;
  @override
  final String message;
  @override
  final T? data;
  final Map<String, dynamic>? _errors;
  @override
  Map<String, dynamic>? get errors {
    final value = _errors;
    if (value == null) return null;
    if (_errors is EqualUnmodifiableMapView) return _errors;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableMapView(value);
  }

  @override
  String toString() {
    return 'ApiResponse<$T>(success: $success, message: $message, data: $data, errors: $errors)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$ApiResponseImpl<T> &&
            (identical(other.success, success) || other.success == success) &&
            (identical(other.message, message) || other.message == message) &&
            const DeepCollectionEquality().equals(other.data, data) &&
            const DeepCollectionEquality().equals(other._errors, _errors));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      success,
      message,
      const DeepCollectionEquality().hash(data),
      const DeepCollectionEquality().hash(_errors));

  /// Create a copy of ApiResponse
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$ApiResponseImplCopyWith<T, _$ApiResponseImpl<T>> get copyWith =>
      __$$ApiResponseImplCopyWithImpl<T, _$ApiResponseImpl<T>>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson(Object? Function(T) toJsonT) {
    return _$$ApiResponseImplToJson<T>(this, toJsonT);
  }
}

abstract class _ApiResponse<T> implements ApiResponse<T> {
  const factory _ApiResponse(
      {required final bool success,
      required final String message,
      final T? data,
      final Map<String, dynamic>? errors}) = _$ApiResponseImpl<T>;

  factory _ApiResponse.fromJson(
          Map<String, dynamic> json, T Function(Object?) fromJsonT) =
      _$ApiResponseImpl<T>.fromJson;

  @override
  bool get success;
  @override
  String get message;
  @override
  T? get data;
  @override
  Map<String, dynamic>? get errors;

  /// Create a copy of ApiResponse
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$ApiResponseImplCopyWith<T, _$ApiResponseImpl<T>> get copyWith =>
      throw _privateConstructorUsedError;
}

AuthState _$AuthStateFromJson(Map<String, dynamic> json) {
  return _AuthState.fromJson(json);
}

/// @nodoc
mixin _$AuthState {
  User? get user => throw _privateConstructorUsedError;
  String? get token => throw _privateConstructorUsedError;
  bool get isAuthenticated => throw _privateConstructorUsedError;
  bool get isLoading => throw _privateConstructorUsedError;
  String? get error => throw _privateConstructorUsedError;
  VerificationStatus? get verificationStatus =>
      throw _privateConstructorUsedError;
  int get verificationProgress => throw _privateConstructorUsedError;

  /// Serializes this AuthState to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of AuthState
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $AuthStateCopyWith<AuthState> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $AuthStateCopyWith<$Res> {
  factory $AuthStateCopyWith(AuthState value, $Res Function(AuthState) then) =
      _$AuthStateCopyWithImpl<$Res, AuthState>;
  @useResult
  $Res call(
      {User? user,
      String? token,
      bool isAuthenticated,
      bool isLoading,
      String? error,
      VerificationStatus? verificationStatus,
      int verificationProgress});

  $UserCopyWith<$Res>? get user;
  $VerificationStatusCopyWith<$Res>? get verificationStatus;
}

/// @nodoc
class _$AuthStateCopyWithImpl<$Res, $Val extends AuthState>
    implements $AuthStateCopyWith<$Res> {
  _$AuthStateCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of AuthState
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? user = freezed,
    Object? token = freezed,
    Object? isAuthenticated = null,
    Object? isLoading = null,
    Object? error = freezed,
    Object? verificationStatus = freezed,
    Object? verificationProgress = null,
  }) {
    return _then(_value.copyWith(
      user: freezed == user
          ? _value.user
          : user // ignore: cast_nullable_to_non_nullable
              as User?,
      token: freezed == token
          ? _value.token
          : token // ignore: cast_nullable_to_non_nullable
              as String?,
      isAuthenticated: null == isAuthenticated
          ? _value.isAuthenticated
          : isAuthenticated // ignore: cast_nullable_to_non_nullable
              as bool,
      isLoading: null == isLoading
          ? _value.isLoading
          : isLoading // ignore: cast_nullable_to_non_nullable
              as bool,
      error: freezed == error
          ? _value.error
          : error // ignore: cast_nullable_to_non_nullable
              as String?,
      verificationStatus: freezed == verificationStatus
          ? _value.verificationStatus
          : verificationStatus // ignore: cast_nullable_to_non_nullable
              as VerificationStatus?,
      verificationProgress: null == verificationProgress
          ? _value.verificationProgress
          : verificationProgress // ignore: cast_nullable_to_non_nullable
              as int,
    ) as $Val);
  }

  /// Create a copy of AuthState
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $UserCopyWith<$Res>? get user {
    if (_value.user == null) {
      return null;
    }

    return $UserCopyWith<$Res>(_value.user!, (value) {
      return _then(_value.copyWith(user: value) as $Val);
    });
  }

  /// Create a copy of AuthState
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $VerificationStatusCopyWith<$Res>? get verificationStatus {
    if (_value.verificationStatus == null) {
      return null;
    }

    return $VerificationStatusCopyWith<$Res>(_value.verificationStatus!,
        (value) {
      return _then(_value.copyWith(verificationStatus: value) as $Val);
    });
  }
}

/// @nodoc
abstract class _$$AuthStateImplCopyWith<$Res>
    implements $AuthStateCopyWith<$Res> {
  factory _$$AuthStateImplCopyWith(
          _$AuthStateImpl value, $Res Function(_$AuthStateImpl) then) =
      __$$AuthStateImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {User? user,
      String? token,
      bool isAuthenticated,
      bool isLoading,
      String? error,
      VerificationStatus? verificationStatus,
      int verificationProgress});

  @override
  $UserCopyWith<$Res>? get user;
  @override
  $VerificationStatusCopyWith<$Res>? get verificationStatus;
}

/// @nodoc
class __$$AuthStateImplCopyWithImpl<$Res>
    extends _$AuthStateCopyWithImpl<$Res, _$AuthStateImpl>
    implements _$$AuthStateImplCopyWith<$Res> {
  __$$AuthStateImplCopyWithImpl(
      _$AuthStateImpl _value, $Res Function(_$AuthStateImpl) _then)
      : super(_value, _then);

  /// Create a copy of AuthState
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? user = freezed,
    Object? token = freezed,
    Object? isAuthenticated = null,
    Object? isLoading = null,
    Object? error = freezed,
    Object? verificationStatus = freezed,
    Object? verificationProgress = null,
  }) {
    return _then(_$AuthStateImpl(
      user: freezed == user
          ? _value.user
          : user // ignore: cast_nullable_to_non_nullable
              as User?,
      token: freezed == token
          ? _value.token
          : token // ignore: cast_nullable_to_non_nullable
              as String?,
      isAuthenticated: null == isAuthenticated
          ? _value.isAuthenticated
          : isAuthenticated // ignore: cast_nullable_to_non_nullable
              as bool,
      isLoading: null == isLoading
          ? _value.isLoading
          : isLoading // ignore: cast_nullable_to_non_nullable
              as bool,
      error: freezed == error
          ? _value.error
          : error // ignore: cast_nullable_to_non_nullable
              as String?,
      verificationStatus: freezed == verificationStatus
          ? _value.verificationStatus
          : verificationStatus // ignore: cast_nullable_to_non_nullable
              as VerificationStatus?,
      verificationProgress: null == verificationProgress
          ? _value.verificationProgress
          : verificationProgress // ignore: cast_nullable_to_non_nullable
              as int,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$AuthStateImpl implements _AuthState {
  const _$AuthStateImpl(
      {this.user,
      this.token,
      this.isAuthenticated = false,
      this.isLoading = false,
      this.error,
      this.verificationStatus,
      this.verificationProgress = 0});

  factory _$AuthStateImpl.fromJson(Map<String, dynamic> json) =>
      _$$AuthStateImplFromJson(json);

  @override
  final User? user;
  @override
  final String? token;
  @override
  @JsonKey()
  final bool isAuthenticated;
  @override
  @JsonKey()
  final bool isLoading;
  @override
  final String? error;
  @override
  final VerificationStatus? verificationStatus;
  @override
  @JsonKey()
  final int verificationProgress;

  @override
  String toString() {
    return 'AuthState(user: $user, token: $token, isAuthenticated: $isAuthenticated, isLoading: $isLoading, error: $error, verificationStatus: $verificationStatus, verificationProgress: $verificationProgress)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$AuthStateImpl &&
            (identical(other.user, user) || other.user == user) &&
            (identical(other.token, token) || other.token == token) &&
            (identical(other.isAuthenticated, isAuthenticated) ||
                other.isAuthenticated == isAuthenticated) &&
            (identical(other.isLoading, isLoading) ||
                other.isLoading == isLoading) &&
            (identical(other.error, error) || other.error == error) &&
            (identical(other.verificationStatus, verificationStatus) ||
                other.verificationStatus == verificationStatus) &&
            (identical(other.verificationProgress, verificationProgress) ||
                other.verificationProgress == verificationProgress));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(runtimeType, user, token, isAuthenticated,
      isLoading, error, verificationStatus, verificationProgress);

  /// Create a copy of AuthState
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$AuthStateImplCopyWith<_$AuthStateImpl> get copyWith =>
      __$$AuthStateImplCopyWithImpl<_$AuthStateImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$AuthStateImplToJson(
      this,
    );
  }
}

abstract class _AuthState implements AuthState {
  const factory _AuthState(
      {final User? user,
      final String? token,
      final bool isAuthenticated,
      final bool isLoading,
      final String? error,
      final VerificationStatus? verificationStatus,
      final int verificationProgress}) = _$AuthStateImpl;

  factory _AuthState.fromJson(Map<String, dynamic> json) =
      _$AuthStateImpl.fromJson;

  @override
  User? get user;
  @override
  String? get token;
  @override
  bool get isAuthenticated;
  @override
  bool get isLoading;
  @override
  String? get error;
  @override
  VerificationStatus? get verificationStatus;
  @override
  int get verificationProgress;

  /// Create a copy of AuthState
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$AuthStateImplCopyWith<_$AuthStateImpl> get copyWith =>
      throw _privateConstructorUsedError;
}
