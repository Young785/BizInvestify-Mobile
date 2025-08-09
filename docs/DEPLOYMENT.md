## Deployment Guide

### Backend (Laravel)
1. Provision PHP 8.2+, Nginx, MySQL/Postgres, Redis (optional)
2. Configure `.env` (app url, db, mail, Stripe/Paystack)
3. Install & optimize:
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:work --daemon
```
4. Configure web server to serve `backend/public`
5. Configure HTTPS and webhooks:
   - Stripe webhook: `POST {APP_URL}/api/webhooks/stripe`
   - Paystack webhook: `POST {APP_URL}/api/webhooks/paystack`

### Frontend (Next.js)
1. Set env in hosting platform:
```
NEXT_PUBLIC_API_URL=https://api.yourdomain.com/api
NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY=pk_live_...
```
2. Build and serve:
```bash
npm ci
npm run build
npm run start
```
3. Configure reverse proxy and HTTPS

### Monitoring & Logs
- Backend: Laravel logs, error tracking, uptime checks
- Payments: Stripe/Paystack dashboards and webhooks
- Frontend: Errors and performance metrics (e.g. Vercel/Analytics)

### Production Checklist
- Secrets set per environment
- HTTPS enforced
- Webhooks verified
- Backups configured
- Rate limits tuned
- Queue workers running


