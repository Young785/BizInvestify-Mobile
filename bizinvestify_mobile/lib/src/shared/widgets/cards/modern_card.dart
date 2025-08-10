import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_dimensions.dart';
import '../../../core/constants/app_typography.dart';

/// Modern Card Widget - Professional and sophisticated
/// A reusable card component with modern styling and smooth animations
class ModernCard extends StatefulWidget {
  const ModernCard({
    super.key,
    required this.child,
    this.onTap,
    this.onLongPress,
    this.margin,
    this.padding,
    this.backgroundColor,
    this.borderRadius,
    this.border,
    this.elevation,
    this.shadows,
    this.style = ModernCardStyle.elevated,
    this.animationDuration = const Duration(milliseconds: 200),
    this.hapticFeedback = true,
  });

  final Widget child;
  final VoidCallback? onTap;
  final VoidCallback? onLongPress;
  final EdgeInsetsGeometry? margin;
  final EdgeInsetsGeometry? padding;
  final Color? backgroundColor;
  final double? borderRadius;
  final Border? border;
  final double? elevation;
  final List<BoxShadow>? shadows;
  final ModernCardStyle style;
  final Duration animationDuration;
  final bool hapticFeedback;

  @override
  State<ModernCard> createState() => _ModernCardState();
}

class _ModernCardState extends State<ModernCard>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _scaleAnimation;
  late Animation<double> _elevationAnimation;
  bool _isPressed = false;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: widget.animationDuration,
      vsync: this,
    );
    _scaleAnimation = Tween<double>(
      begin: 1.0,
      end: 0.98,
    ).animate(CurvedAnimation(
      parent: _controller,
      curve: Curves.easeInOut,
    ));
    _elevationAnimation = Tween<double>(
      begin: 0.0,
      end: 4.0,
    ).animate(CurvedAnimation(
      parent: _controller,
      curve: Curves.easeInOut,
    ));
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _handleTapDown(TapDownDetails details) {
    if (widget.onTap != null) {
      setState(() => _isPressed = true);
      _controller.forward();
    }
  }

  void _handleTapUp(TapUpDetails details) {
    _handleTapEnd();
  }

  void _handleTapCancel() {
    _handleTapEnd();
  }

  void _handleTapEnd() {
    if (mounted) {
      setState(() => _isPressed = false);
      _controller.reverse();
    }
  }

  @override
  Widget build(BuildContext context) {
    final styleConfig = _getStyleConfig();
    final effectivePadding = widget.padding ?? styleConfig.padding;
    final effectiveMargin = widget.margin ?? styleConfig.margin;
    final effectiveBorderRadius = widget.borderRadius ?? styleConfig.borderRadius;

    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        return Transform.scale(
          scale: _scaleAnimation.value,
          child: Container(
            margin: effectiveMargin,
            child: Material(
              color: Colors.transparent,
              child: GestureDetector(
                onTapDown: _handleTapDown,
                onTapUp: _handleTapUp,
                onTapCancel: _handleTapCancel,
                onTap: widget.onTap,
                onLongPress: widget.onLongPress,
                child: AnimatedContainer(
                  duration: widget.animationDuration,
                  curve: Curves.easeInOut,
                  decoration: BoxDecoration(
                    color: widget.backgroundColor ?? styleConfig.backgroundColor,
                    borderRadius: BorderRadius.circular(effectiveBorderRadius),
                    border: widget.border ?? styleConfig.border,
                    boxShadow: _isPressed ? [] : (widget.shadows ?? styleConfig.shadows),
                  ),
                  child: Container(
                    padding: effectivePadding,
                    child: widget.child,
                  ),
                ),
              ),
            ),
          ),
        );
      },
    );
  }

  _CardStyleConfig _getStyleConfig() {
    switch (widget.style) {
      case ModernCardStyle.elevated:
        return _CardStyleConfig(
          backgroundColor: AppColors.backgroundSecondary,
          padding: const EdgeInsets.all(AppDimensions.cardPaddingMedium),
          margin: const EdgeInsets.symmetric(
            horizontal: AppDimensions.containerPaddingMobile,
            vertical: AppDimensions.spacing8,
          ),
          borderRadius: AppDimensions.borderRadiusLarge,
          shadows: [
            BoxShadow(
              color: AppColors.shadowSoft,
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        );

      case ModernCardStyle.outlined:
        return _CardStyleConfig(
          backgroundColor: AppColors.backgroundSecondary,
          padding: const EdgeInsets.all(AppDimensions.cardPaddingMedium),
          margin: const EdgeInsets.symmetric(
            horizontal: AppDimensions.containerPaddingMobile,
            vertical: AppDimensions.spacing8,
          ),
          borderRadius: AppDimensions.borderRadiusLarge,
          border: const Border.fromBorderSide(
            BorderSide(color: AppColors.borderPrimary, width: 1),
          ),
          shadows: [],
        );

      case ModernCardStyle.filled:
        return _CardStyleConfig(
          backgroundColor: AppColors.backgroundTertiary,
          padding: const EdgeInsets.all(AppDimensions.cardPaddingMedium),
          margin: const EdgeInsets.symmetric(
            horizontal: AppDimensions.containerPaddingMobile,
            vertical: AppDimensions.spacing8,
          ),
          borderRadius: AppDimensions.borderRadiusLarge,
          shadows: [],
        );

      case ModernCardStyle.glass:
        return _CardStyleConfig(
          backgroundColor: AppColors.glassPrimary,
          padding: const EdgeInsets.all(AppDimensions.cardPaddingMedium),
          margin: const EdgeInsets.symmetric(
            horizontal: AppDimensions.containerPaddingMobile,
            vertical: AppDimensions.spacing8,
          ),
          borderRadius: AppDimensions.borderRadiusLarge,
          border: const Border.fromBorderSide(
            BorderSide(color: AppColors.borderPrimary, width: 1),
          ),
          shadows: [
            BoxShadow(
              color: AppColors.shadowSoft,
              blurRadius: 16,
              offset: const Offset(0, 4),
            ),
          ],
        );
    }
  }
}

class _CardStyleConfig {
  final Color backgroundColor;
  final EdgeInsetsGeometry padding;
  final EdgeInsetsGeometry margin;
  final double borderRadius;
  final Border? border;
  final List<BoxShadow> shadows;

  _CardStyleConfig({
    required this.backgroundColor,
    required this.padding,
    required this.margin,
    required this.borderRadius,
    this.border,
    required this.shadows,
  });
}

enum ModernCardStyle {
  elevated,
  outlined,
  filled,
  glass,
}

/// Dashboard Card - Specialized card for dashboard metrics
class DashboardCard extends StatelessWidget {
  const DashboardCard({
    super.key,
    required this.title,
    required this.value,
    this.subtitle,
    this.icon,
    this.trend,
    this.trendValue,
    this.onTap,
    this.backgroundColor,
    this.gradient,
  });

  final String title;
  final String value;
  final String? subtitle;
  final IconData? icon;
  final TrendType? trend;
  final String? trendValue;
  final VoidCallback? onTap;
  final Color? backgroundColor;
  final Gradient? gradient;

  @override
  Widget build(BuildContext context) {
    return ModernCard(
      onTap: onTap,
      child: Container(
        decoration: gradient != null
            ? BoxDecoration(
                gradient: gradient,
                borderRadius: BorderRadius.circular(AppDimensions.borderRadiusLarge),
              )
            : null,
        child: Padding(
          padding: gradient != null
              ? const EdgeInsets.all(AppDimensions.cardPaddingMedium)
              : EdgeInsets.zero,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header with icon and title
              Row(
                children: [
                  if (icon != null) ...[
                    Container(
                      padding: const EdgeInsets.all(AppDimensions.spacing8),
                      decoration: BoxDecoration(
                        color: gradient != null
                            ? AppColors.white.withOpacity(0.2)
                            : AppColors.primary100,
                        borderRadius: BorderRadius.circular(AppDimensions.borderRadiusSmall),
                      ),
                      child: Icon(
                        icon!,
                        size: AppDimensions.iconSizeMedium,
                        color: gradient != null
                            ? AppColors.white
                            : AppColors.primary500,
                      ),
                    ),
                    const SizedBox(width: AppDimensions.spacing12),
                  ],
                  Expanded(
                    child: Text(
                      title,
                      style: AppTypography.cardSubtitle.copyWith(
                        color: gradient != null
                            ? AppColors.white.withOpacity(0.8)
                            : null,
                      ),
                    ),
                  ),
                ],
              ),
              
              const SizedBox(height: AppDimensions.spacing16),
              
              // Value
              Text(
                value,
                style: AppTypography.financialLarge.copyWith(
                  color: gradient != null ? AppColors.white : null,
                ),
              ),
              
              // Subtitle and trend
              if (subtitle != null || trend != null) ...[
                const SizedBox(height: AppDimensions.spacing8),
                Row(
                  children: [
                    if (subtitle != null)
                      Expanded(
                        child: Text(
                          subtitle!,
                          style: AppTypography.captionMedium.copyWith(
                            color: gradient != null
                                ? AppColors.white.withOpacity(0.7)
                                : null,
                          ),
                        ),
                      ),
                    if (trend != null && trendValue != null)
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: AppDimensions.spacing8,
                          vertical: AppDimensions.spacing4,
                        ),
                        decoration: BoxDecoration(
                          color: _getTrendColor(trend!).withOpacity(0.1),
                          borderRadius: BorderRadius.circular(AppDimensions.borderRadiusSmall),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              _getTrendIcon(trend!),
                              size: AppDimensions.iconSizeSmall,
                              color: _getTrendColor(trend!),
                            ),
                            const SizedBox(width: AppDimensions.spacing4),
                            Text(
                              trendValue!,
                              style: AppTypography.labelSmall.copyWith(
                                color: _getTrendColor(trend!),
                                fontWeight: AppTypography.semibold,
                              ),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Color _getTrendColor(TrendType trend) {
    switch (trend) {
      case TrendType.up:
        return AppColors.success500;
      case TrendType.down:
        return AppColors.error;
      case TrendType.neutral:
        return AppColors.neutral500;
    }
  }

  IconData _getTrendIcon(TrendType trend) {
    switch (trend) {
      case TrendType.up:
        return Icons.trending_up;
      case TrendType.down:
        return Icons.trending_down;
      case TrendType.neutral:
        return Icons.trending_flat;
    }
  }
}

enum TrendType {
  up,
  down,
  neutral,
}

/// Quick Action Card - For dashboard quick actions
class QuickActionCard extends StatelessWidget {
  const QuickActionCard({
    super.key,
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.onTap,
    this.backgroundColor,
    this.iconColor,
  });

  final String title;
  final String subtitle;
  final IconData icon;
  final VoidCallback onTap;
  final Color? backgroundColor;
  final Color? iconColor;

  @override
  Widget build(BuildContext context) {
    return ModernCard(
      onTap: onTap,
      style: ModernCardStyle.outlined,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Icon
          Container(
            padding: const EdgeInsets.all(AppDimensions.spacing12),
            decoration: BoxDecoration(
              color: backgroundColor ?? AppColors.primary100,
              borderRadius: BorderRadius.circular(AppDimensions.borderRadiusMedium),
            ),
            child: Icon(
              icon,
              size: AppDimensions.iconSizeXLarge,
              color: iconColor ?? AppColors.primary500,
            ),
          ),
          
          const SizedBox(height: AppDimensions.spacing16),
          
          // Title
          Text(
            title,
            style: AppTypography.cardTitle,
          ),
          
          const SizedBox(height: AppDimensions.spacing4),
          
          // Subtitle
          Text(
            subtitle,
            style: AppTypography.cardSubtitle,
          ),
        ],
      ),
    );
  }
}
