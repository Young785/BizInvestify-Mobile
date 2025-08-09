## Testing Guide

### Backend
```bash
cd backend
php artisan test
```

Useful local test scripts:
- `backend/test_api.php` – basic auth/profile flow
- `backend/test_2fa_api.php` – 2FA setup/verification
- `backend/test_2fa_verification.php` – session-based 2FA checks
- `backend/test_frontend_2fa_flow.php` – end-to-end verification flow with frontend expectations
- `backend/test_payment_api.php` – Stripe/Paystack/escrow flows
- `backend/test_forgot_password.php` – password reset

### Frontend
```bash
cd frontend
npm run build && npm run start
# TODO: add component/integration tests (e.g. Vitest/RTL or Playwright)
```

### Manual Scenarios
- Registration → email OTP → phone OTP → KYC upload → 2FA setup → dashboard
- Login with 2FA required → missing code → API returns 403 with `requires_2fa_verification`
- Stripe product payment (test card) → confirm → transaction status
- Paystack init → redirect → verify reference
- Escrow create/release conditions


