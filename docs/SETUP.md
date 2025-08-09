## Setup Guide

This guide covers local development for backend (Laravel) and frontend (Next.js).

### Prerequisites
- PHP 8.2+
- Composer 2.x
- Node.js 18+ and npm
- MySQL or PostgreSQL
- OpenSSL (for Laravel key)

### Backend Setup (Laravel)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate

# Configure DB in .env, then run migrations and seeders
php artisan migrate --graceful
php artisan db:seed

# Run the app (http://localhost:8000)
php artisan serve
```

#### Environment Variables (backend .env)
Set at minimum:
```
APP_NAME=BizInvestify
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

SANCTUM_STATEFUL_DOMAINS=localhost:3000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bizinvestify
DB_USERNAME=root
DB_PASSWORD=secret

# Stripe
STRIPE_SECRET=sk_test_...
STRIPE_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_COMMISSION_RATE=0.05
STRIPE_INVESTMENT_FEE_RATE=0.03

# Paystack
PAYSTACK_SECRET_KEY=sk_test_...
PAYSTACK_PUBLIC_KEY=pk_test_...
PAYSTACK_WEBHOOK_SECRET=whsec_...
PAYSTACK_COMMISSION_RATE=0.05
PAYSTACK_INVESTMENT_FEE_RATE=0.03

# Mail (choose one provider)
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_FROM_ADDRESS=no-reply@bizinvestify.test
MAIL_FROM_NAME="BizInvestify"
```

### Frontend Setup (Next.js)
```bash
cd frontend
npm install

# Configure .env.local
cat > .env.local << 'EOF'
NEXT_PUBLIC_API_URL=http://localhost:8000/api
NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY=pk_test_...
EOF

npm run dev
# App at http://localhost:3000
```

### Optional: Queues and Logs
```bash
cd backend
php artisan queue:work
php artisan pail --timeout=0
```

### Health Check
- Backend: GET `http://localhost:8000/api/public/marketplace/stats`
- Frontend: Open `http://localhost:3000`


