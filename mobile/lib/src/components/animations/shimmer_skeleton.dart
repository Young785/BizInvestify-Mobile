import 'package:flutter/material.dart';

class ShimmerSkeleton extends StatefulWidget {
  final double height;
  final double width;
  final BorderRadius borderRadius;

  const ShimmerSkeleton({
    super.key,
    required this.height,
    required this.width,
    this.borderRadius = const BorderRadius.all(Radius.circular(10)),
  });

  @override
  State<ShimmerSkeleton> createState() => _ShimmerSkeletonState();
}

class _ShimmerSkeletonState extends State<ShimmerSkeleton> with SingleTickerProviderStateMixin {
  late final AnimationController _controller =
      AnimationController(vsync: this, duration: const Duration(milliseconds: 1200))..repeat();

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final base = Theme.of(context).brightness == Brightness.dark
        ? const Color(0xFF1F2937)
        : const Color(0xFFE5E7EB);
    final highlight = Theme.of(context).brightness == Brightness.dark
        ? const Color(0xFF374151)
        : const Color(0xFFF3F4F6);

    return ClipRRect(
      borderRadius: widget.borderRadius,
      child: AnimatedBuilder(
        animation: _controller,
        builder: (_, __) {
          return CustomPaint(
            size: Size(widget.width, widget.height),
            painter: _ShimmerPainter(
              progress: _controller.value,
              base: base,
              highlight: highlight,
            ),
          );
        },
      ),
    );
  }
}

class _ShimmerPainter extends CustomPainter {
  final double progress;
  final Color base;
  final Color highlight;

  _ShimmerPainter({required this.progress, required this.base, required this.highlight});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()..shader = _buildShader(size);
    canvas.drawRect(Offset.zero & size, paint);
  }

  LinearGradient _gradient() {
    return LinearGradient(
      colors: [base, highlight, base],
      stops: const [0.25, 0.5, 0.75],
      begin: Alignment(-1 - 2, 0),
      end: const Alignment(1 + 2, 0),
      transform: _SlidingTransform(progress),
    );
  }

  Shader _buildShader(Size size) => _gradient().createShader(Offset.zero & size);

  @override
  bool shouldRepaint(covariant _ShimmerPainter oldDelegate) => oldDelegate.progress != progress;
}

class _SlidingTransform extends GradientTransform {
  final double progress;
  const _SlidingTransform(this.progress);

  @override
  Matrix4 transform(Rect bounds, {TextDirection? textDirection}) {
    final dx = (bounds.width + bounds.width) * (progress - 0.5);
    return Matrix4.translationValues(dx, 0, 0);
  }
}


