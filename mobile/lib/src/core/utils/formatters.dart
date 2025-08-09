import 'package:intl/intl.dart';

class Formatters {
  static final NumberFormat _currencyFormat = NumberFormat.currency(
    symbol: '\$',
    decimalDigits: 0,
  );

  static final NumberFormat _currencyFormatWithDecimals = NumberFormat.currency(
    symbol: '\$',
    decimalDigits: 2,
  );

  // Removed unused percent formatter

  static final DateFormat _dateFormat = DateFormat('MMM dd, yyyy');
  static final DateFormat _dateTimeFormat = DateFormat('MMM dd, yyyy HH:mm');

  /// Format currency with appropriate scaling (K, M, B)
  static String formatCurrency(double amount, {bool showDecimals = false}) {
    if (amount >= 1000000000) {
      return '\$${(amount / 1000000000).toStringAsFixed(1)}B';
    } else if (amount >= 1000000) {
      return '\$${(amount / 1000000).toStringAsFixed(1)}M';
    } else if (amount >= 1000) {
      return '\$${(amount / 1000).toStringAsFixed(1)}K';
    } else {
      return showDecimals 
          ? _currencyFormatWithDecimals.format(amount)
          : _currencyFormat.format(amount);
    }
  }

  /// Format currency without scaling
  static String formatCurrencyExact(double amount) {
    return _currencyFormatWithDecimals.format(amount);
  }

  /// Format percentage
  static String formatPercentage(double percentage) {
    return '${percentage.toStringAsFixed(1)}%';
  }

  /// Format number with appropriate scaling (K, M, B)
  static String formatNumber(int number) {
    if (number >= 1000000000) {
      return '${(number / 1000000000).toStringAsFixed(1)}B';
    } else if (number >= 1000000) {
      return '${(number / 1000000).toStringAsFixed(1)}M';
    } else if (number >= 1000) {
      return '${(number / 1000).toStringAsFixed(1)}K';
    } else {
      return number.toString();
    }
  }

  /// Format date
  static String formatDate(DateTime date) {
    return _dateFormat.format(date);
  }

  /// Format date and time
  static String formatDateTime(DateTime dateTime) {
    return _dateTimeFormat.format(dateTime);
  }

  /// Format relative time (e.g., "2 hours ago", "3 days ago")
  static String formatRelativeTime(DateTime dateTime) {
    final now = DateTime.now();
    final difference = now.difference(dateTime);

    if (difference.inDays > 365) {
      final years = (difference.inDays / 365).floor();
      return '$years year${years == 1 ? '' : 's'} ago';
    } else if (difference.inDays > 30) {
      final months = (difference.inDays / 30).floor();
      return '$months month${months == 1 ? '' : 's'} ago';
    } else if (difference.inDays > 0) {
      return '${difference.inDays} day${difference.inDays == 1 ? '' : 's'} ago';
    } else if (difference.inHours > 0) {
      return '${difference.inHours} hour${difference.inHours == 1 ? '' : 's'} ago';
    } else if (difference.inMinutes > 0) {
      return '${difference.inMinutes} minute${difference.inMinutes == 1 ? '' : 's'} ago';
    } else {
      return 'Just now';
    }
  }

  /// Format investment status
  static String formatInvestmentStatus(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'Pending Review';
      case 'approved':
        return 'Approved';
      case 'rejected':
        return 'Rejected';
      case 'completed':
        return 'Completed';
      case 'cancelled':
        return 'Cancelled';
      default:
        return status.toUpperCase();
    }
  }

  /// Format business status
  static String formatBusinessStatus(String status) {
    switch (status.toLowerCase()) {
      case 'active':
        return 'Active';
      case 'inactive':
        return 'Inactive';
      case 'pending':
        return 'Pending Approval';
      case 'rejected':
        return 'Rejected';
      case 'sold':
        return 'Sold';
      default:
        return status.toUpperCase();
    }
  }

  /// Format file size
  static String formatFileSize(int bytes) {
    if (bytes >= 1024 * 1024 * 1024) {
      return '${(bytes / (1024 * 1024 * 1024)).toStringAsFixed(1)} GB';
    } else if (bytes >= 1024 * 1024) {
      return '${(bytes / (1024 * 1024)).toStringAsFixed(1)} MB';
    } else if (bytes >= 1024) {
      return '${(bytes / 1024).toStringAsFixed(1)} KB';
    } else {
      return '$bytes bytes';
    }
  }

  /// Format phone number
  static String formatPhoneNumber(String phoneNumber) {
    // Remove all non-digit characters
    final digits = phoneNumber.replaceAll(RegExp(r'\D'), '');
    
    // Format US phone numbers
    if (digits.length == 10) {
      return '(${digits.substring(0, 3)}) ${digits.substring(3, 6)}-${digits.substring(6)}';
    } else if (digits.length == 11 && digits.startsWith('1')) {
      return '+1 (${digits.substring(1, 4)}) ${digits.substring(4, 7)}-${digits.substring(7)}';
    }
    
    return phoneNumber; // Return original if not a standard format
  }

  /// Truncate text with ellipsis
  static String truncateText(String text, int maxLength) {
    if (text.length <= maxLength) {
      return text;
    }
    return '${text.substring(0, maxLength)}...';
  }

  /// Format duration
  static String formatDuration(Duration duration) {
    if (duration.inDays > 0) {
      return '${duration.inDays}d ${duration.inHours % 24}h';
    } else if (duration.inHours > 0) {
      return '${duration.inHours}h ${duration.inMinutes % 60}m';
    } else if (duration.inMinutes > 0) {
      return '${duration.inMinutes}m';
    } else {
      return '${duration.inSeconds}s';
    }
  }

  /// Format ROI (Return on Investment)
  static String formatROI(double roi) {
    final sign = roi >= 0 ? '+' : '';
    return '$sign${roi.toStringAsFixed(1)}%';
  }

  /// Format equity percentage with color coding context
  static String formatEquity(double equity) {
    return '${equity.toStringAsFixed(1)}%';
  }

  /// Format revenue with growth indicator
  static String formatRevenue(double revenue, {double? previousRevenue}) {
    final formatted = formatCurrency(revenue);
    
    if (previousRevenue != null && previousRevenue > 0) {
      final growth = ((revenue - previousRevenue) / previousRevenue) * 100;
      final growthStr = formatROI(growth);
      return '$formatted ($growthStr)';
    }
    
    return formatted;
  }
}
