class Message {
  final int id;
  final int conversationId;
  final int senderId;
  final String content;
  final String? attachmentUrl;
  final String messageType; // text, image, file, etc.
  final bool isRead;
  final DateTime createdAt;
  final DateTime updatedAt;

  Message({
    required this.id,
    required this.conversationId,
    required this.senderId,
    required this.content,
    this.attachmentUrl,
    this.messageType = 'text',
    this.isRead = false,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Message.fromJson(Map<String, dynamic> json) {
    return Message(
      id: json['id'],
      conversationId: json['conversation_id'],
      senderId: json['sender_id'],
      content: json['content'],
      attachmentUrl: json['attachment_url'],
      messageType: json['message_type'] ?? 'text',
      isRead: json['is_read'] ?? false,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'conversation_id': conversationId,
      'sender_id': senderId,
      'content': content,
      'attachment_url': attachmentUrl,
      'message_type': messageType,
      'is_read': isRead,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  Message copyWith({
    int? id,
    int? conversationId,
    int? senderId,
    String? content,
    String? attachmentUrl,
    String? messageType,
    bool? isRead,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Message(
      id: id ?? this.id,
      conversationId: conversationId ?? this.conversationId,
      senderId: senderId ?? this.senderId,
      content: content ?? this.content,
      attachmentUrl: attachmentUrl ?? this.attachmentUrl,
      messageType: messageType ?? this.messageType,
      isRead: isRead ?? this.isRead,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}
