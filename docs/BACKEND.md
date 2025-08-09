## Backend Architecture (Laravel)

### Stack
- Laravel 12, PHP 8.2+
- Sanctum for API auth (Bearer tokens)
- Spatie Permission (roles/permissions)
- Stripe and Paystack integrations

### Key Directories
- `app/Http/Controllers/Api/*`: Feature controllers (auth, products, payments, etc.)
- `app/Services/*`: Business logic services (Marketplace, Payment)
- `app/Http/Middleware/*`: Security, 2FA, rate limiting
- `routes/api.php`: All API routes (public + authenticated)
- `config/services.php`: Provider configs (Stripe, Paystack, Mail, etc.)

### Auth & Verification Flow
Steps enforced via `AuthController` and `TwoFactorMiddleware`:
1. Registration → email verification (`/verify-email` or OTP)
2. Phone verification (`/send-phone-verification`, `/verify-phone`)
3. KYC upload/submit (`/kyc-upload`, `/kyc/submit`)
4. 2FA setup (TOTP or email 2FA) and confirmation

Middleware `TwoFactorMiddleware` requires 2FA session verification for protected API routes (403 with `requires_2fa_verification` when missing).

### Roles & Permissions
- Uses Spatie `spatie/laravel-permission`.
- Routes grouped with `permission:*` gates in `routes/api.php`.

### Payments
- Implemented in `App\Services\PaymentService` and `PaymentController`.
- Stripe: product purchase and business investment via PaymentIntents.
- Paystack: initialize transaction, redirect to authorization_url, verify by reference.
- Escrow: create escrow transaction and release with conditions.
- Commission: `calculateCommission(amount, type)` with rates from env.

### Marketplace
- `MarketplaceService` computes stats, trending, recommendations, analytics, health.
- Public endpoints under `/api/public/*` for read-only marketplace data.

### Important Endpoints (selection)
- Auth: `/register`, `/login`, `/logout`, `/me`, password reset, email/phone verification.
- 2FA: `/setup-2fa`, `/confirm-2fa`, `/verify-2fa`, `/verify-2fa-session`, `/check-2fa-status`.
- Products/Businesses: CRUD protected by permissions; public show/index available.
- Payments: Stripe intents, Paystack init/verify, escrow create/release, wallet/transactions.
- Marketplace: `/marketplace/stats`, `/marketplace/recommendations`, `/marketplace/featured`, `/marketplace/trending`.

See `docs/API_REFERENCE.md` for a concise reference list.


