# Verification Flow Test Guide

## Test Steps

### 1. Start Backend Server
```bash
cd backend
php artisan serve
```

### 2. Start Frontend Server
```bash
cd frontend
npm run dev
```

### 3. Test Registration and Login Flow

#### Step 1: Register a new user
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "seller",
    "first_name": "Test",
    "last_name": "User"
  }'
```

#### Step 2: Check the response
- Should return success with user_id and email
- Check backend logs for verification status

#### Step 3: Try to login
- Visit http://localhost:3000/auth/login
- Login with test@example.com / password123
- Check browser console for debug logs
- Should redirect to email verification

#### Step 4: Complete email verification
- Check email logs in backend/storage/logs/laravel.log
- Copy verification token from logs
- Visit: http://localhost:3000/auth/verify-email?token=TOKEN&email=test@example.com
- Should redirect to phone verification

#### Step 5: Complete phone verification
- Should redirect to KYC

#### Step 6: Complete KYC
- Should redirect to 2FA setup

#### Step 7: Complete 2FA
- Should redirect to dashboard

### 4. Debug Information

#### Backend Logs
Check `backend/storage/logs/laravel.log` for:
- User login verification status
- Email verification attempts
- Verification progress calculations

#### Frontend Console
Check browser console for:
- Login response structure
- Verification progress values
- Redirect decisions

### 5. Expected Behavior

#### For New Users:
1. Register → Email verification needed
2. Login → Redirect to email verification
3. Complete email → Redirect to phone verification
4. Complete phone → Redirect to KYC
5. Complete KYC → Redirect to 2FA setup
6. Complete 2FA → Access dashboard

#### For Partially Verified Users:
- Login should redirect to the next incomplete step
- Dashboard access should be blocked until fully verified

### 6. Troubleshooting

#### If login redirects to dashboard instead of verification:
- Check backend logs for verification status
- Verify `requires_verification` flag in login response
- Check if user is actually unverified

#### If verification pages don't work:
- Check if verification tokens are being generated
- Verify email sending is working
- Check API endpoints are responding correctly

#### If dashboard access is blocked for verified users:
- Check verification progress calculation
- Verify all verification steps are marked as complete
- Check `isFullyVerified()` method logic 