#!/usr/bin/env dart

import 'dart:io';

/// Script to automatically fix common Flutter deprecation warnings
void main() async {
  if (_isDebug) print('🔧 Fixing Flutter deprecation warnings...\n');
  
  final libDir = Directory('lib');
  if (!libDir.existsSync()) {
    if (_isDebug) print('❌ lib directory not found. Run this from the Flutter project root.');
    exit(1);
  }
  
  int filesFixed = 0;
  int warningsFixed = 0;
  
  await for (final file in libDir.list(recursive: true)) {
    if (file is File && file.path.endsWith('.dart')) {
      final result = await fixFileDeprecations(file);
      if (result > 0) {
        filesFixed++;
        warningsFixed += result;
        if (_isDebug) print('✅ Fixed ${result} warnings in ${file.path}');
      }
    }
  }
  
  if (_isDebug) print('\n🎉 Fixed $warningsFixed deprecation warnings in $filesFixed files!');
  if (_isDebug) print('📝 Run "flutter analyze" to verify the fixes.');
}

/// Fix deprecation warnings in a single file
Future<int> fixFileDeprecations(File file) async {
  try {
    String content = await file.readAsString();
    final originalContent = content;
    int fixCount = 0;
    
    // Fix .withOpacity() deprecation
    final opacityRegex = RegExp(r'\.withOpacity\(([^)]+)\)');
    content = content.replaceAllMapped(opacityRegex, (match) {
      final opacity = match.group(1);
      fixCount++;
      return '.withValues(alpha: $opacity)';
    });
    
    // Fix background/onBackground color scheme deprecations
    content = content.replaceAll('colorScheme.background', 'colorScheme.surface');
    content = content.replaceAll('colorScheme.onBackground', 'colorScheme.onSurface');
    if (content.contains('colorScheme.surface') && !originalContent.contains('colorScheme.surface')) {
      fixCount++;
    }
    
    // Fix QR code foregroundColor deprecation
    content = content.replaceAll(
      'foregroundColor: AppColors.textPrimary,',
      'eyeStyle: const QrEyeStyle(color: AppColors.textPrimary),\n        dataModuleStyle: const QrDataModuleStyle(color: AppColors.textPrimary),'
    );
    if (content.contains('QrEyeStyle') && !originalContent.contains('QrEyeStyle')) {
      fixCount++;
    }
    
    // Fix null return for void functions
    content = content.replaceAll(
      'return ApiResponse.fromJson(response.data, (json) => null);',
      'return ApiResponse.fromJson(response.data, (json) => {});'
    );
    if (content.contains('(json) => {}') && originalContent.contains('(json) => null')) {
      fixCount++;
    }
    
    // Only write if changes were made
    if (content != originalContent) {
      await file.writeAsString(content);
    }
    
    return fixCount;
  } catch (e) {
    if (_isDebug) print('⚠️  Error processing ${file.path}: $e');
    return 0;
  }
}

bool get _isDebug => true;
