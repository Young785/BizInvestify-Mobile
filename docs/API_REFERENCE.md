## API Reference (Selected)

Base URL: `{BACKEND_URL}/api` (local: `http://localhost:8000/api`)

### Public Marketplace
- GET `/public/products`
- GET `/public/businesses`
- GET `/public/products/{id}`
- GET `/public/businesses/{id}`
- GET `/public/categories`
- GET `/public/featured`
- GET `/public/trending`
- GET `/public/search`
- GET `/public/marketplace/stats`
- GET `/public/marketplace/recommendations`

### Auth
- POST `/register`
- POST `/login`
- POST `/logout` (auth)
- GET `/me` (auth)
- POST `/forgot-password`
- POST `/reset-password`

### Email & Phone Verification
- POST `/verify-email`
- POST `/check-email-verification-status`
- POST `/resend-email-verification`
- POST `/send-email-verification-otp`
- POST `/verify-email-otp`
- POST `/send-phone-verification`
- POST `/verify-phone`

### KYC & 2FA (auth)
- POST `/kyc-upload`
- GET `/kyc/my-application`
- POST `/kyc/submit`
- POST `/setup-2fa`
- POST `/confirm-2fa`
- POST `/setup-email-2fa`
- POST `/confirm-email-2fa`
- POST `/skip-2fa`
- POST `/verify-2fa`
- POST `/disable-2fa`
- POST `/verify-2fa-session`
- GET `/check-2fa-status`

### Products (auth; some routes public)
- GET `/products`, `/products/{id}`, `/products/trending`, `/products/{id}/related`
- POST `/products` (permission: `products.create`)
- PUT `/products/{id}` (permission: `products.edit`)
- DELETE `/products/{id}` (permission: `products.delete`)

### Businesses (auth; some routes public)
- GET `/businesses`, `/businesses/{id}`, `/businesses/trending`, `/businesses/{id}/related`
- POST `/businesses` (permission: `businesses.create`)
- PUT `/businesses/{id}` (permission: `businesses.edit`)
- DELETE `/businesses/{id}` (permission: `businesses.delete`)

### Investments (auth)
- GET `/investments`, `/investments/{id}`, `/businesses/{id}/investments`
- POST `/investments` (permission: `investments.create`)
- PUT `/investments/{id}/approve|reject` (permission: `investments.approve`)

### Payments (auth)
- Stripe: POST `/payments/product-intent`, `/payments/investment-intent`, `/payments/confirm`
- Paystack: POST `/payments/paystack/product`, `/payments/paystack/investment`, `/payments/paystack/verify`
- Escrow: POST `/payments/escrow/create`, `/payments/escrow/release`, GET `/payments/escrow/{escrow_id}`
- Wallet: GET `/payments/wallet-balance`
- History: GET `/payments/transaction-history`
- Refund: POST `/payments/refund` (permission: `payments.refund`)
- Webhooks: POST `/webhooks/stripe`, `/webhooks/paystack`

### Search (auth)
- GET `/search/products`, `/search/businesses`, `/search/suggestions`, `/search/recommendations`

### Wishlist (auth)
- GET/POST/PUT/DELETE `/wishlist` and variants

### Reviews (auth)
- GET/POST/PUT/DELETE `/reviews`, POST `/reviews/{id}/helpful`

### Messaging/Chat (auth)
- REST routes under `/chat/*` and `/conversations` + `/messages`

### Admin (auth + permissions)
- Analytics: GET `/marketplace/analytics`, `/marketplace/search-insights`, `/marketplace/health`
- Users: CRUD under `/admin/users*` (+ suspend/activate/impersonate/export)
- KYC: `/admin/kyc/*`
- Transactions: `/admin/transactions*`
- Reports: `/admin/reports/*`
- Moderation: `/admin/moderation/*`
- Settings/System: `/admin/settings`, `/admin/system/*`

For detailed request/response schemas, see controller code under `backend/app/Http/Controllers/Api/*` and services.


