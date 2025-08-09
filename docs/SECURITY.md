## Security & Compliance

### Authentication & Sessions
- Sanctum Bearer tokens for API auth
- 2FA enforcement via `TwoFactorMiddleware` (403 + redirect hint)
- Email + phone verification + KYC gating

### Authorization
- Role-based and permission-based access using Spatie Permission
- Route-level `permission:*` guards

### Rate Limiting
- Granular middleware groups in `routes/api.php` (auth, password_reset, email_verify, phone_verify, kyc, 2fa, public_api, chat, api)

### Payments
- Stripe and Paystack API keys via env, server-side intents/initializations
- Webhook endpoints for post-payment events
- Escrow flow for acquisitions

### Secure Headers (frontend)
- `middleware.ts` sets: X-Frame-Options, X-Content-Type-Options, Referrer-Policy, X-XSS-Protection

### Data Protection
- Avoid storing sensitive card data (providers handle)
- File uploads stored under `storage/app/public` with validation

### Logging & Audit
- Auth events (login, failed login, registration) logged
- KYC and payment flows log key actions

### Environment Hygiene
- Never commit secrets
- Separate `.env` for local/staging/prod


