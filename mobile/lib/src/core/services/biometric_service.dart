import 'package:flutter/services.dart';
import 'package:local_auth/local_auth.dart';
import 'package:local_auth_android/local_auth_android.dart';
import 'package:local_auth_darwin/local_auth_darwin.dart';

/// Service for handling biometric authentication
class BiometricService {
  static final LocalAuthentication _localAuth = LocalAuthentication();
  
  /// Check if biometric authentication is available on the device
  static Future<bool> isAvailable() async {
    try {
      final bool isAvailable = await _localAuth.canCheckBiometrics;
      final bool isDeviceSupported = await _localAuth.isDeviceSupported();
      return isAvailable && isDeviceSupported;
    } on PlatformException {
      return false;
    }
  }
  
  /// Get available biometric types
  static Future<List<BiometricType>> getAvailableBiometrics() async {
    try {
      return await _localAuth.getAvailableBiometrics();
    } on PlatformException {
      return [];
    }
  }
  
  /// Authenticate using biometrics
  static Future<BiometricAuthResult> authenticate({
    String reason = 'Please authenticate to access BizInvestify',
    bool biometricOnly = false,
  }) async {
    try {
      final bool isAvailable = await BiometricService.isAvailable();
      if (!isAvailable) {
        return BiometricAuthResult.notAvailable;
      }
      
      final bool didAuthenticate = await _localAuth.authenticate(
        localizedReason: reason,
        authMessages: const [
          AndroidAuthMessages(
            signInTitle: 'BizInvestify Authentication',
            biometricHint: 'Touch the fingerprint sensor',
            biometricNotRecognized: 'Fingerprint not recognized. Try again.',
            biometricSuccess: 'Fingerprint recognized successfully',
            cancelButton: 'Cancel',
            deviceCredentialsRequiredTitle: 'Device lock required',
            deviceCredentialsSetupDescription: 'Please set up device lock',
            goToSettingsButton: 'Settings',
            goToSettingsDescription: 'Set up fingerprint in Settings',
          ),
          IOSAuthMessages(
            lockOut: 'Biometric authentication is locked out',
            goToSettingsButton: 'Settings',
            goToSettingsDescription: 'Set up Touch ID or Face ID',
            cancelButton: 'Cancel',
          ),
        ],
        options: AuthenticationOptions(
          biometricOnly: biometricOnly,
          stickyAuth: true,
          sensitiveTransaction: true,
        ),
      );
      
      return didAuthenticate 
          ? BiometricAuthResult.success 
          : BiometricAuthResult.failed;
          
    } on PlatformException catch (e) {
      switch (e.code) {
        case 'NotAvailable':
          return BiometricAuthResult.notAvailable;
        case 'NotEnrolled':
          return BiometricAuthResult.notEnrolled;
        case 'LockedOut':
          return BiometricAuthResult.lockedOut;
        case 'PermanentlyLockedOut':
          return BiometricAuthResult.permanentlyLockedOut;
        case 'UserCancel':
          return BiometricAuthResult.cancelled;
        default:
          return BiometricAuthResult.error;
      }
    }
  }
  
  /// Check if user has biometric credentials enrolled
  static Future<bool> hasEnrolledBiometrics() async {
    try {
      final availableBiometrics = await getAvailableBiometrics();
      return availableBiometrics.isNotEmpty;
    } catch (e) {
      return false;
    }
  }
  
  /// Get biometric type display name
  static String getBiometricTypeName(BiometricType type) {
    switch (type) {
      case BiometricType.face:
        return 'Face ID';
      case BiometricType.fingerprint:
        return 'Fingerprint';
      case BiometricType.iris:
        return 'Iris';
      case BiometricType.strong:
        return 'Strong Biometric';
      case BiometricType.weak:
        return 'Weak Biometric';
    }
  }
  
  /// Get primary biometric type available
  static Future<BiometricType?> getPrimaryBiometricType() async {
    final availableBiometrics = await getAvailableBiometrics();
    if (availableBiometrics.isEmpty) return null;
    
    // Prioritize Face ID, then Fingerprint
    if (availableBiometrics.contains(BiometricType.face)) {
      return BiometricType.face;
    } else if (availableBiometrics.contains(BiometricType.fingerprint)) {
      return BiometricType.fingerprint;
    } else {
      return availableBiometrics.first;
    }
  }
}

/// Result of biometric authentication attempt
enum BiometricAuthResult {
  success,
  failed,
  cancelled,
  notAvailable,
  notEnrolled,
  lockedOut,
  permanentlyLockedOut,
  error,
}

/// Extension for BiometricAuthResult to get user-friendly messages
extension BiometricAuthResultExtension on BiometricAuthResult {
  String get message {
    switch (this) {
      case BiometricAuthResult.success:
        return 'Authentication successful';
      case BiometricAuthResult.failed:
        return 'Authentication failed. Please try again.';
      case BiometricAuthResult.cancelled:
        return 'Authentication cancelled';
      case BiometricAuthResult.notAvailable:
        return 'Biometric authentication is not available on this device';
      case BiometricAuthResult.notEnrolled:
        return 'No biometric credentials enrolled. Please set up biometric authentication in Settings.';
      case BiometricAuthResult.lockedOut:
        return 'Biometric authentication is temporarily locked. Please try again later.';
      case BiometricAuthResult.permanentlyLockedOut:
        return 'Biometric authentication is permanently locked. Please use device passcode.';
      case BiometricAuthResult.error:
        return 'An error occurred during authentication';
    }
  }
  
  bool get isSuccess => this == BiometricAuthResult.success;
  bool get isFailure => !isSuccess;
}
