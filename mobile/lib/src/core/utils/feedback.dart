import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

class Haptics {
  static Future<void> light() => HapticFeedback.lightImpact();
  static Future<void> medium() => HapticFeedback.mediumImpact();
  static Future<void> heavy() => HapticFeedback.heavyImpact();
  static Future<void> success() => HapticFeedback.selectionClick();
  static Future<void> error() => HapticFeedback.vibrate();
}

class SnackbarFeedback {
  static void show(
    BuildContext context, {
    required String message,
    Color? background,
    String? actionLabel,
    VoidCallback? onAction,
  }) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: background,
        action: actionLabel != null
            ? SnackBarAction(label: actionLabel, onPressed: onAction ?? () {})
            : null,
      ),
    );
  }
}


