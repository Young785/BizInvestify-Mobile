# Two-Factor Authentication (2FA) Verification System

## Overview

This system implements a comprehensive 2FA verification flow that requires users with enabled 2FA to complete verification after login before accessing any part of the application.

## System Architecture

### Backend Components

#### 1. TwoFactorMiddleware (`backend/app/Http/Middleware/TwoFactorMiddleware.php`)
- **Purpose**: Enforces 2FA verification for all authenticated routes
- **Functionality**:
  - Checks if user has 2FA enabled (`hasTwoFactorEnabled()`)
  - Verifies if 2FA was completed in current session
  - Redirects to 2FA verification page for web requests
  - Returns 403 with verification requirement for API requests
  - Skips verification for admin impersonation

#### 2. AuthController Methods
- **`verify2FAForSession()`**: Verifies 2FA code for authenticated users
- **`check2FAVerificationStatus()`**: Checks if 2FA verification is required
- **Session Management**: Uses session keys like `2fa_verified_{user_id}`

#### 3. User Model Methods
- **`hasTwoFactorEnabled()`**: Checks if user has 2FA enabled
- **`isFullyVerified()`**: Checks complete verification status
- **`getVerificationProgressAttribute()`**: Calculates verification progress

### Frontend Components

#### 1. 2FA Verification Page (`frontend/src/app/auth/verify-2fa/page.tsx`)
- **Purpose**: Dedicated page for 2FA verification after login
- **Features**:
  - 6-digit code input with validation
  - Real-time error handling
  - Success state with auto-redirect
  - Remaining attempts tracking
  - Responsive design with security messaging

#### 2. Updated AuthGuard (`frontend/src/components/AuthGuard.tsx`)
- **Enhanced**: Checks 2FA verification status for protected routes
- **Flow**: Redirects to 2FA verification if required
- **Fallback**: Allows access if 2FA check fails (fail-open approach)

#### 3. Updated useAuth Hook (`frontend/src/hooks/useAuth.tsx`)
- **Enhanced**: Checks 2FA status after successful login
- **Flow**: Redirects to 2FA verification before dashboard access

#### 4. Updated Middleware (`frontend/middleware.ts`)
- **Enhanced**: Handles 2FA verification routes
- **Flow**: Allows authenticated users to access 2FA verification page

## API Endpoints

### New Endpoints

#### 1. `POST /api/verify-2fa-session`
- **Purpose**: Verify 2FA code for authenticated users
- **Request**: `{ "code": "123456" }`
- **Response**: 
  ```json
  {
    "success": true,
    "message": "Two-factor authentication verified successfully",
    "data": {
      "user_id": 123,
      "verification_complete": true
    }
  }
  ```

#### 2. `GET /api/check-2fa-status`
- **Purpose**: Check if 2FA verification is required
- **Response**:
  ```json
  {
    "success": true,
    "data": {
      "requires_2fa_verification": true,
      "user_id": 123
    }
  }
  ```

### Updated Endpoints

#### 1. `POST /api/login`
- **Enhanced**: Returns 2FA requirement status
- **Response** (when 2FA required):
  ```json
  {
    "success": false,
    "message": "2FA code required",
    "data": {
      "requires_2fa": true,
      "user_id": 123
    }
  }
  ```

## User Flow

### 1. Login Flow
```
User Login → Check 2FA Status → 
├─ 2FA Disabled → Dashboard Access
└─ 2FA Enabled → 2FA Verification Page → Dashboard Access
```

### 2. Protected Route Access
```
Access Protected Route → AuthGuard Check → 
├─ Not Authenticated → Login Page
├─ Authenticated, No 2FA → Dashboard Access
└─ Authenticated, 2FA Enabled → Check Session → 
   ├─ 2FA Verified → Dashboard Access
   └─ 2FA Not Verified → 2FA Verification Page
```

### 3. API Request Flow
```
API Request → TwoFactorMiddleware → 
├─ No Auth → Continue
├─ Auth, No 2FA → Continue
└─ Auth, 2FA Enabled → Check Session → 
   ├─ 2FA Verified → Continue
   └─ 2FA Not Verified → Return 403 with verification requirement
```

## Security Features

### 1. Session-Based Verification
- **Session Keys**: `2fa_verified_{user_id}`
- **Scope**: Per-session verification
- **Persistence**: Session-based, cleared on logout

### 2. Rate Limiting
- **2FA Routes**: 10 requests per minute
- **Failed Attempts**: Tracks and limits failed verification attempts

### 3. Error Handling
- **Graceful Degradation**: Fail-open approach for 2FA status checks
- **User Feedback**: Clear error messages and remaining attempts
- **Logging**: Comprehensive logging for security events

### 4. Admin Impersonation
- **Bypass**: 2FA verification skipped for admin impersonation
- **Header**: Uses `X-Impersonating` header to identify impersonation

## Configuration

### Backend Configuration

#### 1. Middleware Registration
```php
// backend/app/Http/Kernel.php
protected $middlewareAliases = [
    '2fa' => \App\Http\Middleware\TwoFactorMiddleware::class,
];
```

#### 2. Route Protection
```php
// backend/routes/api.php
Route::middleware(['auth:sanctum', 'rate_limit:api,60,1', '2fa'])->group(function () {
    // Protected routes
});
```

### Frontend Configuration

#### 1. Route Protection
```typescript
// frontend/middleware.ts
const twoFactorRoutes = [
  '/auth/verify-2fa'
];
```

#### 2. AuthGuard Enhancement
```typescript
// frontend/src/components/AuthGuard.tsx
// Check 2FA verification requirement for authenticated users
if (requireAuth && isAuthenticated && user) {
  if (user.two_factor_confirmed_at) {
    // Check 2FA verification status
  }
}
```

## Testing

### Manual Testing

#### 1. Test User Setup
```bash
# Run test script
php backend/test_2fa_verification.php
```

#### 2. Test Scenarios
- **Login with 2FA enabled**: Should redirect to verification page
- **Access protected route**: Should require 2FA verification
- **API requests**: Should return 403 if 2FA not verified
- **Session persistence**: 2FA verification should persist in session
- **Admin impersonation**: Should bypass 2FA verification

### Automated Testing

#### 1. Unit Tests
- Test User model methods
- Test middleware logic
- Test controller methods

#### 2. Integration Tests
- Test complete login flow
- Test protected route access
- Test API request handling

## Troubleshooting

### Common Issues

#### 1. 2FA Verification Not Working
- **Check**: User has `two_factor_confirmed_at` set
- **Check**: Session key `2fa_verified_{user_id}` exists
- **Check**: Middleware is properly registered

#### 2. Infinite Redirect Loop
- **Check**: 2FA verification page is accessible
- **Check**: Session handling is working correctly
- **Check**: AuthGuard logic is not conflicting

#### 3. API Requests Failing
- **Check**: TwoFactorMiddleware is applied to routes
- **Check**: Session handling for API requests
- **Check**: Error response format

### Debug Steps

#### 1. Check User Status
```php
$user = Auth::user();
echo "2FA Enabled: " . ($user->hasTwoFactorEnabled() ? 'Yes' : 'No');
echo "Session Verified: " . (session()->has('2fa_verified_' . $user->id) ? 'Yes' : 'No');
```

#### 2. Check Middleware
```php
// Add logging to TwoFactorMiddleware
Log::info('2FA check', [
    'user_id' => $user->id,
    'has_2fa' => $user->hasTwoFactorEnabled(),
    'session_verified' => $request->session()->has('2fa_verified_' . $user->id)
]);
```

#### 3. Check Frontend
```javascript
// Add logging to AuthGuard
console.log('2FA check', {
    user: user,
    has2FA: user?.two_factor_confirmed_at,
    requiresVerification: response?.data?.requires_2fa_verification
});
```

## Future Enhancements

### 1. Remember Device
- **Feature**: Remember 2FA verification for trusted devices
- **Implementation**: Store device fingerprint in user preferences

### 2. Backup Codes
- **Feature**: Allow users to use backup codes for 2FA
- **Implementation**: Generate and store backup codes during 2FA setup

### 3. Email 2FA
- **Feature**: Email-based 2FA as alternative to authenticator apps
- **Implementation**: Send 2FA codes via email

### 4. Biometric 2FA
- **Feature**: Support for biometric authentication
- **Implementation**: Integrate with device biometric APIs

## Security Considerations

### 1. Session Security
- **Session Keys**: Use unique, user-specific session keys
- **Session Expiry**: Implement proper session timeout
- **Session Regeneration**: Regenerate session after 2FA verification

### 2. Rate Limiting
- **2FA Attempts**: Limit failed 2FA verification attempts
- **Account Lockout**: Implement temporary account lockout after multiple failures
- **IP Tracking**: Track and limit attempts by IP address

### 3. Audit Logging
- **2FA Events**: Log all 2FA-related events
- **Security Events**: Log failed verification attempts
- **User Actions**: Track user 2FA setup and changes

### 4. Data Protection
- **Secret Storage**: Securely store 2FA secrets
- **Data Encryption**: Encrypt sensitive 2FA data
- **Access Control**: Limit access to 2FA-related data

## Conclusion

This 2FA verification system provides comprehensive security for the BizInvestify platform by ensuring that users with enabled 2FA must complete verification after login before accessing any part of the application. The system is designed to be secure, user-friendly, and maintainable. 