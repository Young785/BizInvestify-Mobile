// User model is defined inline

class Conversation {
  final int id;
  final User recipient;
  final String? lastMessage;
  final DateTime? lastMessageTime;
  final int unreadCount;
  final bool isActive;
  final DateTime createdAt;
  final DateTime updatedAt;

  Conversation({
    required this.id,
    required this.recipient,
    this.lastMessage,
    this.lastMessageTime,
    this.unreadCount = 0,
    this.isActive = true,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Conversation.fromJson(Map<String, dynamic> json) {
    return Conversation(
      id: json['id'],
      recipient: User.fromJson(json['recipient'] ?? json['user']),
      lastMessage: json['last_message'],
      lastMessageTime: json['last_message_time'] != null 
          ? DateTime.parse(json['last_message_time'])
          : null,
      unreadCount: json['unread_count'] ?? 0,
      isActive: json['is_active'] ?? true,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'recipient': recipient.toJson(),
      'last_message': lastMessage,
      'last_message_time': lastMessageTime?.toIso8601String(),
      'unread_count': unreadCount,
      'is_active': isActive,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  Conversation copyWith({
    int? id,
    User? recipient,
    String? lastMessage,
    DateTime? lastMessageTime,
    int? unreadCount,
    bool? isActive,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Conversation(
      id: id ?? this.id,
      recipient: recipient ?? this.recipient,
      lastMessage: lastMessage ?? this.lastMessage,
      lastMessageTime: lastMessageTime ?? this.lastMessageTime,
      unreadCount: unreadCount ?? this.unreadCount,
      isActive: isActive ?? this.isActive,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}

// Simple User model for conversations
class User {
  final int id;
  final String name;
  final String email;
  final String? profilePicture;
  final bool isOnline;

  User({
    required this.id,
    required this.name,
    required this.email,
    this.profilePicture,
    this.isOnline = false,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'] ?? json['first_name'] + ' ' + json['last_name'],
      email: json['email'],
      profilePicture: json['profile_picture'],
      isOnline: json['is_online'] ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'profile_picture': profilePicture,
      'is_online': isOnline,
    };
  }
}
