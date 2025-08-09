// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'marketplace_models.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
    'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models');

Business _$BusinessFromJson(Map<String, dynamic> json) {
  return _Business.fromJson(json);
}

/// @nodoc
mixin _$Business {
  String get id => throw _privateConstructorUsedError;
  @JsonKey(name: 'seller_id')
  String get sellerId => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get description => throw _privateConstructorUsedError;
  String get industry => throw _privateConstructorUsedError;
  double get valuation => throw _privateConstructorUsedError;
  @JsonKey(name: 'funding_goal')
  double get fundingGoal => throw _privateConstructorUsedError;
  @JsonKey(name: 'equity_offered')
  double get equityOffered => throw _privateConstructorUsedError;
  @JsonKey(name: 'pitch_deck_url')
  String? get pitchDeckUrl => throw _privateConstructorUsedError;
  @JsonKey(name: 'business_plan')
  String? get businessPlan => throw _privateConstructorUsedError;
  double get revenue => throw _privateConstructorUsedError;
  @JsonKey(name: 'profit_margin')
  double get profitMargin => throw _privateConstructorUsedError;
  @JsonKey(name: 'employees_count')
  int get employeesCount => throw _privateConstructorUsedError;
  @JsonKey(name: 'founded_year')
  int get foundedYear => throw _privateConstructorUsedError;
  String get location => throw _privateConstructorUsedError;
  List<String> get images => throw _privateConstructorUsedError;
  String get status => throw _privateConstructorUsedError;
  @JsonKey(name: 'views_count')
  int get viewsCount => throw _privateConstructorUsedError;
  @JsonKey(name: 'created_at')
  DateTime get createdAt => throw _privateConstructorUsedError;
  @JsonKey(name: 'updated_at')
  DateTime get updatedAt => throw _privateConstructorUsedError;
  BusinessSeller? get seller => throw _privateConstructorUsedError;
  List<Investment> get investments => throw _privateConstructorUsedError;

  /// Serializes this Business to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of Business
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $BusinessCopyWith<Business> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $BusinessCopyWith<$Res> {
  factory $BusinessCopyWith(Business value, $Res Function(Business) then) =
      _$BusinessCopyWithImpl<$Res, Business>;
  @useResult
  $Res call(
      {String id,
      @JsonKey(name: 'seller_id') String sellerId,
      String name,
      String description,
      String industry,
      double valuation,
      @JsonKey(name: 'funding_goal') double fundingGoal,
      @JsonKey(name: 'equity_offered') double equityOffered,
      @JsonKey(name: 'pitch_deck_url') String? pitchDeckUrl,
      @JsonKey(name: 'business_plan') String? businessPlan,
      double revenue,
      @JsonKey(name: 'profit_margin') double profitMargin,
      @JsonKey(name: 'employees_count') int employeesCount,
      @JsonKey(name: 'founded_year') int foundedYear,
      String location,
      List<String> images,
      String status,
      @JsonKey(name: 'views_count') int viewsCount,
      @JsonKey(name: 'created_at') DateTime createdAt,
      @JsonKey(name: 'updated_at') DateTime updatedAt,
      BusinessSeller? seller,
      List<Investment> investments});

  $BusinessSellerCopyWith<$Res>? get seller;
}

/// @nodoc
class _$BusinessCopyWithImpl<$Res, $Val extends Business>
    implements $BusinessCopyWith<$Res> {
  _$BusinessCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of Business
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? sellerId = null,
    Object? name = null,
    Object? description = null,
    Object? industry = null,
    Object? valuation = null,
    Object? fundingGoal = null,
    Object? equityOffered = null,
    Object? pitchDeckUrl = freezed,
    Object? businessPlan = freezed,
    Object? revenue = null,
    Object? profitMargin = null,
    Object? employeesCount = null,
    Object? foundedYear = null,
    Object? location = null,
    Object? images = null,
    Object? status = null,
    Object? viewsCount = null,
    Object? createdAt = null,
    Object? updatedAt = null,
    Object? seller = freezed,
    Object? investments = null,
  }) {
    return _then(_value.copyWith(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as String,
      sellerId: null == sellerId
          ? _value.sellerId
          : sellerId // ignore: cast_nullable_to_non_nullable
              as String,
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      description: null == description
          ? _value.description
          : description // ignore: cast_nullable_to_non_nullable
              as String,
      industry: null == industry
          ? _value.industry
          : industry // ignore: cast_nullable_to_non_nullable
              as String,
      valuation: null == valuation
          ? _value.valuation
          : valuation // ignore: cast_nullable_to_non_nullable
              as double,
      fundingGoal: null == fundingGoal
          ? _value.fundingGoal
          : fundingGoal // ignore: cast_nullable_to_non_nullable
              as double,
      equityOffered: null == equityOffered
          ? _value.equityOffered
          : equityOffered // ignore: cast_nullable_to_non_nullable
              as double,
      pitchDeckUrl: freezed == pitchDeckUrl
          ? _value.pitchDeckUrl
          : pitchDeckUrl // ignore: cast_nullable_to_non_nullable
              as String?,
      businessPlan: freezed == businessPlan
          ? _value.businessPlan
          : businessPlan // ignore: cast_nullable_to_non_nullable
              as String?,
      revenue: null == revenue
          ? _value.revenue
          : revenue // ignore: cast_nullable_to_non_nullable
              as double,
      profitMargin: null == profitMargin
          ? _value.profitMargin
          : profitMargin // ignore: cast_nullable_to_non_nullable
              as double,
      employeesCount: null == employeesCount
          ? _value.employeesCount
          : employeesCount // ignore: cast_nullable_to_non_nullable
              as int,
      foundedYear: null == foundedYear
          ? _value.foundedYear
          : foundedYear // ignore: cast_nullable_to_non_nullable
              as int,
      location: null == location
          ? _value.location
          : location // ignore: cast_nullable_to_non_nullable
              as String,
      images: null == images
          ? _value.images
          : images // ignore: cast_nullable_to_non_nullable
              as List<String>,
      status: null == status
          ? _value.status
          : status // ignore: cast_nullable_to_non_nullable
              as String,
      viewsCount: null == viewsCount
          ? _value.viewsCount
          : viewsCount // ignore: cast_nullable_to_non_nullable
              as int,
      createdAt: null == createdAt
          ? _value.createdAt
          : createdAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
      updatedAt: null == updatedAt
          ? _value.updatedAt
          : updatedAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
      seller: freezed == seller
          ? _value.seller
          : seller // ignore: cast_nullable_to_non_nullable
              as BusinessSeller?,
      investments: null == investments
          ? _value.investments
          : investments // ignore: cast_nullable_to_non_nullable
              as List<Investment>,
    ) as $Val);
  }

  /// Create a copy of Business
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $BusinessSellerCopyWith<$Res>? get seller {
    if (_value.seller == null) {
      return null;
    }

    return $BusinessSellerCopyWith<$Res>(_value.seller!, (value) {
      return _then(_value.copyWith(seller: value) as $Val);
    });
  }
}

/// @nodoc
abstract class _$$BusinessImplCopyWith<$Res>
    implements $BusinessCopyWith<$Res> {
  factory _$$BusinessImplCopyWith(
          _$BusinessImpl value, $Res Function(_$BusinessImpl) then) =
      __$$BusinessImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {String id,
      @JsonKey(name: 'seller_id') String sellerId,
      String name,
      String description,
      String industry,
      double valuation,
      @JsonKey(name: 'funding_goal') double fundingGoal,
      @JsonKey(name: 'equity_offered') double equityOffered,
      @JsonKey(name: 'pitch_deck_url') String? pitchDeckUrl,
      @JsonKey(name: 'business_plan') String? businessPlan,
      double revenue,
      @JsonKey(name: 'profit_margin') double profitMargin,
      @JsonKey(name: 'employees_count') int employeesCount,
      @JsonKey(name: 'founded_year') int foundedYear,
      String location,
      List<String> images,
      String status,
      @JsonKey(name: 'views_count') int viewsCount,
      @JsonKey(name: 'created_at') DateTime createdAt,
      @JsonKey(name: 'updated_at') DateTime updatedAt,
      BusinessSeller? seller,
      List<Investment> investments});

  @override
  $BusinessSellerCopyWith<$Res>? get seller;
}

/// @nodoc
class __$$BusinessImplCopyWithImpl<$Res>
    extends _$BusinessCopyWithImpl<$Res, _$BusinessImpl>
    implements _$$BusinessImplCopyWith<$Res> {
  __$$BusinessImplCopyWithImpl(
      _$BusinessImpl _value, $Res Function(_$BusinessImpl) _then)
      : super(_value, _then);

  /// Create a copy of Business
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? sellerId = null,
    Object? name = null,
    Object? description = null,
    Object? industry = null,
    Object? valuation = null,
    Object? fundingGoal = null,
    Object? equityOffered = null,
    Object? pitchDeckUrl = freezed,
    Object? businessPlan = freezed,
    Object? revenue = null,
    Object? profitMargin = null,
    Object? employeesCount = null,
    Object? foundedYear = null,
    Object? location = null,
    Object? images = null,
    Object? status = null,
    Object? viewsCount = null,
    Object? createdAt = null,
    Object? updatedAt = null,
    Object? seller = freezed,
    Object? investments = null,
  }) {
    return _then(_$BusinessImpl(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as String,
      sellerId: null == sellerId
          ? _value.sellerId
          : sellerId // ignore: cast_nullable_to_non_nullable
              as String,
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      description: null == description
          ? _value.description
          : description // ignore: cast_nullable_to_non_nullable
              as String,
      industry: null == industry
          ? _value.industry
          : industry // ignore: cast_nullable_to_non_nullable
              as String,
      valuation: null == valuation
          ? _value.valuation
          : valuation // ignore: cast_nullable_to_non_nullable
              as double,
      fundingGoal: null == fundingGoal
          ? _value.fundingGoal
          : fundingGoal // ignore: cast_nullable_to_non_nullable
              as double,
      equityOffered: null == equityOffered
          ? _value.equityOffered
          : equityOffered // ignore: cast_nullable_to_non_nullable
              as double,
      pitchDeckUrl: freezed == pitchDeckUrl
          ? _value.pitchDeckUrl
          : pitchDeckUrl // ignore: cast_nullable_to_non_nullable
              as String?,
      businessPlan: freezed == businessPlan
          ? _value.businessPlan
          : businessPlan // ignore: cast_nullable_to_non_nullable
              as String?,
      revenue: null == revenue
          ? _value.revenue
          : revenue // ignore: cast_nullable_to_non_nullable
              as double,
      profitMargin: null == profitMargin
          ? _value.profitMargin
          : profitMargin // ignore: cast_nullable_to_non_nullable
              as double,
      employeesCount: null == employeesCount
          ? _value.employeesCount
          : employeesCount // ignore: cast_nullable_to_non_nullable
              as int,
      foundedYear: null == foundedYear
          ? _value.foundedYear
          : foundedYear // ignore: cast_nullable_to_non_nullable
              as int,
      location: null == location
          ? _value.location
          : location // ignore: cast_nullable_to_non_nullable
              as String,
      images: null == images
          ? _value._images
          : images // ignore: cast_nullable_to_non_nullable
              as List<String>,
      status: null == status
          ? _value.status
          : status // ignore: cast_nullable_to_non_nullable
              as String,
      viewsCount: null == viewsCount
          ? _value.viewsCount
          : viewsCount // ignore: cast_nullable_to_non_nullable
              as int,
      createdAt: null == createdAt
          ? _value.createdAt
          : createdAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
      updatedAt: null == updatedAt
          ? _value.updatedAt
          : updatedAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
      seller: freezed == seller
          ? _value.seller
          : seller // ignore: cast_nullable_to_non_nullable
              as BusinessSeller?,
      investments: null == investments
          ? _value._investments
          : investments // ignore: cast_nullable_to_non_nullable
              as List<Investment>,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$BusinessImpl implements _Business {
  const _$BusinessImpl(
      {required this.id,
      @JsonKey(name: 'seller_id') required this.sellerId,
      required this.name,
      required this.description,
      required this.industry,
      required this.valuation,
      @JsonKey(name: 'funding_goal') required this.fundingGoal,
      @JsonKey(name: 'equity_offered') required this.equityOffered,
      @JsonKey(name: 'pitch_deck_url') this.pitchDeckUrl,
      @JsonKey(name: 'business_plan') this.businessPlan,
      required this.revenue,
      @JsonKey(name: 'profit_margin') required this.profitMargin,
      @JsonKey(name: 'employees_count') required this.employeesCount,
      @JsonKey(name: 'founded_year') required this.foundedYear,
      required this.location,
      final List<String> images = const [],
      this.status = 'active',
      @JsonKey(name: 'views_count') this.viewsCount = 0,
      @JsonKey(name: 'created_at') required this.createdAt,
      @JsonKey(name: 'updated_at') required this.updatedAt,
      this.seller,
      final List<Investment> investments = const []})
      : _images = images,
        _investments = investments;

  factory _$BusinessImpl.fromJson(Map<String, dynamic> json) =>
      _$$BusinessImplFromJson(json);

  @override
  final String id;
  @override
  @JsonKey(name: 'seller_id')
  final String sellerId;
  @override
  final String name;
  @override
  final String description;
  @override
  final String industry;
  @override
  final double valuation;
  @override
  @JsonKey(name: 'funding_goal')
  final double fundingGoal;
  @override
  @JsonKey(name: 'equity_offered')
  final double equityOffered;
  @override
  @JsonKey(name: 'pitch_deck_url')
  final String? pitchDeckUrl;
  @override
  @JsonKey(name: 'business_plan')
  final String? businessPlan;
  @override
  final double revenue;
  @override
  @JsonKey(name: 'profit_margin')
  final double profitMargin;
  @override
  @JsonKey(name: 'employees_count')
  final int employeesCount;
  @override
  @JsonKey(name: 'founded_year')
  final int foundedYear;
  @override
  final String location;
  final List<String> _images;
  @override
  @JsonKey()
  List<String> get images {
    if (_images is EqualUnmodifiableListView) return _images;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_images);
  }

  @override
  @JsonKey()
  final String status;
  @override
  @JsonKey(name: 'views_count')
  final int viewsCount;
  @override
  @JsonKey(name: 'created_at')
  final DateTime createdAt;
  @override
  @JsonKey(name: 'updated_at')
  final DateTime updatedAt;
  @override
  final BusinessSeller? seller;
  final List<Investment> _investments;
  @override
  @JsonKey()
  List<Investment> get investments {
    if (_investments is EqualUnmodifiableListView) return _investments;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_investments);
  }

  @override
  String toString() {
    return 'Business(id: $id, sellerId: $sellerId, name: $name, description: $description, industry: $industry, valuation: $valuation, fundingGoal: $fundingGoal, equityOffered: $equityOffered, pitchDeckUrl: $pitchDeckUrl, businessPlan: $businessPlan, revenue: $revenue, profitMargin: $profitMargin, employeesCount: $employeesCount, foundedYear: $foundedYear, location: $location, images: $images, status: $status, viewsCount: $viewsCount, createdAt: $createdAt, updatedAt: $updatedAt, seller: $seller, investments: $investments)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$BusinessImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.sellerId, sellerId) ||
                other.sellerId == sellerId) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.industry, industry) ||
                other.industry == industry) &&
            (identical(other.valuation, valuation) ||
                other.valuation == valuation) &&
            (identical(other.fundingGoal, fundingGoal) ||
                other.fundingGoal == fundingGoal) &&
            (identical(other.equityOffered, equityOffered) ||
                other.equityOffered == equityOffered) &&
            (identical(other.pitchDeckUrl, pitchDeckUrl) ||
                other.pitchDeckUrl == pitchDeckUrl) &&
            (identical(other.businessPlan, businessPlan) ||
                other.businessPlan == businessPlan) &&
            (identical(other.revenue, revenue) || other.revenue == revenue) &&
            (identical(other.profitMargin, profitMargin) ||
                other.profitMargin == profitMargin) &&
            (identical(other.employeesCount, employeesCount) ||
                other.employeesCount == employeesCount) &&
            (identical(other.foundedYear, foundedYear) ||
                other.foundedYear == foundedYear) &&
            (identical(other.location, location) ||
                other.location == location) &&
            const DeepCollectionEquality().equals(other._images, _images) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.viewsCount, viewsCount) ||
                other.viewsCount == viewsCount) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt) &&
            (identical(other.updatedAt, updatedAt) ||
                other.updatedAt == updatedAt) &&
            (identical(other.seller, seller) || other.seller == seller) &&
            const DeepCollectionEquality()
                .equals(other._investments, _investments));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hashAll([
        runtimeType,
        id,
        sellerId,
        name,
        description,
        industry,
        valuation,
        fundingGoal,
        equityOffered,
        pitchDeckUrl,
        businessPlan,
        revenue,
        profitMargin,
        employeesCount,
        foundedYear,
        location,
        const DeepCollectionEquality().hash(_images),
        status,
        viewsCount,
        createdAt,
        updatedAt,
        seller,
        const DeepCollectionEquality().hash(_investments)
      ]);

  /// Create a copy of Business
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$BusinessImplCopyWith<_$BusinessImpl> get copyWith =>
      __$$BusinessImplCopyWithImpl<_$BusinessImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$BusinessImplToJson(
      this,
    );
  }
}

abstract class _Business implements Business {
  const factory _Business(
      {required final String id,
      @JsonKey(name: 'seller_id') required final String sellerId,
      required final String name,
      required final String description,
      required final String industry,
      required final double valuation,
      @JsonKey(name: 'funding_goal') required final double fundingGoal,
      @JsonKey(name: 'equity_offered') required final double equityOffered,
      @JsonKey(name: 'pitch_deck_url') final String? pitchDeckUrl,
      @JsonKey(name: 'business_plan') final String? businessPlan,
      required final double revenue,
      @JsonKey(name: 'profit_margin') required final double profitMargin,
      @JsonKey(name: 'employees_count') required final int employeesCount,
      @JsonKey(name: 'founded_year') required final int foundedYear,
      required final String location,
      final List<String> images,
      final String status,
      @JsonKey(name: 'views_count') final int viewsCount,
      @JsonKey(name: 'created_at') required final DateTime createdAt,
      @JsonKey(name: 'updated_at') required final DateTime updatedAt,
      final BusinessSeller? seller,
      final List<Investment> investments}) = _$BusinessImpl;

  factory _Business.fromJson(Map<String, dynamic> json) =
      _$BusinessImpl.fromJson;

  @override
  String get id;
  @override
  @JsonKey(name: 'seller_id')
  String get sellerId;
  @override
  String get name;
  @override
  String get description;
  @override
  String get industry;
  @override
  double get valuation;
  @override
  @JsonKey(name: 'funding_goal')
  double get fundingGoal;
  @override
  @JsonKey(name: 'equity_offered')
  double get equityOffered;
  @override
  @JsonKey(name: 'pitch_deck_url')
  String? get pitchDeckUrl;
  @override
  @JsonKey(name: 'business_plan')
  String? get businessPlan;
  @override
  double get revenue;
  @override
  @JsonKey(name: 'profit_margin')
  double get profitMargin;
  @override
  @JsonKey(name: 'employees_count')
  int get employeesCount;
  @override
  @JsonKey(name: 'founded_year')
  int get foundedYear;
  @override
  String get location;
  @override
  List<String> get images;
  @override
  String get status;
  @override
  @JsonKey(name: 'views_count')
  int get viewsCount;
  @override
  @JsonKey(name: 'created_at')
  DateTime get createdAt;
  @override
  @JsonKey(name: 'updated_at')
  DateTime get updatedAt;
  @override
  BusinessSeller? get seller;
  @override
  List<Investment> get investments;

  /// Create a copy of Business
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$BusinessImplCopyWith<_$BusinessImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

BusinessSeller _$BusinessSellerFromJson(Map<String, dynamic> json) {
  return _BusinessSeller.fromJson(json);
}

/// @nodoc
mixin _$BusinessSeller {
  String get id => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  @JsonKey(name: 'first_name')
  String? get firstName => throw _privateConstructorUsedError;
  @JsonKey(name: 'last_name')
  String? get lastName => throw _privateConstructorUsedError;
  String get email => throw _privateConstructorUsedError;
  @JsonKey(name: 'avatar_url')
  String? get avatarUrl => throw _privateConstructorUsedError;
  @JsonKey(name: 'trust_score')
  double get trustScore => throw _privateConstructorUsedError;
  @JsonKey(name: 'is_verified')
  bool get isVerified => throw _privateConstructorUsedError;

  /// Serializes this BusinessSeller to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of BusinessSeller
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $BusinessSellerCopyWith<BusinessSeller> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $BusinessSellerCopyWith<$Res> {
  factory $BusinessSellerCopyWith(
          BusinessSeller value, $Res Function(BusinessSeller) then) =
      _$BusinessSellerCopyWithImpl<$Res, BusinessSeller>;
  @useResult
  $Res call(
      {String id,
      String name,
      @JsonKey(name: 'first_name') String? firstName,
      @JsonKey(name: 'last_name') String? lastName,
      String email,
      @JsonKey(name: 'avatar_url') String? avatarUrl,
      @JsonKey(name: 'trust_score') double trustScore,
      @JsonKey(name: 'is_verified') bool isVerified});
}

/// @nodoc
class _$BusinessSellerCopyWithImpl<$Res, $Val extends BusinessSeller>
    implements $BusinessSellerCopyWith<$Res> {
  _$BusinessSellerCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of BusinessSeller
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? firstName = freezed,
    Object? lastName = freezed,
    Object? email = null,
    Object? avatarUrl = freezed,
    Object? trustScore = null,
    Object? isVerified = null,
  }) {
    return _then(_value.copyWith(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as String,
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      firstName: freezed == firstName
          ? _value.firstName
          : firstName // ignore: cast_nullable_to_non_nullable
              as String?,
      lastName: freezed == lastName
          ? _value.lastName
          : lastName // ignore: cast_nullable_to_non_nullable
              as String?,
      email: null == email
          ? _value.email
          : email // ignore: cast_nullable_to_non_nullable
              as String,
      avatarUrl: freezed == avatarUrl
          ? _value.avatarUrl
          : avatarUrl // ignore: cast_nullable_to_non_nullable
              as String?,
      trustScore: null == trustScore
          ? _value.trustScore
          : trustScore // ignore: cast_nullable_to_non_nullable
              as double,
      isVerified: null == isVerified
          ? _value.isVerified
          : isVerified // ignore: cast_nullable_to_non_nullable
              as bool,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$BusinessSellerImplCopyWith<$Res>
    implements $BusinessSellerCopyWith<$Res> {
  factory _$$BusinessSellerImplCopyWith(_$BusinessSellerImpl value,
          $Res Function(_$BusinessSellerImpl) then) =
      __$$BusinessSellerImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {String id,
      String name,
      @JsonKey(name: 'first_name') String? firstName,
      @JsonKey(name: 'last_name') String? lastName,
      String email,
      @JsonKey(name: 'avatar_url') String? avatarUrl,
      @JsonKey(name: 'trust_score') double trustScore,
      @JsonKey(name: 'is_verified') bool isVerified});
}

/// @nodoc
class __$$BusinessSellerImplCopyWithImpl<$Res>
    extends _$BusinessSellerCopyWithImpl<$Res, _$BusinessSellerImpl>
    implements _$$BusinessSellerImplCopyWith<$Res> {
  __$$BusinessSellerImplCopyWithImpl(
      _$BusinessSellerImpl _value, $Res Function(_$BusinessSellerImpl) _then)
      : super(_value, _then);

  /// Create a copy of BusinessSeller
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? firstName = freezed,
    Object? lastName = freezed,
    Object? email = null,
    Object? avatarUrl = freezed,
    Object? trustScore = null,
    Object? isVerified = null,
  }) {
    return _then(_$BusinessSellerImpl(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as String,
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      firstName: freezed == firstName
          ? _value.firstName
          : firstName // ignore: cast_nullable_to_non_nullable
              as String?,
      lastName: freezed == lastName
          ? _value.lastName
          : lastName // ignore: cast_nullable_to_non_nullable
              as String?,
      email: null == email
          ? _value.email
          : email // ignore: cast_nullable_to_non_nullable
              as String,
      avatarUrl: freezed == avatarUrl
          ? _value.avatarUrl
          : avatarUrl // ignore: cast_nullable_to_non_nullable
              as String?,
      trustScore: null == trustScore
          ? _value.trustScore
          : trustScore // ignore: cast_nullable_to_non_nullable
              as double,
      isVerified: null == isVerified
          ? _value.isVerified
          : isVerified // ignore: cast_nullable_to_non_nullable
              as bool,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$BusinessSellerImpl implements _BusinessSeller {
  const _$BusinessSellerImpl(
      {required this.id,
      required this.name,
      @JsonKey(name: 'first_name') this.firstName,
      @JsonKey(name: 'last_name') this.lastName,
      required this.email,
      @JsonKey(name: 'avatar_url') this.avatarUrl,
      @JsonKey(name: 'trust_score') this.trustScore = 0.0,
      @JsonKey(name: 'is_verified') this.isVerified = false});

  factory _$BusinessSellerImpl.fromJson(Map<String, dynamic> json) =>
      _$$BusinessSellerImplFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  @JsonKey(name: 'first_name')
  final String? firstName;
  @override
  @JsonKey(name: 'last_name')
  final String? lastName;
  @override
  final String email;
  @override
  @JsonKey(name: 'avatar_url')
  final String? avatarUrl;
  @override
  @JsonKey(name: 'trust_score')
  final double trustScore;
  @override
  @JsonKey(name: 'is_verified')
  final bool isVerified;

  @override
  String toString() {
    return 'BusinessSeller(id: $id, name: $name, firstName: $firstName, lastName: $lastName, email: $email, avatarUrl: $avatarUrl, trustScore: $trustScore, isVerified: $isVerified)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$BusinessSellerImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.firstName, firstName) ||
                other.firstName == firstName) &&
            (identical(other.lastName, lastName) ||
                other.lastName == lastName) &&
            (identical(other.email, email) || other.email == email) &&
            (identical(other.avatarUrl, avatarUrl) ||
                other.avatarUrl == avatarUrl) &&
            (identical(other.trustScore, trustScore) ||
                other.trustScore == trustScore) &&
            (identical(other.isVerified, isVerified) ||
                other.isVerified == isVerified));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(runtimeType, id, name, firstName, lastName,
      email, avatarUrl, trustScore, isVerified);

  /// Create a copy of BusinessSeller
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$BusinessSellerImplCopyWith<_$BusinessSellerImpl> get copyWith =>
      __$$BusinessSellerImplCopyWithImpl<_$BusinessSellerImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$BusinessSellerImplToJson(
      this,
    );
  }
}

abstract class _BusinessSeller implements BusinessSeller {
  const factory _BusinessSeller(
          {required final String id,
          required final String name,
          @JsonKey(name: 'first_name') final String? firstName,
          @JsonKey(name: 'last_name') final String? lastName,
          required final String email,
          @JsonKey(name: 'avatar_url') final String? avatarUrl,
          @JsonKey(name: 'trust_score') final double trustScore,
          @JsonKey(name: 'is_verified') final bool isVerified}) =
      _$BusinessSellerImpl;

  factory _BusinessSeller.fromJson(Map<String, dynamic> json) =
      _$BusinessSellerImpl.fromJson;

  @override
  String get id;
  @override
  String get name;
  @override
  @JsonKey(name: 'first_name')
  String? get firstName;
  @override
  @JsonKey(name: 'last_name')
  String? get lastName;
  @override
  String get email;
  @override
  @JsonKey(name: 'avatar_url')
  String? get avatarUrl;
  @override
  @JsonKey(name: 'trust_score')
  double get trustScore;
  @override
  @JsonKey(name: 'is_verified')
  bool get isVerified;

  /// Create a copy of BusinessSeller
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$BusinessSellerImplCopyWith<_$BusinessSellerImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

Investment _$InvestmentFromJson(Map<String, dynamic> json) {
  return _Investment.fromJson(json);
}

/// @nodoc
mixin _$Investment {
  String get id => throw _privateConstructorUsedError;
  @JsonKey(name: 'business_id')
  String get businessId => throw _privateConstructorUsedError;
  @JsonKey(name: 'investor_id')
  String get investorId => throw _privateConstructorUsedError;
  double get amount => throw _privateConstructorUsedError;
  @JsonKey(name: 'equity_percentage')
  double get equityPercentage => throw _privateConstructorUsedError;
  String get status => throw _privateConstructorUsedError;
  String? get message => throw _privateConstructorUsedError;
  @JsonKey(name: 'created_at')
  DateTime get createdAt => throw _privateConstructorUsedError;
  @JsonKey(name: 'updated_at')
  DateTime get updatedAt => throw _privateConstructorUsedError;
  Business? get business => throw _privateConstructorUsedError;
  Investor? get investor => throw _privateConstructorUsedError;

  /// Serializes this Investment to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of Investment
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $InvestmentCopyWith<Investment> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $InvestmentCopyWith<$Res> {
  factory $InvestmentCopyWith(
          Investment value, $Res Function(Investment) then) =
      _$InvestmentCopyWithImpl<$Res, Investment>;
  @useResult
  $Res call(
      {String id,
      @JsonKey(name: 'business_id') String businessId,
      @JsonKey(name: 'investor_id') String investorId,
      double amount,
      @JsonKey(name: 'equity_percentage') double equityPercentage,
      String status,
      String? message,
      @JsonKey(name: 'created_at') DateTime createdAt,
      @JsonKey(name: 'updated_at') DateTime updatedAt,
      Business? business,
      Investor? investor});

  $BusinessCopyWith<$Res>? get business;
  $InvestorCopyWith<$Res>? get investor;
}

/// @nodoc
class _$InvestmentCopyWithImpl<$Res, $Val extends Investment>
    implements $InvestmentCopyWith<$Res> {
  _$InvestmentCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of Investment
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? businessId = null,
    Object? investorId = null,
    Object? amount = null,
    Object? equityPercentage = null,
    Object? status = null,
    Object? message = freezed,
    Object? createdAt = null,
    Object? updatedAt = null,
    Object? business = freezed,
    Object? investor = freezed,
  }) {
    return _then(_value.copyWith(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as String,
      businessId: null == businessId
          ? _value.businessId
          : businessId // ignore: cast_nullable_to_non_nullable
              as String,
      investorId: null == investorId
          ? _value.investorId
          : investorId // ignore: cast_nullable_to_non_nullable
              as String,
      amount: null == amount
          ? _value.amount
          : amount // ignore: cast_nullable_to_non_nullable
              as double,
      equityPercentage: null == equityPercentage
          ? _value.equityPercentage
          : equityPercentage // ignore: cast_nullable_to_non_nullable
              as double,
      status: null == status
          ? _value.status
          : status // ignore: cast_nullable_to_non_nullable
              as String,
      message: freezed == message
          ? _value.message
          : message // ignore: cast_nullable_to_non_nullable
              as String?,
      createdAt: null == createdAt
          ? _value.createdAt
          : createdAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
      updatedAt: null == updatedAt
          ? _value.updatedAt
          : updatedAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
      business: freezed == business
          ? _value.business
          : business // ignore: cast_nullable_to_non_nullable
              as Business?,
      investor: freezed == investor
          ? _value.investor
          : investor // ignore: cast_nullable_to_non_nullable
              as Investor?,
    ) as $Val);
  }

  /// Create a copy of Investment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $BusinessCopyWith<$Res>? get business {
    if (_value.business == null) {
      return null;
    }

    return $BusinessCopyWith<$Res>(_value.business!, (value) {
      return _then(_value.copyWith(business: value) as $Val);
    });
  }

  /// Create a copy of Investment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @pragma('vm:prefer-inline')
  $InvestorCopyWith<$Res>? get investor {
    if (_value.investor == null) {
      return null;
    }

    return $InvestorCopyWith<$Res>(_value.investor!, (value) {
      return _then(_value.copyWith(investor: value) as $Val);
    });
  }
}

/// @nodoc
abstract class _$$InvestmentImplCopyWith<$Res>
    implements $InvestmentCopyWith<$Res> {
  factory _$$InvestmentImplCopyWith(
          _$InvestmentImpl value, $Res Function(_$InvestmentImpl) then) =
      __$$InvestmentImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {String id,
      @JsonKey(name: 'business_id') String businessId,
      @JsonKey(name: 'investor_id') String investorId,
      double amount,
      @JsonKey(name: 'equity_percentage') double equityPercentage,
      String status,
      String? message,
      @JsonKey(name: 'created_at') DateTime createdAt,
      @JsonKey(name: 'updated_at') DateTime updatedAt,
      Business? business,
      Investor? investor});

  @override
  $BusinessCopyWith<$Res>? get business;
  @override
  $InvestorCopyWith<$Res>? get investor;
}

/// @nodoc
class __$$InvestmentImplCopyWithImpl<$Res>
    extends _$InvestmentCopyWithImpl<$Res, _$InvestmentImpl>
    implements _$$InvestmentImplCopyWith<$Res> {
  __$$InvestmentImplCopyWithImpl(
      _$InvestmentImpl _value, $Res Function(_$InvestmentImpl) _then)
      : super(_value, _then);

  /// Create a copy of Investment
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? businessId = null,
    Object? investorId = null,
    Object? amount = null,
    Object? equityPercentage = null,
    Object? status = null,
    Object? message = freezed,
    Object? createdAt = null,
    Object? updatedAt = null,
    Object? business = freezed,
    Object? investor = freezed,
  }) {
    return _then(_$InvestmentImpl(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as String,
      businessId: null == businessId
          ? _value.businessId
          : businessId // ignore: cast_nullable_to_non_nullable
              as String,
      investorId: null == investorId
          ? _value.investorId
          : investorId // ignore: cast_nullable_to_non_nullable
              as String,
      amount: null == amount
          ? _value.amount
          : amount // ignore: cast_nullable_to_non_nullable
              as double,
      equityPercentage: null == equityPercentage
          ? _value.equityPercentage
          : equityPercentage // ignore: cast_nullable_to_non_nullable
              as double,
      status: null == status
          ? _value.status
          : status // ignore: cast_nullable_to_non_nullable
              as String,
      message: freezed == message
          ? _value.message
          : message // ignore: cast_nullable_to_non_nullable
              as String?,
      createdAt: null == createdAt
          ? _value.createdAt
          : createdAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
      updatedAt: null == updatedAt
          ? _value.updatedAt
          : updatedAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
      business: freezed == business
          ? _value.business
          : business // ignore: cast_nullable_to_non_nullable
              as Business?,
      investor: freezed == investor
          ? _value.investor
          : investor // ignore: cast_nullable_to_non_nullable
              as Investor?,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$InvestmentImpl implements _Investment {
  const _$InvestmentImpl(
      {required this.id,
      @JsonKey(name: 'business_id') required this.businessId,
      @JsonKey(name: 'investor_id') required this.investorId,
      required this.amount,
      @JsonKey(name: 'equity_percentage') required this.equityPercentage,
      this.status = 'pending',
      this.message,
      @JsonKey(name: 'created_at') required this.createdAt,
      @JsonKey(name: 'updated_at') required this.updatedAt,
      this.business,
      this.investor});

  factory _$InvestmentImpl.fromJson(Map<String, dynamic> json) =>
      _$$InvestmentImplFromJson(json);

  @override
  final String id;
  @override
  @JsonKey(name: 'business_id')
  final String businessId;
  @override
  @JsonKey(name: 'investor_id')
  final String investorId;
  @override
  final double amount;
  @override
  @JsonKey(name: 'equity_percentage')
  final double equityPercentage;
  @override
  @JsonKey()
  final String status;
  @override
  final String? message;
  @override
  @JsonKey(name: 'created_at')
  final DateTime createdAt;
  @override
  @JsonKey(name: 'updated_at')
  final DateTime updatedAt;
  @override
  final Business? business;
  @override
  final Investor? investor;

  @override
  String toString() {
    return 'Investment(id: $id, businessId: $businessId, investorId: $investorId, amount: $amount, equityPercentage: $equityPercentage, status: $status, message: $message, createdAt: $createdAt, updatedAt: $updatedAt, business: $business, investor: $investor)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$InvestmentImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.businessId, businessId) ||
                other.businessId == businessId) &&
            (identical(other.investorId, investorId) ||
                other.investorId == investorId) &&
            (identical(other.amount, amount) || other.amount == amount) &&
            (identical(other.equityPercentage, equityPercentage) ||
                other.equityPercentage == equityPercentage) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.message, message) || other.message == message) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt) &&
            (identical(other.updatedAt, updatedAt) ||
                other.updatedAt == updatedAt) &&
            (identical(other.business, business) ||
                other.business == business) &&
            (identical(other.investor, investor) ||
                other.investor == investor));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      id,
      businessId,
      investorId,
      amount,
      equityPercentage,
      status,
      message,
      createdAt,
      updatedAt,
      business,
      investor);

  /// Create a copy of Investment
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$InvestmentImplCopyWith<_$InvestmentImpl> get copyWith =>
      __$$InvestmentImplCopyWithImpl<_$InvestmentImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$InvestmentImplToJson(
      this,
    );
  }
}

abstract class _Investment implements Investment {
  const factory _Investment(
      {required final String id,
      @JsonKey(name: 'business_id') required final String businessId,
      @JsonKey(name: 'investor_id') required final String investorId,
      required final double amount,
      @JsonKey(name: 'equity_percentage')
      required final double equityPercentage,
      final String status,
      final String? message,
      @JsonKey(name: 'created_at') required final DateTime createdAt,
      @JsonKey(name: 'updated_at') required final DateTime updatedAt,
      final Business? business,
      final Investor? investor}) = _$InvestmentImpl;

  factory _Investment.fromJson(Map<String, dynamic> json) =
      _$InvestmentImpl.fromJson;

  @override
  String get id;
  @override
  @JsonKey(name: 'business_id')
  String get businessId;
  @override
  @JsonKey(name: 'investor_id')
  String get investorId;
  @override
  double get amount;
  @override
  @JsonKey(name: 'equity_percentage')
  double get equityPercentage;
  @override
  String get status;
  @override
  String? get message;
  @override
  @JsonKey(name: 'created_at')
  DateTime get createdAt;
  @override
  @JsonKey(name: 'updated_at')
  DateTime get updatedAt;
  @override
  Business? get business;
  @override
  Investor? get investor;

  /// Create a copy of Investment
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$InvestmentImplCopyWith<_$InvestmentImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

Investor _$InvestorFromJson(Map<String, dynamic> json) {
  return _Investor.fromJson(json);
}

/// @nodoc
mixin _$Investor {
  String get id => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  @JsonKey(name: 'first_name')
  String? get firstName => throw _privateConstructorUsedError;
  @JsonKey(name: 'last_name')
  String? get lastName => throw _privateConstructorUsedError;
  String get email => throw _privateConstructorUsedError;
  @JsonKey(name: 'avatar_url')
  String? get avatarUrl => throw _privateConstructorUsedError;
  @JsonKey(name: 'trust_score')
  double get trustScore => throw _privateConstructorUsedError;
  @JsonKey(name: 'is_verified')
  bool get isVerified => throw _privateConstructorUsedError;

  /// Serializes this Investor to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of Investor
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $InvestorCopyWith<Investor> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $InvestorCopyWith<$Res> {
  factory $InvestorCopyWith(Investor value, $Res Function(Investor) then) =
      _$InvestorCopyWithImpl<$Res, Investor>;
  @useResult
  $Res call(
      {String id,
      String name,
      @JsonKey(name: 'first_name') String? firstName,
      @JsonKey(name: 'last_name') String? lastName,
      String email,
      @JsonKey(name: 'avatar_url') String? avatarUrl,
      @JsonKey(name: 'trust_score') double trustScore,
      @JsonKey(name: 'is_verified') bool isVerified});
}

/// @nodoc
class _$InvestorCopyWithImpl<$Res, $Val extends Investor>
    implements $InvestorCopyWith<$Res> {
  _$InvestorCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of Investor
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? firstName = freezed,
    Object? lastName = freezed,
    Object? email = null,
    Object? avatarUrl = freezed,
    Object? trustScore = null,
    Object? isVerified = null,
  }) {
    return _then(_value.copyWith(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as String,
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      firstName: freezed == firstName
          ? _value.firstName
          : firstName // ignore: cast_nullable_to_non_nullable
              as String?,
      lastName: freezed == lastName
          ? _value.lastName
          : lastName // ignore: cast_nullable_to_non_nullable
              as String?,
      email: null == email
          ? _value.email
          : email // ignore: cast_nullable_to_non_nullable
              as String,
      avatarUrl: freezed == avatarUrl
          ? _value.avatarUrl
          : avatarUrl // ignore: cast_nullable_to_non_nullable
              as String?,
      trustScore: null == trustScore
          ? _value.trustScore
          : trustScore // ignore: cast_nullable_to_non_nullable
              as double,
      isVerified: null == isVerified
          ? _value.isVerified
          : isVerified // ignore: cast_nullable_to_non_nullable
              as bool,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$InvestorImplCopyWith<$Res>
    implements $InvestorCopyWith<$Res> {
  factory _$$InvestorImplCopyWith(
          _$InvestorImpl value, $Res Function(_$InvestorImpl) then) =
      __$$InvestorImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {String id,
      String name,
      @JsonKey(name: 'first_name') String? firstName,
      @JsonKey(name: 'last_name') String? lastName,
      String email,
      @JsonKey(name: 'avatar_url') String? avatarUrl,
      @JsonKey(name: 'trust_score') double trustScore,
      @JsonKey(name: 'is_verified') bool isVerified});
}

/// @nodoc
class __$$InvestorImplCopyWithImpl<$Res>
    extends _$InvestorCopyWithImpl<$Res, _$InvestorImpl>
    implements _$$InvestorImplCopyWith<$Res> {
  __$$InvestorImplCopyWithImpl(
      _$InvestorImpl _value, $Res Function(_$InvestorImpl) _then)
      : super(_value, _then);

  /// Create a copy of Investor
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? name = null,
    Object? firstName = freezed,
    Object? lastName = freezed,
    Object? email = null,
    Object? avatarUrl = freezed,
    Object? trustScore = null,
    Object? isVerified = null,
  }) {
    return _then(_$InvestorImpl(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as String,
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      firstName: freezed == firstName
          ? _value.firstName
          : firstName // ignore: cast_nullable_to_non_nullable
              as String?,
      lastName: freezed == lastName
          ? _value.lastName
          : lastName // ignore: cast_nullable_to_non_nullable
              as String?,
      email: null == email
          ? _value.email
          : email // ignore: cast_nullable_to_non_nullable
              as String,
      avatarUrl: freezed == avatarUrl
          ? _value.avatarUrl
          : avatarUrl // ignore: cast_nullable_to_non_nullable
              as String?,
      trustScore: null == trustScore
          ? _value.trustScore
          : trustScore // ignore: cast_nullable_to_non_nullable
              as double,
      isVerified: null == isVerified
          ? _value.isVerified
          : isVerified // ignore: cast_nullable_to_non_nullable
              as bool,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$InvestorImpl implements _Investor {
  const _$InvestorImpl(
      {required this.id,
      required this.name,
      @JsonKey(name: 'first_name') this.firstName,
      @JsonKey(name: 'last_name') this.lastName,
      required this.email,
      @JsonKey(name: 'avatar_url') this.avatarUrl,
      @JsonKey(name: 'trust_score') this.trustScore = 0.0,
      @JsonKey(name: 'is_verified') this.isVerified = false});

  factory _$InvestorImpl.fromJson(Map<String, dynamic> json) =>
      _$$InvestorImplFromJson(json);

  @override
  final String id;
  @override
  final String name;
  @override
  @JsonKey(name: 'first_name')
  final String? firstName;
  @override
  @JsonKey(name: 'last_name')
  final String? lastName;
  @override
  final String email;
  @override
  @JsonKey(name: 'avatar_url')
  final String? avatarUrl;
  @override
  @JsonKey(name: 'trust_score')
  final double trustScore;
  @override
  @JsonKey(name: 'is_verified')
  final bool isVerified;

  @override
  String toString() {
    return 'Investor(id: $id, name: $name, firstName: $firstName, lastName: $lastName, email: $email, avatarUrl: $avatarUrl, trustScore: $trustScore, isVerified: $isVerified)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$InvestorImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.firstName, firstName) ||
                other.firstName == firstName) &&
            (identical(other.lastName, lastName) ||
                other.lastName == lastName) &&
            (identical(other.email, email) || other.email == email) &&
            (identical(other.avatarUrl, avatarUrl) ||
                other.avatarUrl == avatarUrl) &&
            (identical(other.trustScore, trustScore) ||
                other.trustScore == trustScore) &&
            (identical(other.isVerified, isVerified) ||
                other.isVerified == isVerified));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(runtimeType, id, name, firstName, lastName,
      email, avatarUrl, trustScore, isVerified);

  /// Create a copy of Investor
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$InvestorImplCopyWith<_$InvestorImpl> get copyWith =>
      __$$InvestorImplCopyWithImpl<_$InvestorImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$InvestorImplToJson(
      this,
    );
  }
}

abstract class _Investor implements Investor {
  const factory _Investor(
      {required final String id,
      required final String name,
      @JsonKey(name: 'first_name') final String? firstName,
      @JsonKey(name: 'last_name') final String? lastName,
      required final String email,
      @JsonKey(name: 'avatar_url') final String? avatarUrl,
      @JsonKey(name: 'trust_score') final double trustScore,
      @JsonKey(name: 'is_verified') final bool isVerified}) = _$InvestorImpl;

  factory _Investor.fromJson(Map<String, dynamic> json) =
      _$InvestorImpl.fromJson;

  @override
  String get id;
  @override
  String get name;
  @override
  @JsonKey(name: 'first_name')
  String? get firstName;
  @override
  @JsonKey(name: 'last_name')
  String? get lastName;
  @override
  String get email;
  @override
  @JsonKey(name: 'avatar_url')
  String? get avatarUrl;
  @override
  @JsonKey(name: 'trust_score')
  double get trustScore;
  @override
  @JsonKey(name: 'is_verified')
  bool get isVerified;

  /// Create a copy of Investor
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$InvestorImplCopyWith<_$InvestorImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

Portfolio _$PortfolioFromJson(Map<String, dynamic> json) {
  return _Portfolio.fromJson(json);
}

/// @nodoc
mixin _$Portfolio {
  @JsonKey(name: 'total_invested')
  double get totalInvested => throw _privateConstructorUsedError;
  @JsonKey(name: 'total_returns')
  double get totalReturns => throw _privateConstructorUsedError;
  @JsonKey(name: 'active_investments')
  int get activeInvestments => throw _privateConstructorUsedError;
  @JsonKey(name: 'portfolio_value')
  double get portfolioValue => throw _privateConstructorUsedError;
  List<Investment> get investments => throw _privateConstructorUsedError;

  /// Serializes this Portfolio to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of Portfolio
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $PortfolioCopyWith<Portfolio> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $PortfolioCopyWith<$Res> {
  factory $PortfolioCopyWith(Portfolio value, $Res Function(Portfolio) then) =
      _$PortfolioCopyWithImpl<$Res, Portfolio>;
  @useResult
  $Res call(
      {@JsonKey(name: 'total_invested') double totalInvested,
      @JsonKey(name: 'total_returns') double totalReturns,
      @JsonKey(name: 'active_investments') int activeInvestments,
      @JsonKey(name: 'portfolio_value') double portfolioValue,
      List<Investment> investments});
}

/// @nodoc
class _$PortfolioCopyWithImpl<$Res, $Val extends Portfolio>
    implements $PortfolioCopyWith<$Res> {
  _$PortfolioCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of Portfolio
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? totalInvested = null,
    Object? totalReturns = null,
    Object? activeInvestments = null,
    Object? portfolioValue = null,
    Object? investments = null,
  }) {
    return _then(_value.copyWith(
      totalInvested: null == totalInvested
          ? _value.totalInvested
          : totalInvested // ignore: cast_nullable_to_non_nullable
              as double,
      totalReturns: null == totalReturns
          ? _value.totalReturns
          : totalReturns // ignore: cast_nullable_to_non_nullable
              as double,
      activeInvestments: null == activeInvestments
          ? _value.activeInvestments
          : activeInvestments // ignore: cast_nullable_to_non_nullable
              as int,
      portfolioValue: null == portfolioValue
          ? _value.portfolioValue
          : portfolioValue // ignore: cast_nullable_to_non_nullable
              as double,
      investments: null == investments
          ? _value.investments
          : investments // ignore: cast_nullable_to_non_nullable
              as List<Investment>,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$PortfolioImplCopyWith<$Res>
    implements $PortfolioCopyWith<$Res> {
  factory _$$PortfolioImplCopyWith(
          _$PortfolioImpl value, $Res Function(_$PortfolioImpl) then) =
      __$$PortfolioImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {@JsonKey(name: 'total_invested') double totalInvested,
      @JsonKey(name: 'total_returns') double totalReturns,
      @JsonKey(name: 'active_investments') int activeInvestments,
      @JsonKey(name: 'portfolio_value') double portfolioValue,
      List<Investment> investments});
}

/// @nodoc
class __$$PortfolioImplCopyWithImpl<$Res>
    extends _$PortfolioCopyWithImpl<$Res, _$PortfolioImpl>
    implements _$$PortfolioImplCopyWith<$Res> {
  __$$PortfolioImplCopyWithImpl(
      _$PortfolioImpl _value, $Res Function(_$PortfolioImpl) _then)
      : super(_value, _then);

  /// Create a copy of Portfolio
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? totalInvested = null,
    Object? totalReturns = null,
    Object? activeInvestments = null,
    Object? portfolioValue = null,
    Object? investments = null,
  }) {
    return _then(_$PortfolioImpl(
      totalInvested: null == totalInvested
          ? _value.totalInvested
          : totalInvested // ignore: cast_nullable_to_non_nullable
              as double,
      totalReturns: null == totalReturns
          ? _value.totalReturns
          : totalReturns // ignore: cast_nullable_to_non_nullable
              as double,
      activeInvestments: null == activeInvestments
          ? _value.activeInvestments
          : activeInvestments // ignore: cast_nullable_to_non_nullable
              as int,
      portfolioValue: null == portfolioValue
          ? _value.portfolioValue
          : portfolioValue // ignore: cast_nullable_to_non_nullable
              as double,
      investments: null == investments
          ? _value._investments
          : investments // ignore: cast_nullable_to_non_nullable
              as List<Investment>,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$PortfolioImpl implements _Portfolio {
  const _$PortfolioImpl(
      {@JsonKey(name: 'total_invested') this.totalInvested = 0.0,
      @JsonKey(name: 'total_returns') this.totalReturns = 0.0,
      @JsonKey(name: 'active_investments') this.activeInvestments = 0,
      @JsonKey(name: 'portfolio_value') this.portfolioValue = 0.0,
      final List<Investment> investments = const []})
      : _investments = investments;

  factory _$PortfolioImpl.fromJson(Map<String, dynamic> json) =>
      _$$PortfolioImplFromJson(json);

  @override
  @JsonKey(name: 'total_invested')
  final double totalInvested;
  @override
  @JsonKey(name: 'total_returns')
  final double totalReturns;
  @override
  @JsonKey(name: 'active_investments')
  final int activeInvestments;
  @override
  @JsonKey(name: 'portfolio_value')
  final double portfolioValue;
  final List<Investment> _investments;
  @override
  @JsonKey()
  List<Investment> get investments {
    if (_investments is EqualUnmodifiableListView) return _investments;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_investments);
  }

  @override
  String toString() {
    return 'Portfolio(totalInvested: $totalInvested, totalReturns: $totalReturns, activeInvestments: $activeInvestments, portfolioValue: $portfolioValue, investments: $investments)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$PortfolioImpl &&
            (identical(other.totalInvested, totalInvested) ||
                other.totalInvested == totalInvested) &&
            (identical(other.totalReturns, totalReturns) ||
                other.totalReturns == totalReturns) &&
            (identical(other.activeInvestments, activeInvestments) ||
                other.activeInvestments == activeInvestments) &&
            (identical(other.portfolioValue, portfolioValue) ||
                other.portfolioValue == portfolioValue) &&
            const DeepCollectionEquality()
                .equals(other._investments, _investments));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      totalInvested,
      totalReturns,
      activeInvestments,
      portfolioValue,
      const DeepCollectionEquality().hash(_investments));

  /// Create a copy of Portfolio
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$PortfolioImplCopyWith<_$PortfolioImpl> get copyWith =>
      __$$PortfolioImplCopyWithImpl<_$PortfolioImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$PortfolioImplToJson(
      this,
    );
  }
}

abstract class _Portfolio implements Portfolio {
  const factory _Portfolio(
      {@JsonKey(name: 'total_invested') final double totalInvested,
      @JsonKey(name: 'total_returns') final double totalReturns,
      @JsonKey(name: 'active_investments') final int activeInvestments,
      @JsonKey(name: 'portfolio_value') final double portfolioValue,
      final List<Investment> investments}) = _$PortfolioImpl;

  factory _Portfolio.fromJson(Map<String, dynamic> json) =
      _$PortfolioImpl.fromJson;

  @override
  @JsonKey(name: 'total_invested')
  double get totalInvested;
  @override
  @JsonKey(name: 'total_returns')
  double get totalReturns;
  @override
  @JsonKey(name: 'active_investments')
  int get activeInvestments;
  @override
  @JsonKey(name: 'portfolio_value')
  double get portfolioValue;
  @override
  List<Investment> get investments;

  /// Create a copy of Portfolio
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$PortfolioImplCopyWith<_$PortfolioImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

CreateBusinessRequest _$CreateBusinessRequestFromJson(
    Map<String, dynamic> json) {
  return _CreateBusinessRequest.fromJson(json);
}

/// @nodoc
mixin _$CreateBusinessRequest {
  String get name => throw _privateConstructorUsedError;
  String get description => throw _privateConstructorUsedError;
  String get industry => throw _privateConstructorUsedError;
  double get valuation => throw _privateConstructorUsedError;
  @JsonKey(name: 'funding_goal')
  double get fundingGoal => throw _privateConstructorUsedError;
  @JsonKey(name: 'equity_offered')
  double get equityOffered => throw _privateConstructorUsedError;
  String? get businessPlan => throw _privateConstructorUsedError;
  double get revenue => throw _privateConstructorUsedError;
  @JsonKey(name: 'profit_margin')
  double get profitMargin => throw _privateConstructorUsedError;
  @JsonKey(name: 'employees_count')
  int get employeesCount => throw _privateConstructorUsedError;
  @JsonKey(name: 'founded_year')
  int get foundedYear => throw _privateConstructorUsedError;
  String get location => throw _privateConstructorUsedError;
  List<String> get images => throw _privateConstructorUsedError;

  /// Serializes this CreateBusinessRequest to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of CreateBusinessRequest
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $CreateBusinessRequestCopyWith<CreateBusinessRequest> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $CreateBusinessRequestCopyWith<$Res> {
  factory $CreateBusinessRequestCopyWith(CreateBusinessRequest value,
          $Res Function(CreateBusinessRequest) then) =
      _$CreateBusinessRequestCopyWithImpl<$Res, CreateBusinessRequest>;
  @useResult
  $Res call(
      {String name,
      String description,
      String industry,
      double valuation,
      @JsonKey(name: 'funding_goal') double fundingGoal,
      @JsonKey(name: 'equity_offered') double equityOffered,
      String? businessPlan,
      double revenue,
      @JsonKey(name: 'profit_margin') double profitMargin,
      @JsonKey(name: 'employees_count') int employeesCount,
      @JsonKey(name: 'founded_year') int foundedYear,
      String location,
      List<String> images});
}

/// @nodoc
class _$CreateBusinessRequestCopyWithImpl<$Res,
        $Val extends CreateBusinessRequest>
    implements $CreateBusinessRequestCopyWith<$Res> {
  _$CreateBusinessRequestCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of CreateBusinessRequest
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? name = null,
    Object? description = null,
    Object? industry = null,
    Object? valuation = null,
    Object? fundingGoal = null,
    Object? equityOffered = null,
    Object? businessPlan = freezed,
    Object? revenue = null,
    Object? profitMargin = null,
    Object? employeesCount = null,
    Object? foundedYear = null,
    Object? location = null,
    Object? images = null,
  }) {
    return _then(_value.copyWith(
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      description: null == description
          ? _value.description
          : description // ignore: cast_nullable_to_non_nullable
              as String,
      industry: null == industry
          ? _value.industry
          : industry // ignore: cast_nullable_to_non_nullable
              as String,
      valuation: null == valuation
          ? _value.valuation
          : valuation // ignore: cast_nullable_to_non_nullable
              as double,
      fundingGoal: null == fundingGoal
          ? _value.fundingGoal
          : fundingGoal // ignore: cast_nullable_to_non_nullable
              as double,
      equityOffered: null == equityOffered
          ? _value.equityOffered
          : equityOffered // ignore: cast_nullable_to_non_nullable
              as double,
      businessPlan: freezed == businessPlan
          ? _value.businessPlan
          : businessPlan // ignore: cast_nullable_to_non_nullable
              as String?,
      revenue: null == revenue
          ? _value.revenue
          : revenue // ignore: cast_nullable_to_non_nullable
              as double,
      profitMargin: null == profitMargin
          ? _value.profitMargin
          : profitMargin // ignore: cast_nullable_to_non_nullable
              as double,
      employeesCount: null == employeesCount
          ? _value.employeesCount
          : employeesCount // ignore: cast_nullable_to_non_nullable
              as int,
      foundedYear: null == foundedYear
          ? _value.foundedYear
          : foundedYear // ignore: cast_nullable_to_non_nullable
              as int,
      location: null == location
          ? _value.location
          : location // ignore: cast_nullable_to_non_nullable
              as String,
      images: null == images
          ? _value.images
          : images // ignore: cast_nullable_to_non_nullable
              as List<String>,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$CreateBusinessRequestImplCopyWith<$Res>
    implements $CreateBusinessRequestCopyWith<$Res> {
  factory _$$CreateBusinessRequestImplCopyWith(
          _$CreateBusinessRequestImpl value,
          $Res Function(_$CreateBusinessRequestImpl) then) =
      __$$CreateBusinessRequestImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {String name,
      String description,
      String industry,
      double valuation,
      @JsonKey(name: 'funding_goal') double fundingGoal,
      @JsonKey(name: 'equity_offered') double equityOffered,
      String? businessPlan,
      double revenue,
      @JsonKey(name: 'profit_margin') double profitMargin,
      @JsonKey(name: 'employees_count') int employeesCount,
      @JsonKey(name: 'founded_year') int foundedYear,
      String location,
      List<String> images});
}

/// @nodoc
class __$$CreateBusinessRequestImplCopyWithImpl<$Res>
    extends _$CreateBusinessRequestCopyWithImpl<$Res,
        _$CreateBusinessRequestImpl>
    implements _$$CreateBusinessRequestImplCopyWith<$Res> {
  __$$CreateBusinessRequestImplCopyWithImpl(_$CreateBusinessRequestImpl _value,
      $Res Function(_$CreateBusinessRequestImpl) _then)
      : super(_value, _then);

  /// Create a copy of CreateBusinessRequest
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? name = null,
    Object? description = null,
    Object? industry = null,
    Object? valuation = null,
    Object? fundingGoal = null,
    Object? equityOffered = null,
    Object? businessPlan = freezed,
    Object? revenue = null,
    Object? profitMargin = null,
    Object? employeesCount = null,
    Object? foundedYear = null,
    Object? location = null,
    Object? images = null,
  }) {
    return _then(_$CreateBusinessRequestImpl(
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      description: null == description
          ? _value.description
          : description // ignore: cast_nullable_to_non_nullable
              as String,
      industry: null == industry
          ? _value.industry
          : industry // ignore: cast_nullable_to_non_nullable
              as String,
      valuation: null == valuation
          ? _value.valuation
          : valuation // ignore: cast_nullable_to_non_nullable
              as double,
      fundingGoal: null == fundingGoal
          ? _value.fundingGoal
          : fundingGoal // ignore: cast_nullable_to_non_nullable
              as double,
      equityOffered: null == equityOffered
          ? _value.equityOffered
          : equityOffered // ignore: cast_nullable_to_non_nullable
              as double,
      businessPlan: freezed == businessPlan
          ? _value.businessPlan
          : businessPlan // ignore: cast_nullable_to_non_nullable
              as String?,
      revenue: null == revenue
          ? _value.revenue
          : revenue // ignore: cast_nullable_to_non_nullable
              as double,
      profitMargin: null == profitMargin
          ? _value.profitMargin
          : profitMargin // ignore: cast_nullable_to_non_nullable
              as double,
      employeesCount: null == employeesCount
          ? _value.employeesCount
          : employeesCount // ignore: cast_nullable_to_non_nullable
              as int,
      foundedYear: null == foundedYear
          ? _value.foundedYear
          : foundedYear // ignore: cast_nullable_to_non_nullable
              as int,
      location: null == location
          ? _value.location
          : location // ignore: cast_nullable_to_non_nullable
              as String,
      images: null == images
          ? _value._images
          : images // ignore: cast_nullable_to_non_nullable
              as List<String>,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$CreateBusinessRequestImpl implements _CreateBusinessRequest {
  const _$CreateBusinessRequestImpl(
      {required this.name,
      required this.description,
      required this.industry,
      required this.valuation,
      @JsonKey(name: 'funding_goal') required this.fundingGoal,
      @JsonKey(name: 'equity_offered') required this.equityOffered,
      this.businessPlan,
      required this.revenue,
      @JsonKey(name: 'profit_margin') required this.profitMargin,
      @JsonKey(name: 'employees_count') required this.employeesCount,
      @JsonKey(name: 'founded_year') required this.foundedYear,
      required this.location,
      final List<String> images = const []})
      : _images = images;

  factory _$CreateBusinessRequestImpl.fromJson(Map<String, dynamic> json) =>
      _$$CreateBusinessRequestImplFromJson(json);

  @override
  final String name;
  @override
  final String description;
  @override
  final String industry;
  @override
  final double valuation;
  @override
  @JsonKey(name: 'funding_goal')
  final double fundingGoal;
  @override
  @JsonKey(name: 'equity_offered')
  final double equityOffered;
  @override
  final String? businessPlan;
  @override
  final double revenue;
  @override
  @JsonKey(name: 'profit_margin')
  final double profitMargin;
  @override
  @JsonKey(name: 'employees_count')
  final int employeesCount;
  @override
  @JsonKey(name: 'founded_year')
  final int foundedYear;
  @override
  final String location;
  final List<String> _images;
  @override
  @JsonKey()
  List<String> get images {
    if (_images is EqualUnmodifiableListView) return _images;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_images);
  }

  @override
  String toString() {
    return 'CreateBusinessRequest(name: $name, description: $description, industry: $industry, valuation: $valuation, fundingGoal: $fundingGoal, equityOffered: $equityOffered, businessPlan: $businessPlan, revenue: $revenue, profitMargin: $profitMargin, employeesCount: $employeesCount, foundedYear: $foundedYear, location: $location, images: $images)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$CreateBusinessRequestImpl &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.industry, industry) ||
                other.industry == industry) &&
            (identical(other.valuation, valuation) ||
                other.valuation == valuation) &&
            (identical(other.fundingGoal, fundingGoal) ||
                other.fundingGoal == fundingGoal) &&
            (identical(other.equityOffered, equityOffered) ||
                other.equityOffered == equityOffered) &&
            (identical(other.businessPlan, businessPlan) ||
                other.businessPlan == businessPlan) &&
            (identical(other.revenue, revenue) || other.revenue == revenue) &&
            (identical(other.profitMargin, profitMargin) ||
                other.profitMargin == profitMargin) &&
            (identical(other.employeesCount, employeesCount) ||
                other.employeesCount == employeesCount) &&
            (identical(other.foundedYear, foundedYear) ||
                other.foundedYear == foundedYear) &&
            (identical(other.location, location) ||
                other.location == location) &&
            const DeepCollectionEquality().equals(other._images, _images));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      name,
      description,
      industry,
      valuation,
      fundingGoal,
      equityOffered,
      businessPlan,
      revenue,
      profitMargin,
      employeesCount,
      foundedYear,
      location,
      const DeepCollectionEquality().hash(_images));

  /// Create a copy of CreateBusinessRequest
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$CreateBusinessRequestImplCopyWith<_$CreateBusinessRequestImpl>
      get copyWith => __$$CreateBusinessRequestImplCopyWithImpl<
          _$CreateBusinessRequestImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$CreateBusinessRequestImplToJson(
      this,
    );
  }
}

abstract class _CreateBusinessRequest implements CreateBusinessRequest {
  const factory _CreateBusinessRequest(
      {required final String name,
      required final String description,
      required final String industry,
      required final double valuation,
      @JsonKey(name: 'funding_goal') required final double fundingGoal,
      @JsonKey(name: 'equity_offered') required final double equityOffered,
      final String? businessPlan,
      required final double revenue,
      @JsonKey(name: 'profit_margin') required final double profitMargin,
      @JsonKey(name: 'employees_count') required final int employeesCount,
      @JsonKey(name: 'founded_year') required final int foundedYear,
      required final String location,
      final List<String> images}) = _$CreateBusinessRequestImpl;

  factory _CreateBusinessRequest.fromJson(Map<String, dynamic> json) =
      _$CreateBusinessRequestImpl.fromJson;

  @override
  String get name;
  @override
  String get description;
  @override
  String get industry;
  @override
  double get valuation;
  @override
  @JsonKey(name: 'funding_goal')
  double get fundingGoal;
  @override
  @JsonKey(name: 'equity_offered')
  double get equityOffered;
  @override
  String? get businessPlan;
  @override
  double get revenue;
  @override
  @JsonKey(name: 'profit_margin')
  double get profitMargin;
  @override
  @JsonKey(name: 'employees_count')
  int get employeesCount;
  @override
  @JsonKey(name: 'founded_year')
  int get foundedYear;
  @override
  String get location;
  @override
  List<String> get images;

  /// Create a copy of CreateBusinessRequest
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$CreateBusinessRequestImplCopyWith<_$CreateBusinessRequestImpl>
      get copyWith => throw _privateConstructorUsedError;
}

CreateInvestmentRequest _$CreateInvestmentRequestFromJson(
    Map<String, dynamic> json) {
  return _CreateInvestmentRequest.fromJson(json);
}

/// @nodoc
mixin _$CreateInvestmentRequest {
  @JsonKey(name: 'business_id')
  String get businessId => throw _privateConstructorUsedError;
  double get amount => throw _privateConstructorUsedError;
  String? get message => throw _privateConstructorUsedError;

  /// Serializes this CreateInvestmentRequest to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of CreateInvestmentRequest
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $CreateInvestmentRequestCopyWith<CreateInvestmentRequest> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $CreateInvestmentRequestCopyWith<$Res> {
  factory $CreateInvestmentRequestCopyWith(CreateInvestmentRequest value,
          $Res Function(CreateInvestmentRequest) then) =
      _$CreateInvestmentRequestCopyWithImpl<$Res, CreateInvestmentRequest>;
  @useResult
  $Res call(
      {@JsonKey(name: 'business_id') String businessId,
      double amount,
      String? message});
}

/// @nodoc
class _$CreateInvestmentRequestCopyWithImpl<$Res,
        $Val extends CreateInvestmentRequest>
    implements $CreateInvestmentRequestCopyWith<$Res> {
  _$CreateInvestmentRequestCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of CreateInvestmentRequest
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? businessId = null,
    Object? amount = null,
    Object? message = freezed,
  }) {
    return _then(_value.copyWith(
      businessId: null == businessId
          ? _value.businessId
          : businessId // ignore: cast_nullable_to_non_nullable
              as String,
      amount: null == amount
          ? _value.amount
          : amount // ignore: cast_nullable_to_non_nullable
              as double,
      message: freezed == message
          ? _value.message
          : message // ignore: cast_nullable_to_non_nullable
              as String?,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$CreateInvestmentRequestImplCopyWith<$Res>
    implements $CreateInvestmentRequestCopyWith<$Res> {
  factory _$$CreateInvestmentRequestImplCopyWith(
          _$CreateInvestmentRequestImpl value,
          $Res Function(_$CreateInvestmentRequestImpl) then) =
      __$$CreateInvestmentRequestImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {@JsonKey(name: 'business_id') String businessId,
      double amount,
      String? message});
}

/// @nodoc
class __$$CreateInvestmentRequestImplCopyWithImpl<$Res>
    extends _$CreateInvestmentRequestCopyWithImpl<$Res,
        _$CreateInvestmentRequestImpl>
    implements _$$CreateInvestmentRequestImplCopyWith<$Res> {
  __$$CreateInvestmentRequestImplCopyWithImpl(
      _$CreateInvestmentRequestImpl _value,
      $Res Function(_$CreateInvestmentRequestImpl) _then)
      : super(_value, _then);

  /// Create a copy of CreateInvestmentRequest
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? businessId = null,
    Object? amount = null,
    Object? message = freezed,
  }) {
    return _then(_$CreateInvestmentRequestImpl(
      businessId: null == businessId
          ? _value.businessId
          : businessId // ignore: cast_nullable_to_non_nullable
              as String,
      amount: null == amount
          ? _value.amount
          : amount // ignore: cast_nullable_to_non_nullable
              as double,
      message: freezed == message
          ? _value.message
          : message // ignore: cast_nullable_to_non_nullable
              as String?,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$CreateInvestmentRequestImpl implements _CreateInvestmentRequest {
  const _$CreateInvestmentRequestImpl(
      {@JsonKey(name: 'business_id') required this.businessId,
      required this.amount,
      this.message});

  factory _$CreateInvestmentRequestImpl.fromJson(Map<String, dynamic> json) =>
      _$$CreateInvestmentRequestImplFromJson(json);

  @override
  @JsonKey(name: 'business_id')
  final String businessId;
  @override
  final double amount;
  @override
  final String? message;

  @override
  String toString() {
    return 'CreateInvestmentRequest(businessId: $businessId, amount: $amount, message: $message)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$CreateInvestmentRequestImpl &&
            (identical(other.businessId, businessId) ||
                other.businessId == businessId) &&
            (identical(other.amount, amount) || other.amount == amount) &&
            (identical(other.message, message) || other.message == message));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(runtimeType, businessId, amount, message);

  /// Create a copy of CreateInvestmentRequest
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$CreateInvestmentRequestImplCopyWith<_$CreateInvestmentRequestImpl>
      get copyWith => __$$CreateInvestmentRequestImplCopyWithImpl<
          _$CreateInvestmentRequestImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$CreateInvestmentRequestImplToJson(
      this,
    );
  }
}

abstract class _CreateInvestmentRequest implements CreateInvestmentRequest {
  const factory _CreateInvestmentRequest(
      {@JsonKey(name: 'business_id') required final String businessId,
      required final double amount,
      final String? message}) = _$CreateInvestmentRequestImpl;

  factory _CreateInvestmentRequest.fromJson(Map<String, dynamic> json) =
      _$CreateInvestmentRequestImpl.fromJson;

  @override
  @JsonKey(name: 'business_id')
  String get businessId;
  @override
  double get amount;
  @override
  String? get message;

  /// Create a copy of CreateInvestmentRequest
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$CreateInvestmentRequestImplCopyWith<_$CreateInvestmentRequestImpl>
      get copyWith => throw _privateConstructorUsedError;
}

BusinessFilters _$BusinessFiltersFromJson(Map<String, dynamic> json) {
  return _BusinessFilters.fromJson(json);
}

/// @nodoc
mixin _$BusinessFilters {
  String? get industry => throw _privateConstructorUsedError;
  @JsonKey(name: 'min_valuation')
  double? get minValuation => throw _privateConstructorUsedError;
  @JsonKey(name: 'max_valuation')
  double? get maxValuation => throw _privateConstructorUsedError;
  @JsonKey(name: 'min_funding')
  double? get minFunding => throw _privateConstructorUsedError;
  @JsonKey(name: 'max_funding')
  double? get maxFunding => throw _privateConstructorUsedError;
  String? get location => throw _privateConstructorUsedError;
  String? get search => throw _privateConstructorUsedError;
  @JsonKey(name: 'sort_by')
  String get sortBy => throw _privateConstructorUsedError;
  @JsonKey(name: 'sort_order')
  String get sortOrder => throw _privateConstructorUsedError;

  /// Serializes this BusinessFilters to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of BusinessFilters
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $BusinessFiltersCopyWith<BusinessFilters> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $BusinessFiltersCopyWith<$Res> {
  factory $BusinessFiltersCopyWith(
          BusinessFilters value, $Res Function(BusinessFilters) then) =
      _$BusinessFiltersCopyWithImpl<$Res, BusinessFilters>;
  @useResult
  $Res call(
      {String? industry,
      @JsonKey(name: 'min_valuation') double? minValuation,
      @JsonKey(name: 'max_valuation') double? maxValuation,
      @JsonKey(name: 'min_funding') double? minFunding,
      @JsonKey(name: 'max_funding') double? maxFunding,
      String? location,
      String? search,
      @JsonKey(name: 'sort_by') String sortBy,
      @JsonKey(name: 'sort_order') String sortOrder});
}

/// @nodoc
class _$BusinessFiltersCopyWithImpl<$Res, $Val extends BusinessFilters>
    implements $BusinessFiltersCopyWith<$Res> {
  _$BusinessFiltersCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of BusinessFilters
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? industry = freezed,
    Object? minValuation = freezed,
    Object? maxValuation = freezed,
    Object? minFunding = freezed,
    Object? maxFunding = freezed,
    Object? location = freezed,
    Object? search = freezed,
    Object? sortBy = null,
    Object? sortOrder = null,
  }) {
    return _then(_value.copyWith(
      industry: freezed == industry
          ? _value.industry
          : industry // ignore: cast_nullable_to_non_nullable
              as String?,
      minValuation: freezed == minValuation
          ? _value.minValuation
          : minValuation // ignore: cast_nullable_to_non_nullable
              as double?,
      maxValuation: freezed == maxValuation
          ? _value.maxValuation
          : maxValuation // ignore: cast_nullable_to_non_nullable
              as double?,
      minFunding: freezed == minFunding
          ? _value.minFunding
          : minFunding // ignore: cast_nullable_to_non_nullable
              as double?,
      maxFunding: freezed == maxFunding
          ? _value.maxFunding
          : maxFunding // ignore: cast_nullable_to_non_nullable
              as double?,
      location: freezed == location
          ? _value.location
          : location // ignore: cast_nullable_to_non_nullable
              as String?,
      search: freezed == search
          ? _value.search
          : search // ignore: cast_nullable_to_non_nullable
              as String?,
      sortBy: null == sortBy
          ? _value.sortBy
          : sortBy // ignore: cast_nullable_to_non_nullable
              as String,
      sortOrder: null == sortOrder
          ? _value.sortOrder
          : sortOrder // ignore: cast_nullable_to_non_nullable
              as String,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$BusinessFiltersImplCopyWith<$Res>
    implements $BusinessFiltersCopyWith<$Res> {
  factory _$$BusinessFiltersImplCopyWith(_$BusinessFiltersImpl value,
          $Res Function(_$BusinessFiltersImpl) then) =
      __$$BusinessFiltersImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {String? industry,
      @JsonKey(name: 'min_valuation') double? minValuation,
      @JsonKey(name: 'max_valuation') double? maxValuation,
      @JsonKey(name: 'min_funding') double? minFunding,
      @JsonKey(name: 'max_funding') double? maxFunding,
      String? location,
      String? search,
      @JsonKey(name: 'sort_by') String sortBy,
      @JsonKey(name: 'sort_order') String sortOrder});
}

/// @nodoc
class __$$BusinessFiltersImplCopyWithImpl<$Res>
    extends _$BusinessFiltersCopyWithImpl<$Res, _$BusinessFiltersImpl>
    implements _$$BusinessFiltersImplCopyWith<$Res> {
  __$$BusinessFiltersImplCopyWithImpl(
      _$BusinessFiltersImpl _value, $Res Function(_$BusinessFiltersImpl) _then)
      : super(_value, _then);

  /// Create a copy of BusinessFilters
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? industry = freezed,
    Object? minValuation = freezed,
    Object? maxValuation = freezed,
    Object? minFunding = freezed,
    Object? maxFunding = freezed,
    Object? location = freezed,
    Object? search = freezed,
    Object? sortBy = null,
    Object? sortOrder = null,
  }) {
    return _then(_$BusinessFiltersImpl(
      industry: freezed == industry
          ? _value.industry
          : industry // ignore: cast_nullable_to_non_nullable
              as String?,
      minValuation: freezed == minValuation
          ? _value.minValuation
          : minValuation // ignore: cast_nullable_to_non_nullable
              as double?,
      maxValuation: freezed == maxValuation
          ? _value.maxValuation
          : maxValuation // ignore: cast_nullable_to_non_nullable
              as double?,
      minFunding: freezed == minFunding
          ? _value.minFunding
          : minFunding // ignore: cast_nullable_to_non_nullable
              as double?,
      maxFunding: freezed == maxFunding
          ? _value.maxFunding
          : maxFunding // ignore: cast_nullable_to_non_nullable
              as double?,
      location: freezed == location
          ? _value.location
          : location // ignore: cast_nullable_to_non_nullable
              as String?,
      search: freezed == search
          ? _value.search
          : search // ignore: cast_nullable_to_non_nullable
              as String?,
      sortBy: null == sortBy
          ? _value.sortBy
          : sortBy // ignore: cast_nullable_to_non_nullable
              as String,
      sortOrder: null == sortOrder
          ? _value.sortOrder
          : sortOrder // ignore: cast_nullable_to_non_nullable
              as String,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$BusinessFiltersImpl implements _BusinessFilters {
  const _$BusinessFiltersImpl(
      {this.industry,
      @JsonKey(name: 'min_valuation') this.minValuation,
      @JsonKey(name: 'max_valuation') this.maxValuation,
      @JsonKey(name: 'min_funding') this.minFunding,
      @JsonKey(name: 'max_funding') this.maxFunding,
      this.location,
      this.search,
      @JsonKey(name: 'sort_by') this.sortBy = 'created_at',
      @JsonKey(name: 'sort_order') this.sortOrder = 'desc'});

  factory _$BusinessFiltersImpl.fromJson(Map<String, dynamic> json) =>
      _$$BusinessFiltersImplFromJson(json);

  @override
  final String? industry;
  @override
  @JsonKey(name: 'min_valuation')
  final double? minValuation;
  @override
  @JsonKey(name: 'max_valuation')
  final double? maxValuation;
  @override
  @JsonKey(name: 'min_funding')
  final double? minFunding;
  @override
  @JsonKey(name: 'max_funding')
  final double? maxFunding;
  @override
  final String? location;
  @override
  final String? search;
  @override
  @JsonKey(name: 'sort_by')
  final String sortBy;
  @override
  @JsonKey(name: 'sort_order')
  final String sortOrder;

  @override
  String toString() {
    return 'BusinessFilters(industry: $industry, minValuation: $minValuation, maxValuation: $maxValuation, minFunding: $minFunding, maxFunding: $maxFunding, location: $location, search: $search, sortBy: $sortBy, sortOrder: $sortOrder)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$BusinessFiltersImpl &&
            (identical(other.industry, industry) ||
                other.industry == industry) &&
            (identical(other.minValuation, minValuation) ||
                other.minValuation == minValuation) &&
            (identical(other.maxValuation, maxValuation) ||
                other.maxValuation == maxValuation) &&
            (identical(other.minFunding, minFunding) ||
                other.minFunding == minFunding) &&
            (identical(other.maxFunding, maxFunding) ||
                other.maxFunding == maxFunding) &&
            (identical(other.location, location) ||
                other.location == location) &&
            (identical(other.search, search) || other.search == search) &&
            (identical(other.sortBy, sortBy) || other.sortBy == sortBy) &&
            (identical(other.sortOrder, sortOrder) ||
                other.sortOrder == sortOrder));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      industry,
      minValuation,
      maxValuation,
      minFunding,
      maxFunding,
      location,
      search,
      sortBy,
      sortOrder);

  /// Create a copy of BusinessFilters
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$BusinessFiltersImplCopyWith<_$BusinessFiltersImpl> get copyWith =>
      __$$BusinessFiltersImplCopyWithImpl<_$BusinessFiltersImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$BusinessFiltersImplToJson(
      this,
    );
  }
}

abstract class _BusinessFilters implements BusinessFilters {
  const factory _BusinessFilters(
          {final String? industry,
          @JsonKey(name: 'min_valuation') final double? minValuation,
          @JsonKey(name: 'max_valuation') final double? maxValuation,
          @JsonKey(name: 'min_funding') final double? minFunding,
          @JsonKey(name: 'max_funding') final double? maxFunding,
          final String? location,
          final String? search,
          @JsonKey(name: 'sort_by') final String sortBy,
          @JsonKey(name: 'sort_order') final String sortOrder}) =
      _$BusinessFiltersImpl;

  factory _BusinessFilters.fromJson(Map<String, dynamic> json) =
      _$BusinessFiltersImpl.fromJson;

  @override
  String? get industry;
  @override
  @JsonKey(name: 'min_valuation')
  double? get minValuation;
  @override
  @JsonKey(name: 'max_valuation')
  double? get maxValuation;
  @override
  @JsonKey(name: 'min_funding')
  double? get minFunding;
  @override
  @JsonKey(name: 'max_funding')
  double? get maxFunding;
  @override
  String? get location;
  @override
  String? get search;
  @override
  @JsonKey(name: 'sort_by')
  String get sortBy;
  @override
  @JsonKey(name: 'sort_order')
  String get sortOrder;

  /// Create a copy of BusinessFilters
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$BusinessFiltersImplCopyWith<_$BusinessFiltersImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

MarketplaceStats _$MarketplaceStatsFromJson(Map<String, dynamic> json) {
  return _MarketplaceStats.fromJson(json);
}

/// @nodoc
mixin _$MarketplaceStats {
  @JsonKey(name: 'total_businesses')
  int get totalBusinesses => throw _privateConstructorUsedError;
  @JsonKey(name: 'total_investments')
  int get totalInvestments => throw _privateConstructorUsedError;
  @JsonKey(name: 'total_investment_volume')
  double get totalInvestmentVolume => throw _privateConstructorUsedError;
  @JsonKey(name: 'active_investors')
  int get activeInvestors => throw _privateConstructorUsedError;
  @JsonKey(name: 'average_investment')
  double get averageInvestment => throw _privateConstructorUsedError;
  @JsonKey(name: 'top_industries')
  List<String> get topIndustries => throw _privateConstructorUsedError;

  /// Serializes this MarketplaceStats to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of MarketplaceStats
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $MarketplaceStatsCopyWith<MarketplaceStats> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $MarketplaceStatsCopyWith<$Res> {
  factory $MarketplaceStatsCopyWith(
          MarketplaceStats value, $Res Function(MarketplaceStats) then) =
      _$MarketplaceStatsCopyWithImpl<$Res, MarketplaceStats>;
  @useResult
  $Res call(
      {@JsonKey(name: 'total_businesses') int totalBusinesses,
      @JsonKey(name: 'total_investments') int totalInvestments,
      @JsonKey(name: 'total_investment_volume') double totalInvestmentVolume,
      @JsonKey(name: 'active_investors') int activeInvestors,
      @JsonKey(name: 'average_investment') double averageInvestment,
      @JsonKey(name: 'top_industries') List<String> topIndustries});
}

/// @nodoc
class _$MarketplaceStatsCopyWithImpl<$Res, $Val extends MarketplaceStats>
    implements $MarketplaceStatsCopyWith<$Res> {
  _$MarketplaceStatsCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of MarketplaceStats
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? totalBusinesses = null,
    Object? totalInvestments = null,
    Object? totalInvestmentVolume = null,
    Object? activeInvestors = null,
    Object? averageInvestment = null,
    Object? topIndustries = null,
  }) {
    return _then(_value.copyWith(
      totalBusinesses: null == totalBusinesses
          ? _value.totalBusinesses
          : totalBusinesses // ignore: cast_nullable_to_non_nullable
              as int,
      totalInvestments: null == totalInvestments
          ? _value.totalInvestments
          : totalInvestments // ignore: cast_nullable_to_non_nullable
              as int,
      totalInvestmentVolume: null == totalInvestmentVolume
          ? _value.totalInvestmentVolume
          : totalInvestmentVolume // ignore: cast_nullable_to_non_nullable
              as double,
      activeInvestors: null == activeInvestors
          ? _value.activeInvestors
          : activeInvestors // ignore: cast_nullable_to_non_nullable
              as int,
      averageInvestment: null == averageInvestment
          ? _value.averageInvestment
          : averageInvestment // ignore: cast_nullable_to_non_nullable
              as double,
      topIndustries: null == topIndustries
          ? _value.topIndustries
          : topIndustries // ignore: cast_nullable_to_non_nullable
              as List<String>,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$MarketplaceStatsImplCopyWith<$Res>
    implements $MarketplaceStatsCopyWith<$Res> {
  factory _$$MarketplaceStatsImplCopyWith(_$MarketplaceStatsImpl value,
          $Res Function(_$MarketplaceStatsImpl) then) =
      __$$MarketplaceStatsImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {@JsonKey(name: 'total_businesses') int totalBusinesses,
      @JsonKey(name: 'total_investments') int totalInvestments,
      @JsonKey(name: 'total_investment_volume') double totalInvestmentVolume,
      @JsonKey(name: 'active_investors') int activeInvestors,
      @JsonKey(name: 'average_investment') double averageInvestment,
      @JsonKey(name: 'top_industries') List<String> topIndustries});
}

/// @nodoc
class __$$MarketplaceStatsImplCopyWithImpl<$Res>
    extends _$MarketplaceStatsCopyWithImpl<$Res, _$MarketplaceStatsImpl>
    implements _$$MarketplaceStatsImplCopyWith<$Res> {
  __$$MarketplaceStatsImplCopyWithImpl(_$MarketplaceStatsImpl _value,
      $Res Function(_$MarketplaceStatsImpl) _then)
      : super(_value, _then);

  /// Create a copy of MarketplaceStats
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? totalBusinesses = null,
    Object? totalInvestments = null,
    Object? totalInvestmentVolume = null,
    Object? activeInvestors = null,
    Object? averageInvestment = null,
    Object? topIndustries = null,
  }) {
    return _then(_$MarketplaceStatsImpl(
      totalBusinesses: null == totalBusinesses
          ? _value.totalBusinesses
          : totalBusinesses // ignore: cast_nullable_to_non_nullable
              as int,
      totalInvestments: null == totalInvestments
          ? _value.totalInvestments
          : totalInvestments // ignore: cast_nullable_to_non_nullable
              as int,
      totalInvestmentVolume: null == totalInvestmentVolume
          ? _value.totalInvestmentVolume
          : totalInvestmentVolume // ignore: cast_nullable_to_non_nullable
              as double,
      activeInvestors: null == activeInvestors
          ? _value.activeInvestors
          : activeInvestors // ignore: cast_nullable_to_non_nullable
              as int,
      averageInvestment: null == averageInvestment
          ? _value.averageInvestment
          : averageInvestment // ignore: cast_nullable_to_non_nullable
              as double,
      topIndustries: null == topIndustries
          ? _value._topIndustries
          : topIndustries // ignore: cast_nullable_to_non_nullable
              as List<String>,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$MarketplaceStatsImpl implements _MarketplaceStats {
  const _$MarketplaceStatsImpl(
      {@JsonKey(name: 'total_businesses') this.totalBusinesses = 0,
      @JsonKey(name: 'total_investments') this.totalInvestments = 0,
      @JsonKey(name: 'total_investment_volume')
      this.totalInvestmentVolume = 0.0,
      @JsonKey(name: 'active_investors') this.activeInvestors = 0,
      @JsonKey(name: 'average_investment') this.averageInvestment = 0.0,
      @JsonKey(name: 'top_industries')
      final List<String> topIndustries = const []})
      : _topIndustries = topIndustries;

  factory _$MarketplaceStatsImpl.fromJson(Map<String, dynamic> json) =>
      _$$MarketplaceStatsImplFromJson(json);

  @override
  @JsonKey(name: 'total_businesses')
  final int totalBusinesses;
  @override
  @JsonKey(name: 'total_investments')
  final int totalInvestments;
  @override
  @JsonKey(name: 'total_investment_volume')
  final double totalInvestmentVolume;
  @override
  @JsonKey(name: 'active_investors')
  final int activeInvestors;
  @override
  @JsonKey(name: 'average_investment')
  final double averageInvestment;
  final List<String> _topIndustries;
  @override
  @JsonKey(name: 'top_industries')
  List<String> get topIndustries {
    if (_topIndustries is EqualUnmodifiableListView) return _topIndustries;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_topIndustries);
  }

  @override
  String toString() {
    return 'MarketplaceStats(totalBusinesses: $totalBusinesses, totalInvestments: $totalInvestments, totalInvestmentVolume: $totalInvestmentVolume, activeInvestors: $activeInvestors, averageInvestment: $averageInvestment, topIndustries: $topIndustries)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$MarketplaceStatsImpl &&
            (identical(other.totalBusinesses, totalBusinesses) ||
                other.totalBusinesses == totalBusinesses) &&
            (identical(other.totalInvestments, totalInvestments) ||
                other.totalInvestments == totalInvestments) &&
            (identical(other.totalInvestmentVolume, totalInvestmentVolume) ||
                other.totalInvestmentVolume == totalInvestmentVolume) &&
            (identical(other.activeInvestors, activeInvestors) ||
                other.activeInvestors == activeInvestors) &&
            (identical(other.averageInvestment, averageInvestment) ||
                other.averageInvestment == averageInvestment) &&
            const DeepCollectionEquality()
                .equals(other._topIndustries, _topIndustries));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      totalBusinesses,
      totalInvestments,
      totalInvestmentVolume,
      activeInvestors,
      averageInvestment,
      const DeepCollectionEquality().hash(_topIndustries));

  /// Create a copy of MarketplaceStats
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$MarketplaceStatsImplCopyWith<_$MarketplaceStatsImpl> get copyWith =>
      __$$MarketplaceStatsImplCopyWithImpl<_$MarketplaceStatsImpl>(
          this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$MarketplaceStatsImplToJson(
      this,
    );
  }
}

abstract class _MarketplaceStats implements MarketplaceStats {
  const factory _MarketplaceStats(
          {@JsonKey(name: 'total_businesses') final int totalBusinesses,
          @JsonKey(name: 'total_investments') final int totalInvestments,
          @JsonKey(name: 'total_investment_volume')
          final double totalInvestmentVolume,
          @JsonKey(name: 'active_investors') final int activeInvestors,
          @JsonKey(name: 'average_investment') final double averageInvestment,
          @JsonKey(name: 'top_industries') final List<String> topIndustries}) =
      _$MarketplaceStatsImpl;

  factory _MarketplaceStats.fromJson(Map<String, dynamic> json) =
      _$MarketplaceStatsImpl.fromJson;

  @override
  @JsonKey(name: 'total_businesses')
  int get totalBusinesses;
  @override
  @JsonKey(name: 'total_investments')
  int get totalInvestments;
  @override
  @JsonKey(name: 'total_investment_volume')
  double get totalInvestmentVolume;
  @override
  @JsonKey(name: 'active_investors')
  int get activeInvestors;
  @override
  @JsonKey(name: 'average_investment')
  double get averageInvestment;
  @override
  @JsonKey(name: 'top_industries')
  List<String> get topIndustries;

  /// Create a copy of MarketplaceStats
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$MarketplaceStatsImplCopyWith<_$MarketplaceStatsImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

PaymentIntent _$PaymentIntentFromJson(Map<String, dynamic> json) {
  return _PaymentIntent.fromJson(json);
}

/// @nodoc
mixin _$PaymentIntent {
  String get id => throw _privateConstructorUsedError;
  @JsonKey(name: 'client_secret')
  String get clientSecret => throw _privateConstructorUsedError;
  double get amount => throw _privateConstructorUsedError;
  String get currency => throw _privateConstructorUsedError;
  String get status => throw _privateConstructorUsedError;
  @JsonKey(name: 'payment_method_types')
  List<String> get paymentMethodTypes => throw _privateConstructorUsedError;

  /// Serializes this PaymentIntent to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of PaymentIntent
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $PaymentIntentCopyWith<PaymentIntent> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $PaymentIntentCopyWith<$Res> {
  factory $PaymentIntentCopyWith(
          PaymentIntent value, $Res Function(PaymentIntent) then) =
      _$PaymentIntentCopyWithImpl<$Res, PaymentIntent>;
  @useResult
  $Res call(
      {String id,
      @JsonKey(name: 'client_secret') String clientSecret,
      double amount,
      String currency,
      String status,
      @JsonKey(name: 'payment_method_types') List<String> paymentMethodTypes});
}

/// @nodoc
class _$PaymentIntentCopyWithImpl<$Res, $Val extends PaymentIntent>
    implements $PaymentIntentCopyWith<$Res> {
  _$PaymentIntentCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of PaymentIntent
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? clientSecret = null,
    Object? amount = null,
    Object? currency = null,
    Object? status = null,
    Object? paymentMethodTypes = null,
  }) {
    return _then(_value.copyWith(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as String,
      clientSecret: null == clientSecret
          ? _value.clientSecret
          : clientSecret // ignore: cast_nullable_to_non_nullable
              as String,
      amount: null == amount
          ? _value.amount
          : amount // ignore: cast_nullable_to_non_nullable
              as double,
      currency: null == currency
          ? _value.currency
          : currency // ignore: cast_nullable_to_non_nullable
              as String,
      status: null == status
          ? _value.status
          : status // ignore: cast_nullable_to_non_nullable
              as String,
      paymentMethodTypes: null == paymentMethodTypes
          ? _value.paymentMethodTypes
          : paymentMethodTypes // ignore: cast_nullable_to_non_nullable
              as List<String>,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$PaymentIntentImplCopyWith<$Res>
    implements $PaymentIntentCopyWith<$Res> {
  factory _$$PaymentIntentImplCopyWith(
          _$PaymentIntentImpl value, $Res Function(_$PaymentIntentImpl) then) =
      __$$PaymentIntentImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {String id,
      @JsonKey(name: 'client_secret') String clientSecret,
      double amount,
      String currency,
      String status,
      @JsonKey(name: 'payment_method_types') List<String> paymentMethodTypes});
}

/// @nodoc
class __$$PaymentIntentImplCopyWithImpl<$Res>
    extends _$PaymentIntentCopyWithImpl<$Res, _$PaymentIntentImpl>
    implements _$$PaymentIntentImplCopyWith<$Res> {
  __$$PaymentIntentImplCopyWithImpl(
      _$PaymentIntentImpl _value, $Res Function(_$PaymentIntentImpl) _then)
      : super(_value, _then);

  /// Create a copy of PaymentIntent
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? clientSecret = null,
    Object? amount = null,
    Object? currency = null,
    Object? status = null,
    Object? paymentMethodTypes = null,
  }) {
    return _then(_$PaymentIntentImpl(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as String,
      clientSecret: null == clientSecret
          ? _value.clientSecret
          : clientSecret // ignore: cast_nullable_to_non_nullable
              as String,
      amount: null == amount
          ? _value.amount
          : amount // ignore: cast_nullable_to_non_nullable
              as double,
      currency: null == currency
          ? _value.currency
          : currency // ignore: cast_nullable_to_non_nullable
              as String,
      status: null == status
          ? _value.status
          : status // ignore: cast_nullable_to_non_nullable
              as String,
      paymentMethodTypes: null == paymentMethodTypes
          ? _value._paymentMethodTypes
          : paymentMethodTypes // ignore: cast_nullable_to_non_nullable
              as List<String>,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$PaymentIntentImpl implements _PaymentIntent {
  const _$PaymentIntentImpl(
      {required this.id,
      @JsonKey(name: 'client_secret') required this.clientSecret,
      required this.amount,
      required this.currency,
      required this.status,
      @JsonKey(name: 'payment_method_types')
      final List<String> paymentMethodTypes = const []})
      : _paymentMethodTypes = paymentMethodTypes;

  factory _$PaymentIntentImpl.fromJson(Map<String, dynamic> json) =>
      _$$PaymentIntentImplFromJson(json);

  @override
  final String id;
  @override
  @JsonKey(name: 'client_secret')
  final String clientSecret;
  @override
  final double amount;
  @override
  final String currency;
  @override
  final String status;
  final List<String> _paymentMethodTypes;
  @override
  @JsonKey(name: 'payment_method_types')
  List<String> get paymentMethodTypes {
    if (_paymentMethodTypes is EqualUnmodifiableListView)
      return _paymentMethodTypes;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_paymentMethodTypes);
  }

  @override
  String toString() {
    return 'PaymentIntent(id: $id, clientSecret: $clientSecret, amount: $amount, currency: $currency, status: $status, paymentMethodTypes: $paymentMethodTypes)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$PaymentIntentImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.clientSecret, clientSecret) ||
                other.clientSecret == clientSecret) &&
            (identical(other.amount, amount) || other.amount == amount) &&
            (identical(other.currency, currency) ||
                other.currency == currency) &&
            (identical(other.status, status) || other.status == status) &&
            const DeepCollectionEquality()
                .equals(other._paymentMethodTypes, _paymentMethodTypes));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      id,
      clientSecret,
      amount,
      currency,
      status,
      const DeepCollectionEquality().hash(_paymentMethodTypes));

  /// Create a copy of PaymentIntent
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$PaymentIntentImplCopyWith<_$PaymentIntentImpl> get copyWith =>
      __$$PaymentIntentImplCopyWithImpl<_$PaymentIntentImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$PaymentIntentImplToJson(
      this,
    );
  }
}

abstract class _PaymentIntent implements PaymentIntent {
  const factory _PaymentIntent(
      {required final String id,
      @JsonKey(name: 'client_secret') required final String clientSecret,
      required final double amount,
      required final String currency,
      required final String status,
      @JsonKey(name: 'payment_method_types')
      final List<String> paymentMethodTypes}) = _$PaymentIntentImpl;

  factory _PaymentIntent.fromJson(Map<String, dynamic> json) =
      _$PaymentIntentImpl.fromJson;

  @override
  String get id;
  @override
  @JsonKey(name: 'client_secret')
  String get clientSecret;
  @override
  double get amount;
  @override
  String get currency;
  @override
  String get status;
  @override
  @JsonKey(name: 'payment_method_types')
  List<String> get paymentMethodTypes;

  /// Create a copy of PaymentIntent
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$PaymentIntentImplCopyWith<_$PaymentIntentImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

Transaction _$TransactionFromJson(Map<String, dynamic> json) {
  return _Transaction.fromJson(json);
}

/// @nodoc
mixin _$Transaction {
  String get id => throw _privateConstructorUsedError;
  @JsonKey(name: 'user_id')
  String get userId => throw _privateConstructorUsedError;
  @JsonKey(name: 'listing_id')
  String? get listingId => throw _privateConstructorUsedError;
  @JsonKey(name: 'listing_type')
  String? get listingType => throw _privateConstructorUsedError;
  double get amount => throw _privateConstructorUsedError;
  String get currency => throw _privateConstructorUsedError;
  String get type => throw _privateConstructorUsedError;
  String get status => throw _privateConstructorUsedError;
  @JsonKey(name: 'stripe_payment_intent_id')
  String? get stripePaymentIntentId => throw _privateConstructorUsedError;
  @JsonKey(name: 'payment_method')
  String? get paymentMethod => throw _privateConstructorUsedError;
  String? get description => throw _privateConstructorUsedError;
  @JsonKey(name: 'created_at')
  DateTime get createdAt => throw _privateConstructorUsedError;
  @JsonKey(name: 'updated_at')
  DateTime get updatedAt => throw _privateConstructorUsedError;

  /// Serializes this Transaction to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of Transaction
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $TransactionCopyWith<Transaction> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $TransactionCopyWith<$Res> {
  factory $TransactionCopyWith(
          Transaction value, $Res Function(Transaction) then) =
      _$TransactionCopyWithImpl<$Res, Transaction>;
  @useResult
  $Res call(
      {String id,
      @JsonKey(name: 'user_id') String userId,
      @JsonKey(name: 'listing_id') String? listingId,
      @JsonKey(name: 'listing_type') String? listingType,
      double amount,
      String currency,
      String type,
      String status,
      @JsonKey(name: 'stripe_payment_intent_id') String? stripePaymentIntentId,
      @JsonKey(name: 'payment_method') String? paymentMethod,
      String? description,
      @JsonKey(name: 'created_at') DateTime createdAt,
      @JsonKey(name: 'updated_at') DateTime updatedAt});
}

/// @nodoc
class _$TransactionCopyWithImpl<$Res, $Val extends Transaction>
    implements $TransactionCopyWith<$Res> {
  _$TransactionCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of Transaction
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? userId = null,
    Object? listingId = freezed,
    Object? listingType = freezed,
    Object? amount = null,
    Object? currency = null,
    Object? type = null,
    Object? status = null,
    Object? stripePaymentIntentId = freezed,
    Object? paymentMethod = freezed,
    Object? description = freezed,
    Object? createdAt = null,
    Object? updatedAt = null,
  }) {
    return _then(_value.copyWith(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as String,
      userId: null == userId
          ? _value.userId
          : userId // ignore: cast_nullable_to_non_nullable
              as String,
      listingId: freezed == listingId
          ? _value.listingId
          : listingId // ignore: cast_nullable_to_non_nullable
              as String?,
      listingType: freezed == listingType
          ? _value.listingType
          : listingType // ignore: cast_nullable_to_non_nullable
              as String?,
      amount: null == amount
          ? _value.amount
          : amount // ignore: cast_nullable_to_non_nullable
              as double,
      currency: null == currency
          ? _value.currency
          : currency // ignore: cast_nullable_to_non_nullable
              as String,
      type: null == type
          ? _value.type
          : type // ignore: cast_nullable_to_non_nullable
              as String,
      status: null == status
          ? _value.status
          : status // ignore: cast_nullable_to_non_nullable
              as String,
      stripePaymentIntentId: freezed == stripePaymentIntentId
          ? _value.stripePaymentIntentId
          : stripePaymentIntentId // ignore: cast_nullable_to_non_nullable
              as String?,
      paymentMethod: freezed == paymentMethod
          ? _value.paymentMethod
          : paymentMethod // ignore: cast_nullable_to_non_nullable
              as String?,
      description: freezed == description
          ? _value.description
          : description // ignore: cast_nullable_to_non_nullable
              as String?,
      createdAt: null == createdAt
          ? _value.createdAt
          : createdAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
      updatedAt: null == updatedAt
          ? _value.updatedAt
          : updatedAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$TransactionImplCopyWith<$Res>
    implements $TransactionCopyWith<$Res> {
  factory _$$TransactionImplCopyWith(
          _$TransactionImpl value, $Res Function(_$TransactionImpl) then) =
      __$$TransactionImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {String id,
      @JsonKey(name: 'user_id') String userId,
      @JsonKey(name: 'listing_id') String? listingId,
      @JsonKey(name: 'listing_type') String? listingType,
      double amount,
      String currency,
      String type,
      String status,
      @JsonKey(name: 'stripe_payment_intent_id') String? stripePaymentIntentId,
      @JsonKey(name: 'payment_method') String? paymentMethod,
      String? description,
      @JsonKey(name: 'created_at') DateTime createdAt,
      @JsonKey(name: 'updated_at') DateTime updatedAt});
}

/// @nodoc
class __$$TransactionImplCopyWithImpl<$Res>
    extends _$TransactionCopyWithImpl<$Res, _$TransactionImpl>
    implements _$$TransactionImplCopyWith<$Res> {
  __$$TransactionImplCopyWithImpl(
      _$TransactionImpl _value, $Res Function(_$TransactionImpl) _then)
      : super(_value, _then);

  /// Create a copy of Transaction
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? userId = null,
    Object? listingId = freezed,
    Object? listingType = freezed,
    Object? amount = null,
    Object? currency = null,
    Object? type = null,
    Object? status = null,
    Object? stripePaymentIntentId = freezed,
    Object? paymentMethod = freezed,
    Object? description = freezed,
    Object? createdAt = null,
    Object? updatedAt = null,
  }) {
    return _then(_$TransactionImpl(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as String,
      userId: null == userId
          ? _value.userId
          : userId // ignore: cast_nullable_to_non_nullable
              as String,
      listingId: freezed == listingId
          ? _value.listingId
          : listingId // ignore: cast_nullable_to_non_nullable
              as String?,
      listingType: freezed == listingType
          ? _value.listingType
          : listingType // ignore: cast_nullable_to_non_nullable
              as String?,
      amount: null == amount
          ? _value.amount
          : amount // ignore: cast_nullable_to_non_nullable
              as double,
      currency: null == currency
          ? _value.currency
          : currency // ignore: cast_nullable_to_non_nullable
              as String,
      type: null == type
          ? _value.type
          : type // ignore: cast_nullable_to_non_nullable
              as String,
      status: null == status
          ? _value.status
          : status // ignore: cast_nullable_to_non_nullable
              as String,
      stripePaymentIntentId: freezed == stripePaymentIntentId
          ? _value.stripePaymentIntentId
          : stripePaymentIntentId // ignore: cast_nullable_to_non_nullable
              as String?,
      paymentMethod: freezed == paymentMethod
          ? _value.paymentMethod
          : paymentMethod // ignore: cast_nullable_to_non_nullable
              as String?,
      description: freezed == description
          ? _value.description
          : description // ignore: cast_nullable_to_non_nullable
              as String?,
      createdAt: null == createdAt
          ? _value.createdAt
          : createdAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
      updatedAt: null == updatedAt
          ? _value.updatedAt
          : updatedAt // ignore: cast_nullable_to_non_nullable
              as DateTime,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$TransactionImpl implements _Transaction {
  const _$TransactionImpl(
      {required this.id,
      @JsonKey(name: 'user_id') required this.userId,
      @JsonKey(name: 'listing_id') this.listingId,
      @JsonKey(name: 'listing_type') this.listingType,
      required this.amount,
      required this.currency,
      required this.type,
      required this.status,
      @JsonKey(name: 'stripe_payment_intent_id') this.stripePaymentIntentId,
      @JsonKey(name: 'payment_method') this.paymentMethod,
      this.description,
      @JsonKey(name: 'created_at') required this.createdAt,
      @JsonKey(name: 'updated_at') required this.updatedAt});

  factory _$TransactionImpl.fromJson(Map<String, dynamic> json) =>
      _$$TransactionImplFromJson(json);

  @override
  final String id;
  @override
  @JsonKey(name: 'user_id')
  final String userId;
  @override
  @JsonKey(name: 'listing_id')
  final String? listingId;
  @override
  @JsonKey(name: 'listing_type')
  final String? listingType;
  @override
  final double amount;
  @override
  final String currency;
  @override
  final String type;
  @override
  final String status;
  @override
  @JsonKey(name: 'stripe_payment_intent_id')
  final String? stripePaymentIntentId;
  @override
  @JsonKey(name: 'payment_method')
  final String? paymentMethod;
  @override
  final String? description;
  @override
  @JsonKey(name: 'created_at')
  final DateTime createdAt;
  @override
  @JsonKey(name: 'updated_at')
  final DateTime updatedAt;

  @override
  String toString() {
    return 'Transaction(id: $id, userId: $userId, listingId: $listingId, listingType: $listingType, amount: $amount, currency: $currency, type: $type, status: $status, stripePaymentIntentId: $stripePaymentIntentId, paymentMethod: $paymentMethod, description: $description, createdAt: $createdAt, updatedAt: $updatedAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$TransactionImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.userId, userId) || other.userId == userId) &&
            (identical(other.listingId, listingId) ||
                other.listingId == listingId) &&
            (identical(other.listingType, listingType) ||
                other.listingType == listingType) &&
            (identical(other.amount, amount) || other.amount == amount) &&
            (identical(other.currency, currency) ||
                other.currency == currency) &&
            (identical(other.type, type) || other.type == type) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.stripePaymentIntentId, stripePaymentIntentId) ||
                other.stripePaymentIntentId == stripePaymentIntentId) &&
            (identical(other.paymentMethod, paymentMethod) ||
                other.paymentMethod == paymentMethod) &&
            (identical(other.description, description) ||
                other.description == description) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt) &&
            (identical(other.updatedAt, updatedAt) ||
                other.updatedAt == updatedAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      id,
      userId,
      listingId,
      listingType,
      amount,
      currency,
      type,
      status,
      stripePaymentIntentId,
      paymentMethod,
      description,
      createdAt,
      updatedAt);

  /// Create a copy of Transaction
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$TransactionImplCopyWith<_$TransactionImpl> get copyWith =>
      __$$TransactionImplCopyWithImpl<_$TransactionImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$TransactionImplToJson(
      this,
    );
  }
}

abstract class _Transaction implements Transaction {
  const factory _Transaction(
          {required final String id,
          @JsonKey(name: 'user_id') required final String userId,
          @JsonKey(name: 'listing_id') final String? listingId,
          @JsonKey(name: 'listing_type') final String? listingType,
          required final double amount,
          required final String currency,
          required final String type,
          required final String status,
          @JsonKey(name: 'stripe_payment_intent_id')
          final String? stripePaymentIntentId,
          @JsonKey(name: 'payment_method') final String? paymentMethod,
          final String? description,
          @JsonKey(name: 'created_at') required final DateTime createdAt,
          @JsonKey(name: 'updated_at') required final DateTime updatedAt}) =
      _$TransactionImpl;

  factory _Transaction.fromJson(Map<String, dynamic> json) =
      _$TransactionImpl.fromJson;

  @override
  String get id;
  @override
  @JsonKey(name: 'user_id')
  String get userId;
  @override
  @JsonKey(name: 'listing_id')
  String? get listingId;
  @override
  @JsonKey(name: 'listing_type')
  String? get listingType;
  @override
  double get amount;
  @override
  String get currency;
  @override
  String get type;
  @override
  String get status;
  @override
  @JsonKey(name: 'stripe_payment_intent_id')
  String? get stripePaymentIntentId;
  @override
  @JsonKey(name: 'payment_method')
  String? get paymentMethod;
  @override
  String? get description;
  @override
  @JsonKey(name: 'created_at')
  DateTime get createdAt;
  @override
  @JsonKey(name: 'updated_at')
  DateTime get updatedAt;

  /// Create a copy of Transaction
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$TransactionImplCopyWith<_$TransactionImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

MarketplaceApiResponse<T> _$MarketplaceApiResponseFromJson<T>(
    Map<String, dynamic> json, T Function(Object?) fromJsonT) {
  return _MarketplaceApiResponse<T>.fromJson(json, fromJsonT);
}

/// @nodoc
mixin _$MarketplaceApiResponse<T> {
  bool get success => throw _privateConstructorUsedError;
  String? get message => throw _privateConstructorUsedError;
  T? get data => throw _privateConstructorUsedError;
  Map<String, dynamic>? get errors => throw _privateConstructorUsedError;
  @JsonKey(name: 'current_page')
  int? get currentPage => throw _privateConstructorUsedError;
  @JsonKey(name: 'last_page')
  int? get lastPage => throw _privateConstructorUsedError;
  @JsonKey(name: 'per_page')
  int? get perPage => throw _privateConstructorUsedError;
  int? get total => throw _privateConstructorUsedError;

  /// Serializes this MarketplaceApiResponse to a JSON map.
  Map<String, dynamic> toJson(Object? Function(T) toJsonT) =>
      throw _privateConstructorUsedError;

  /// Create a copy of MarketplaceApiResponse
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $MarketplaceApiResponseCopyWith<T, MarketplaceApiResponse<T>> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $MarketplaceApiResponseCopyWith<T, $Res> {
  factory $MarketplaceApiResponseCopyWith(MarketplaceApiResponse<T> value,
          $Res Function(MarketplaceApiResponse<T>) then) =
      _$MarketplaceApiResponseCopyWithImpl<T, $Res, MarketplaceApiResponse<T>>;
  @useResult
  $Res call(
      {bool success,
      String? message,
      T? data,
      Map<String, dynamic>? errors,
      @JsonKey(name: 'current_page') int? currentPage,
      @JsonKey(name: 'last_page') int? lastPage,
      @JsonKey(name: 'per_page') int? perPage,
      int? total});
}

/// @nodoc
class _$MarketplaceApiResponseCopyWithImpl<T, $Res,
        $Val extends MarketplaceApiResponse<T>>
    implements $MarketplaceApiResponseCopyWith<T, $Res> {
  _$MarketplaceApiResponseCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of MarketplaceApiResponse
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? success = null,
    Object? message = freezed,
    Object? data = freezed,
    Object? errors = freezed,
    Object? currentPage = freezed,
    Object? lastPage = freezed,
    Object? perPage = freezed,
    Object? total = freezed,
  }) {
    return _then(_value.copyWith(
      success: null == success
          ? _value.success
          : success // ignore: cast_nullable_to_non_nullable
              as bool,
      message: freezed == message
          ? _value.message
          : message // ignore: cast_nullable_to_non_nullable
              as String?,
      data: freezed == data
          ? _value.data
          : data // ignore: cast_nullable_to_non_nullable
              as T?,
      errors: freezed == errors
          ? _value.errors
          : errors // ignore: cast_nullable_to_non_nullable
              as Map<String, dynamic>?,
      currentPage: freezed == currentPage
          ? _value.currentPage
          : currentPage // ignore: cast_nullable_to_non_nullable
              as int?,
      lastPage: freezed == lastPage
          ? _value.lastPage
          : lastPage // ignore: cast_nullable_to_non_nullable
              as int?,
      perPage: freezed == perPage
          ? _value.perPage
          : perPage // ignore: cast_nullable_to_non_nullable
              as int?,
      total: freezed == total
          ? _value.total
          : total // ignore: cast_nullable_to_non_nullable
              as int?,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$MarketplaceApiResponseImplCopyWith<T, $Res>
    implements $MarketplaceApiResponseCopyWith<T, $Res> {
  factory _$$MarketplaceApiResponseImplCopyWith(
          _$MarketplaceApiResponseImpl<T> value,
          $Res Function(_$MarketplaceApiResponseImpl<T>) then) =
      __$$MarketplaceApiResponseImplCopyWithImpl<T, $Res>;
  @override
  @useResult
  $Res call(
      {bool success,
      String? message,
      T? data,
      Map<String, dynamic>? errors,
      @JsonKey(name: 'current_page') int? currentPage,
      @JsonKey(name: 'last_page') int? lastPage,
      @JsonKey(name: 'per_page') int? perPage,
      int? total});
}

/// @nodoc
class __$$MarketplaceApiResponseImplCopyWithImpl<T, $Res>
    extends _$MarketplaceApiResponseCopyWithImpl<T, $Res,
        _$MarketplaceApiResponseImpl<T>>
    implements _$$MarketplaceApiResponseImplCopyWith<T, $Res> {
  __$$MarketplaceApiResponseImplCopyWithImpl(
      _$MarketplaceApiResponseImpl<T> _value,
      $Res Function(_$MarketplaceApiResponseImpl<T>) _then)
      : super(_value, _then);

  /// Create a copy of MarketplaceApiResponse
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? success = null,
    Object? message = freezed,
    Object? data = freezed,
    Object? errors = freezed,
    Object? currentPage = freezed,
    Object? lastPage = freezed,
    Object? perPage = freezed,
    Object? total = freezed,
  }) {
    return _then(_$MarketplaceApiResponseImpl<T>(
      success: null == success
          ? _value.success
          : success // ignore: cast_nullable_to_non_nullable
              as bool,
      message: freezed == message
          ? _value.message
          : message // ignore: cast_nullable_to_non_nullable
              as String?,
      data: freezed == data
          ? _value.data
          : data // ignore: cast_nullable_to_non_nullable
              as T?,
      errors: freezed == errors
          ? _value._errors
          : errors // ignore: cast_nullable_to_non_nullable
              as Map<String, dynamic>?,
      currentPage: freezed == currentPage
          ? _value.currentPage
          : currentPage // ignore: cast_nullable_to_non_nullable
              as int?,
      lastPage: freezed == lastPage
          ? _value.lastPage
          : lastPage // ignore: cast_nullable_to_non_nullable
              as int?,
      perPage: freezed == perPage
          ? _value.perPage
          : perPage // ignore: cast_nullable_to_non_nullable
              as int?,
      total: freezed == total
          ? _value.total
          : total // ignore: cast_nullable_to_non_nullable
              as int?,
    ));
  }
}

/// @nodoc
@JsonSerializable(genericArgumentFactories: true)
class _$MarketplaceApiResponseImpl<T> implements _MarketplaceApiResponse<T> {
  const _$MarketplaceApiResponseImpl(
      {required this.success,
      this.message,
      this.data,
      final Map<String, dynamic>? errors,
      @JsonKey(name: 'current_page') this.currentPage,
      @JsonKey(name: 'last_page') this.lastPage,
      @JsonKey(name: 'per_page') this.perPage,
      this.total})
      : _errors = errors;

  factory _$MarketplaceApiResponseImpl.fromJson(
          Map<String, dynamic> json, T Function(Object?) fromJsonT) =>
      _$$MarketplaceApiResponseImplFromJson(json, fromJsonT);

  @override
  final bool success;
  @override
  final String? message;
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
  @JsonKey(name: 'current_page')
  final int? currentPage;
  @override
  @JsonKey(name: 'last_page')
  final int? lastPage;
  @override
  @JsonKey(name: 'per_page')
  final int? perPage;
  @override
  final int? total;

  @override
  String toString() {
    return 'MarketplaceApiResponse<$T>(success: $success, message: $message, data: $data, errors: $errors, currentPage: $currentPage, lastPage: $lastPage, perPage: $perPage, total: $total)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$MarketplaceApiResponseImpl<T> &&
            (identical(other.success, success) || other.success == success) &&
            (identical(other.message, message) || other.message == message) &&
            const DeepCollectionEquality().equals(other.data, data) &&
            const DeepCollectionEquality().equals(other._errors, _errors) &&
            (identical(other.currentPage, currentPage) ||
                other.currentPage == currentPage) &&
            (identical(other.lastPage, lastPage) ||
                other.lastPage == lastPage) &&
            (identical(other.perPage, perPage) || other.perPage == perPage) &&
            (identical(other.total, total) || other.total == total));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      success,
      message,
      const DeepCollectionEquality().hash(data),
      const DeepCollectionEquality().hash(_errors),
      currentPage,
      lastPage,
      perPage,
      total);

  /// Create a copy of MarketplaceApiResponse
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$MarketplaceApiResponseImplCopyWith<T, _$MarketplaceApiResponseImpl<T>>
      get copyWith => __$$MarketplaceApiResponseImplCopyWithImpl<T,
          _$MarketplaceApiResponseImpl<T>>(this, _$identity);

  @override
  Map<String, dynamic> toJson(Object? Function(T) toJsonT) {
    return _$$MarketplaceApiResponseImplToJson<T>(this, toJsonT);
  }
}

abstract class _MarketplaceApiResponse<T> implements MarketplaceApiResponse<T> {
  const factory _MarketplaceApiResponse(
      {required final bool success,
      final String? message,
      final T? data,
      final Map<String, dynamic>? errors,
      @JsonKey(name: 'current_page') final int? currentPage,
      @JsonKey(name: 'last_page') final int? lastPage,
      @JsonKey(name: 'per_page') final int? perPage,
      final int? total}) = _$MarketplaceApiResponseImpl<T>;

  factory _MarketplaceApiResponse.fromJson(
          Map<String, dynamic> json, T Function(Object?) fromJsonT) =
      _$MarketplaceApiResponseImpl<T>.fromJson;

  @override
  bool get success;
  @override
  String? get message;
  @override
  T? get data;
  @override
  Map<String, dynamic>? get errors;
  @override
  @JsonKey(name: 'current_page')
  int? get currentPage;
  @override
  @JsonKey(name: 'last_page')
  int? get lastPage;
  @override
  @JsonKey(name: 'per_page')
  int? get perPage;
  @override
  int? get total;

  /// Create a copy of MarketplaceApiResponse
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$MarketplaceApiResponseImplCopyWith<T, _$MarketplaceApiResponseImpl<T>>
      get copyWith => throw _privateConstructorUsedError;
}
