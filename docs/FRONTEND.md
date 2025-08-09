## Frontend Architecture (Next.js)

### Stack
- Next.js 15, React 19, TypeScript
- Tailwind CSS, Framer Motion, Lucide
- Axios API client `src/lib/api/client.ts`

### Structure Highlights
- `src/app/*`: App Router pages (auth, dashboard, marketplace, etc.)
- `src/components/*`: UI, marketplace, reviews, wishlist, etc.
- `src/lib/api/*`: Typed API layer mapping to backend endpoints
- `middleware.ts`: Guards routes by role/auth and sets security headers

### API Client
- Base URL from `NEXT_PUBLIC_API_URL` (default `http://localhost:8000/api`).
- Injects `Authorization: Bearer <token>` from `localStorage`.
- Handles 401 (logout) and 403 (2FA redirect or permission denied).

### Auth & Verification UX
- After login, `handleLoginSuccess` persists token and user, then routes through verification steps based on `verification_progress`: email → phone → KYC → 2FA → dashboard.
- Middleware protects `'/dashboard'`, `'/auth/kyc'`, `'/auth/setup-2fa'`, `'/auth/verify-email'`, `'/auth/verify-phone'` and redirects unauthenticated users to `/auth/login`.

### Environment
Create `.env.local`:
```
NEXT_PUBLIC_API_URL=http://localhost:8000/api
NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY=pk_test_...
```


