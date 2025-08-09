#!/usr/bin/env dart

import 'dart:io';

/// Development helper script for common Flutter tasks
void main(List<String> args) async {
  if (args.isEmpty) {
    showUsage();
    return;
  }

  final command = args[0];
  
  switch (command) {
    case 'setup':
      await setupProject();
      break;
    case 'clean':
      await cleanProject();
      break;
    case 'build':
      await buildProject(args.length > 1 ? args[1] : 'debug');
      break;
    case 'analyze':
      await analyzeProject();
      break;
    case 'test':
      await testProject();
      break;
    case 'fix':
      await fixDeprecations();
      break;
    default:
      if (_isDebug) print('❌ Unknown command: $command');
      showUsage();
  }
}

void showUsage() {
  if (_isDebug) print('🛠️  BizInvestify Mobile - Development Helper');
  if (_isDebug) print('');
  if (_isDebug) print('Usage: dart scripts/dev_helper.dart <command>');
  if (_isDebug) print('');
  if (_isDebug) print('Commands:');
  if (_isDebug) print('  setup    - Initial project setup');
  if (_isDebug) print('  clean    - Clean build artifacts');
  if (_isDebug) print('  build    - Build the app (debug/release)');
  if (_isDebug) print('  analyze  - Run code analysis');
  if (_isDebug) print('  test     - Run tests');
  if (_isDebug) print('  fix      - Fix deprecation warnings');
  if (_isDebug) print('');
  if (_isDebug) print('Examples:');
  if (_isDebug) print('  dart scripts/dev_helper.dart setup');
  if (_isDebug) print('  dart scripts/dev_helper.dart build release');
  if (_isDebug) print('  dart scripts/dev_helper.dart analyze');
}

Future<void> setupProject() async {
  if (_isDebug) print('🚀 Setting up BizInvestify Mobile project...\n');
  
  await runCommand('flutter', ['pub', 'get']);
  await runCommand('dart', ['run', 'build_runner', 'build', '--delete-conflicting-outputs']);
  
  if (_isDebug) print('\n✅ Project setup complete!');
  if (_isDebug) print('📱 Run: flutter run -d chrome --web-port=8080');
}

Future<void> cleanProject() async {
  if (_isDebug) print('🧹 Cleaning project...\n');
  
  await runCommand('flutter', ['clean']);
  await runCommand('flutter', ['pub', 'get']);
  
  // Clean build runner cache
  final buildDir = Directory('.dart_tool/build');
  if (buildDir.existsSync()) {
    await buildDir.delete(recursive: true);
    if (_isDebug) print('🗑️  Cleaned build runner cache');
  }
  
  if (_isDebug) print('\n✅ Project cleaned successfully!');
}

Future<void> buildProject(String type) async {
  if (_isDebug) print('🔨 Building project ($type)...\n');
  
  switch (type.toLowerCase()) {
    case 'debug':
      await runCommand('flutter', ['build', 'apk', '--debug']);
      break;
    case 'release':
      await runCommand('flutter', ['build', 'appbundle', '--release', '--obfuscate', '--split-debug-info=build/debug-info']);
      break;
    case 'ios':
      await runCommand('flutter', ['build', 'ios', '--release']);
      break;
    case 'web':
      await runCommand('flutter', ['build', 'web', '--release']);
      break;
    default:
      if (_isDebug) print('❌ Unknown build type: $type');
      if (_isDebug) print('Available: debug, release, ios, web');
      return;
  }
  
  if (_isDebug) print('\n✅ Build complete!');
}

Future<void> analyzeProject() async {
  if (_isDebug) print('🔍 Analyzing project...\n');
  
  await runCommand('flutter', ['analyze', '--no-fatal-infos']);
  
  if (_isDebug) print('\n📊 Analysis complete!');
}

Future<void> testProject() async {
  if (_isDebug) print('🧪 Running tests...\n');
  
  await runCommand('flutter', ['test']);
  
  if (_isDebug) print('\n✅ Tests complete!');
}

Future<void> fixDeprecations() async {
  if (_isDebug) print('🔧 Fixing deprecation warnings...\n');
  
  await runCommand('dart', ['scripts/fix_deprecations.dart']);
  
  if (_isDebug) print('\n✅ Deprecations fixed!');
}

Future<void> runCommand(String command, List<String> args) async {
  if (_isDebug) print('▶️  Running: $command ${args.join(' ')}');
  
  final result = await Process.run(command, args);
  
  if (result.stdout.toString().isNotEmpty && _isDebug) {
    print(result.stdout);
  }
  
  if (result.stderr.toString().isNotEmpty && _isDebug) {
    print('⚠️  ${result.stderr}');
  }
  
  if (result.exitCode != 0) {
    if (_isDebug) print('❌ Command failed with exit code: ${result.exitCode}');
    exit(result.exitCode);
  }
}

bool get _isDebug => true;
