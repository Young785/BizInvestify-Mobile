import 'package:flutter/material.dart';

class PulseButton extends StatefulWidget {
  final Widget child;
  final VoidCallback? onTap;
  final double scale;
  final Duration duration;

  const PulseButton({
    super.key,
    required this.child,
    this.onTap,
    this.scale = 0.96,
    this.duration = const Duration(milliseconds: 120),
  });

  @override
  State<PulseButton> createState() => _PulseButtonState();
}

class _PulseButtonState extends State<PulseButton> with SingleTickerProviderStateMixin {
  late final AnimationController _controller =
      AnimationController(vsync: this, duration: widget.duration, reverseDuration: widget.duration);
  late final Animation<double> _scaleAnim =
      Tween<double>(begin: 1, end: widget.scale).animate(CurvedAnimation(parent: _controller, curve: Curves.easeOut));

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _tapDown(TapDownDetails _) => _controller.forward();
  void _tapCancel() => _controller.reverse();
  void _tapUp(TapUpDetails _) {
    _controller.reverse();
    widget.onTap?.call();
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: _tapDown,
      onTapCancel: _tapCancel,
      onTapUp: _tapUp,
      behavior: HitTestBehavior.opaque,
      child: AnimatedBuilder(
        animation: _controller,
        builder: (_, child) => Transform.scale(scale: _scaleAnim.value, child: child),
        child: widget.child,
      ),
    );
  }
}


