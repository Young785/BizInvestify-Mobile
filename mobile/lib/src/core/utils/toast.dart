import 'package:flutter/material.dart';
import '../routing/app_router.dart';

enum ToastType { success, error, info, warning }

class AppToast {
  static OverlayEntry? _current;

  static void show(
    String message, {
    ToastType type = ToastType.info,
    String? title,
    Duration duration = const Duration(seconds: 3),
  }) {
    final navigator = rootNavigatorKey.currentState;
    if (navigator == null) return;

    _current?.remove();

    final color = _colorFor(type);
    final icon = _iconFor(type);

    final overlay = OverlayEntry(
      builder: (_) => SafeArea(
        child: Align(
          alignment: Alignment.topCenter,
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: _ToastCard(
              title: title,
              message: message,
              color: color,
              icon: icon,
            ),
          ),
        ),
      ),
    );

    navigator.overlay?.insert(overlay);
    _current = overlay;

    Future.delayed(duration, () {
      overlay.remove();
      if (_current == overlay) _current = null;
    });
  }

  static void success(String message, {String? title, Duration duration = const Duration(seconds: 3)}) =>
      show(message, type: ToastType.success, title: title, duration: duration);
  static void error(String message, {String? title, Duration duration = const Duration(seconds: 4)}) =>
      show(message, type: ToastType.error, title: title, duration: duration);
  static void info(String message, {String? title, Duration duration = const Duration(seconds: 3)}) =>
      show(message, type: ToastType.info, title: title, duration: duration);
  static void warning(String message, {String? title, Duration duration = const Duration(seconds: 4)}) =>
      show(message, type: ToastType.warning, title: title, duration: duration);

  static Color _colorFor(ToastType type) {
    switch (type) {
      case ToastType.success:
        return const Color(0xFF16A34A);
      case ToastType.error:
        return const Color(0xFFDC2626);
      case ToastType.warning:
        return const Color(0xFFF59E0B);
      case ToastType.info:
        return const Color(0xFF2563EB);
    }
  }

  static IconData _iconFor(ToastType type) {
    switch (type) {
      case ToastType.success:
        return Icons.check_circle_rounded;
      case ToastType.error:
        return Icons.error_rounded;
      case ToastType.warning:
        return Icons.warning_rounded;
      case ToastType.info:
        return Icons.info_rounded;
    }
  }
}

class _ToastCard extends StatefulWidget {
  final String? title;
  final String message;
  final Color color;
  final IconData icon;

  const _ToastCard({
    required this.title,
    required this.message,
    required this.color,
    required this.icon,
  });

  @override
  State<_ToastCard> createState() => _ToastCardState();
}

class _ToastCardState extends State<_ToastCard> with SingleTickerProviderStateMixin {
  late final AnimationController _controller =
      AnimationController(vsync: this, duration: const Duration(milliseconds: 250));
  late final Animation<Offset> _slide =
      Tween(begin: const Offset(0, -1), end: Offset.zero).animate(CurvedAnimation(parent: _controller, curve: Curves.easeOut));
  late final Animation<double> _fade =
      CurvedAnimation(parent: _controller, curve: Curves.easeIn);

  @override
  void initState() {
    super.initState();
    _controller.forward();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SlideTransition(
      position: _slide,
      child: FadeTransition(
        opacity: _fade,
        child: Material(
          elevation: 12,
          borderRadius: BorderRadius.circular(12),
          color: widget.color,
          child: Container(
            width: double.infinity,
            constraints: const BoxConstraints(maxWidth: 560),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(widget.icon, color: Colors.white),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (widget.title != null)
                        Text(
                          widget.title!,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w700,
                            fontSize: 15,
                          ),
                        ),
                      Text(
                        widget.message,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 14,
                          height: 1.3,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}


