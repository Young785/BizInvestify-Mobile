import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_dimensions.dart';
import '../../../core/constants/app_typography.dart';

/// Modern Bottom Navigation Bar
/// Professional design with smooth animations and modern aesthetics
class ModernBottomNav extends StatelessWidget {
  final int currentIndex;
  final Function(int) onTap;
  final List<ModernBottomNavItem> items;

  const ModernBottomNav({
    super.key,
    required this.currentIndex,
    required this.onTap,
    required this.items,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.backgroundSecondary,
        border: const Border(
          top: BorderSide(
            color: AppColors.borderPrimary,
            width: 1,
          ),
        ),
        boxShadow: [
          BoxShadow(
            color: AppColors.shadowSoft,
            blurRadius: 16,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: SafeArea(
        child: Container(
          height: 80,
          padding: const EdgeInsets.symmetric(
            horizontal: AppDimensions.spacing8,
            vertical: AppDimensions.spacing8,
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: items.asMap().entries.map((entry) {
              final index = entry.key;
              final item = entry.value;
              final isSelected = index == currentIndex;

              return Expanded(
                child: GestureDetector(
                  onTap: () => onTap(index),
                  behavior: HitTestBehavior.opaque,
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 250),
                    curve: Curves.easeInOut,
                    padding: const EdgeInsets.symmetric(
                      horizontal: AppDimensions.spacing8,
                      vertical: AppDimensions.spacing8,
                    ),
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(
                        AppDimensions.borderRadiusMedium,
                      ),
                      color: isSelected
                          ? AppColors.primary500.withOpacity(0.1)
                          : Colors.transparent,
                    ),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        // Icon with animation
                        AnimatedContainer(
                          duration: const Duration(milliseconds: 250),
                          curve: Curves.easeInOut,
                          padding: const EdgeInsets.all(AppDimensions.spacing4),
                          child: Icon(
                            isSelected ? item.activeIcon : item.icon,
                            size: AppDimensions.iconSizeLarge,
                            color: isSelected
                                ? AppColors.primary500
                                : AppColors.textTertiary,
                          ),
                        ),
                        
                        const SizedBox(height: AppDimensions.spacing2),
                        
                        // Label with animation
                        AnimatedDefaultTextStyle(
                          duration: const Duration(milliseconds: 250),
                          curve: Curves.easeInOut,
                          style: AppTypography.bottomNavLabel.copyWith(
                            color: isSelected
                                ? AppColors.primary500
                                : AppColors.textTertiary,
                            fontWeight: isSelected
                                ? AppTypography.semibold
                                : AppTypography.medium,
                          ),
                          child: Text(
                            item.label,
                            textAlign: TextAlign.center,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        
                        // Active indicator
                        AnimatedContainer(
                          duration: const Duration(milliseconds: 250),
                          curve: Curves.easeInOut,
                          height: AppDimensions.spacing2,
                          width: isSelected ? 20 : 0,
                          margin: const EdgeInsets.only(top: AppDimensions.spacing4),
                          decoration: BoxDecoration(
                            color: AppColors.primary500,
                            borderRadius: BorderRadius.circular(1),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
        ),
      ),
    );
  }
}

/// Modern Bottom Navigation Item
class ModernBottomNavItem {
  final IconData icon;
  final IconData activeIcon;
  final String label;
  final int? badgeCount;

  const ModernBottomNavItem({
    required this.icon,
    required this.activeIcon,
    required this.label,
    this.badgeCount,
  });
}

/// Floating Bottom Navigation Bar
/// Modern floating design with glassmorphism effect
class FloatingBottomNav extends StatelessWidget {
  final int currentIndex;
  final Function(int) onTap;
  final List<ModernBottomNavItem> items;

  const FloatingBottomNav({
    super.key,
    required this.currentIndex,
    required this.onTap,
    required this.items,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(AppDimensions.spacing16),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(AppDimensions.borderRadiusXLarge),
        child: Container(
          decoration: BoxDecoration(
            color: AppColors.glassPrimary,
            borderRadius: BorderRadius.circular(AppDimensions.borderRadiusXLarge),
            border: Border.all(
              color: AppColors.borderPrimary,
              width: 1,
            ),
            boxShadow: [
              BoxShadow(
                color: AppColors.shadowLarge,
                blurRadius: 24,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: SafeArea(
            child: Container(
              height: 68,
              padding: const EdgeInsets.symmetric(
                horizontal: AppDimensions.spacing12,
                vertical: AppDimensions.spacing8,
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: items.asMap().entries.map((entry) {
                  final index = entry.key;
                  final item = entry.value;
                  final isSelected = index == currentIndex;

                  return Expanded(
                    child: GestureDetector(
                      onTap: () => onTap(index),
                      behavior: HitTestBehavior.opaque,
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 300),
                        curve: Curves.easeInOut,
                        padding: const EdgeInsets.symmetric(
                          horizontal: AppDimensions.spacing12,
                          vertical: AppDimensions.spacing8,
                        ),
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(
                            AppDimensions.borderRadiusLarge,
                          ),
                          color: isSelected
                              ? AppColors.primary500
                              : Colors.transparent,
                        ),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            // Icon
                            Icon(
                              isSelected ? item.activeIcon : item.icon,
                              size: AppDimensions.iconSizeLarge,
                              color: isSelected
                                  ? AppColors.white
                                  : AppColors.textTertiary,
                            ),
                            
                            const SizedBox(height: AppDimensions.spacing2),
                            
                            // Label
                            AnimatedDefaultTextStyle(
                              duration: const Duration(milliseconds: 300),
                              curve: Curves.easeInOut,
                              style: AppTypography.labelSmall.copyWith(
                                color: isSelected
                                    ? AppColors.white
                                    : AppColors.textTertiary,
                                fontWeight: isSelected
                                    ? AppTypography.semibold
                                    : AppTypography.medium,
                              ),
                              child: Text(
                                item.label,
                                textAlign: TextAlign.center,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  );
                }).toList(),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
