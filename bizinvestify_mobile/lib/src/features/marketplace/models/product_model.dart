class Product {
  final int id;
  final String name;
  final String description;
  final double price;
  final String category;
  final String status;
  final List<String> images;
  final List<String> tags;
  final int sellerId;
  final String sellerName;
  final String? sellerAvatar;
  final double rating;
  final int reviewCount;
  final DateTime createdAt;
  final DateTime updatedAt;

  Product({
    required this.id,
    required this.name,
    required this.description,
    required this.price,
    required this.category,
    required this.status,
    required this.images,
    required this.tags,
    required this.sellerId,
    required this.sellerName,
    this.sellerAvatar,
    this.rating = 0.0,
    this.reviewCount = 0,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'],
      name: json['name'],
      description: json['description'] ?? '',
      price: double.parse(json['price']?.toString() ?? '0'),
      category: json['category'] ?? '',
      status: json['status'] ?? 'active',
      images: List<String>.from(json['images'] ?? []),
      tags: List<String>.from(json['tags'] ?? []),
      sellerId: json['seller_id'] ?? json['user_id'],
      sellerName: json['seller_name'] ?? json['user_name'] ?? 'Unknown Seller',
      sellerAvatar: json['seller_avatar'] ?? json['user_avatar'],
      rating: double.parse(json['rating']?.toString() ?? '0'),
      reviewCount: json['review_count'] ?? 0,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'price': price,
      'category': category,
      'status': status,
      'images': images,
      'tags': tags,
      'seller_id': sellerId,
      'seller_name': sellerName,
      'seller_avatar': sellerAvatar,
      'rating': rating,
      'review_count': reviewCount,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  Product copyWith({
    int? id,
    String? name,
    String? description,
    double? price,
    String? category,
    String? status,
    List<String>? images,
    List<String>? tags,
    int? sellerId,
    String? sellerName,
    String? sellerAvatar,
    double? rating,
    int? reviewCount,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Product(
      id: id ?? this.id,
      name: name ?? this.name,
      description: description ?? this.description,
      price: price ?? this.price,
      category: category ?? this.category,
      status: status ?? this.status,
      images: images ?? this.images,
      tags: tags ?? this.tags,
      sellerId: sellerId ?? this.sellerId,
      sellerName: sellerName ?? this.sellerName,
      sellerAvatar: sellerAvatar ?? this.sellerAvatar,
      rating: rating ?? this.rating,
      reviewCount: reviewCount ?? this.reviewCount,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  // Helper methods
  String get formattedPrice => '\$${price.toStringAsFixed(2)}';
  String get mainImage => images.isNotEmpty ? images.first : '';
  bool get isActive => status == 'active';
  bool get hasImages => images.isNotEmpty;
  bool get hasRating => rating > 0;
}
