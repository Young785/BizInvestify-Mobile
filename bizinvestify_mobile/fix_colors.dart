// Color Migration Script for BizInvestify Mobile App
// This script will be used to systematically update color references

const Map<String, String> colorMigration = {
  // Text Colors
  'AppColors.text800': 'AppColors.textPrimary',
  'AppColors.text700': 'AppColors.textSecondary', 
  'AppColors.text600': 'AppColors.textTertiary',
  'AppColors.text500': 'AppColors.textTertiary',
  'AppColors.text400': 'AppColors.textQuaternary',
  'AppColors.text300': 'AppColors.textQuaternary',
  
  // Background Colors
  'AppColors.background200': 'AppColors.backgroundSecondary',
  'AppColors.background100': 'AppColors.backgroundSecondary',
  'AppColors.background50': 'AppColors.backgroundPrimary',
  'AppColors.background300': 'AppColors.surfaceBorder',
  
  // Accent Colors (changed to secondary)
  'AppColors.accent500': 'AppColors.secondary500',
  'AppColors.accent600': 'AppColors.secondary600',
  'AppColors.accent400': 'AppColors.secondary400',
  'AppColors.accent100': 'AppColors.secondary100',
  'AppColors.accent50': 'AppColors.secondary50',
  
  // Status Colors
  'AppColors.error': 'AppColors.error500',
  'AppColors.success': 'AppColors.success500',
  'AppColors.warning': 'AppColors.warning500',
  'AppColors.info': 'AppColors.info500',
  
  // Dimensions to hardcoded values  
  'AppDimensions.spacing4': '4.0',
  'AppDimensions.spacing8': '8.0',
  'AppDimensions.spacing12': '12.0',
  'AppDimensions.spacing16': '16.0',
  'AppDimensions.spacing20': '20.0',
  'AppDimensions.spacing24': '24.0',
  'AppDimensions.spacing32': '32.0',
  'AppDimensions.spacing48': '48.0',
  'AppDimensions.spacing64': '64.0',
  
  'AppDimensions.borderRadiusSmall': '8.0',
  'AppDimensions.borderRadiusMedium': '12.0',
  'AppDimensions.borderRadiusLarge': '16.0',
  'AppDimensions.borderRadiusXLarge': '24.0',
  
  'AppDimensions.cardElevationSmall': '2.0',
  'AppDimensions.cardElevationMedium': '4.0', 
  'AppDimensions.cardElevationLarge': '8.0',
  
  'AppDimensions.buttonHeightSmall': '36.0',
  'AppDimensions.buttonHeightMedium': '48.0',
  'AppDimensions.buttonHeightLarge': '56.0',
  
  'AppDimensions.buttonPaddingHorizontal': '24.0',
  'AppDimensions.buttonPaddingVertical': '16.0',
  
  'AppDimensions.iconSizeSmall': '16.0',
  'AppDimensions.iconSizeMedium': '24.0',
  'AppDimensions.iconSizeLarge': '32.0',
  
  'AppDimensions.borderWidthThin': '0.5',
  'AppDimensions.borderWidthMedium': '2.0',
  'AppDimensions.borderWidthThick': '4.0',
};

void main() {
  print('Color Migration Map Ready');
  print('Total mappings: ${colorMigration.length}');
}
