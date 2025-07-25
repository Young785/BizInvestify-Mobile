# BizInvestify - Complete Setup Instructions

## 🚀 Full-Stack Application Setup

This guide will help you set up and run the complete BizInvestify platform with both frontend (Next.js) and backend (Laravel) working together.

## 📋 Prerequisites

Before starting, ensure you have the following installed:

- **Node.js** (v18 or higher) - [Download here](https://nodejs.org/)
- **PHP** (v8.2 or higher) - [Download here](https://www.php.net/downloads.php)
- **Composer** - [Download here](https://getcomposer.org/download/)
- **Git** - [Download here](https://git-scm.com/downloads)

## 🎯 Quick Start (Both Frontend & Backend)

### 1. Backend Setup (Laravel API)

```bash
# Navigate to backend directory
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Set up database (SQLite is preconfigured)
php artisan migrate

# Start Laravel development server
php artisan serve --host=0.0.0.0 --port=8000
```

The Laravel API will be available at: `http://localhost:8000`

### 2. Frontend Setup (Next.js)

```bash
# Open a new terminal and navigate to frontend directory
cd frontend

# Install Node.js dependencies
npm install

# Start Next.js development server
npm run dev
```

The Next.js frontend will be available at: `http://localhost:3000`

## 🔧 Detailed Configuration

### Backend Configuration (.env file)

The Laravel backend uses the following key configurations:

```env
APP_NAME="BizInvestify API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (SQLite - no additional setup needed)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Mail Configuration (for contact forms)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@bizinvestify.com"
MAIL_FROM_NAME="${APP_NAME}"
MAIL_ADMIN_EMAIL="admin@bizinvestify.com"
```

### Frontend Configuration

Create a `.env.local` file in the frontend directory:

```env
# API Configuration
NEXT_PUBLIC_API_URL=http://localhost:8000/api
```

## 🌟 Available Features

### ✅ Working Contact Form
The contact form is fully functional and will:
- Validate form data
- Send emails to admin
- Send confirmation emails to users
- Store submission logs
- Handle errors gracefully

### ✅ API Endpoints Available

**Public Endpoints:**
- `POST /api/contact` - Submit contact form
- `POST /api/register` - User registration
- `POST /api/login` - User login

**Protected Endpoints (require authentication):**
- `POST /api/logout` - User logout
- `GET /api/me` - Get current user profile
- `PUT /api/profile` - Update user profile
- API routes for products, businesses, investments, and messages

### ✅ Frontend Features
- **Multi-page Navigation**: Home, About, Features, Pricing, Contact
- **Working Contact Form**: Full validation and API integration
- **Authentication Ready**: Login/register forms connected to API
- **Professional Design**: Modern, responsive UI with animations
- **Error Handling**: Comprehensive error states and user feedback

## 🧪 Testing the Setup

### 1. Test Contact Form
1. Go to `http://localhost:3000/contact`
2. Fill out and submit the contact form
3. Check browser console for API calls
4. Check Laravel logs: `backend/storage/logs/laravel.log`

### 2. Test API Directly
```bash
# Test contact form API
curl -X POST http://localhost:8000/api/contact \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "firstName": "John",
    "lastName": "Doe", 
    "email": "john@example.com",
    "subject": "general",
    "message": "Test message",
    "consent": true
  }'
```

### 3. Test CORS
The CORS middleware is configured to allow requests from:
- `http://localhost:3000` (Next.js dev server)
- `http://127.0.0.1:3000`
- `https://bizinvestify.com` (production)

## 🚨 Troubleshooting

### Common Issues:

**1. Port Already in Use**
```bash
# Kill process using port 8000
lsof -ti:8000 | xargs kill -9

# Or use different port
php artisan serve --port=8001
```

**2. Database Connection Issues**
```bash
# Make sure SQLite database exists
touch backend/database/database.sqlite

# Run migrations again
php artisan migrate
```

**3. CORS Issues**
If you get CORS errors, ensure:
- Laravel server is running on port 8000
- Frontend is running on port 3000
- CORS middleware is properly configured

**4. Permission Issues (macOS/Linux)**
```bash
# Set proper permissions
chmod -R 755 backend/storage
chmod -R 755 backend/bootstrap/cache
```

## 📁 Project Structure

```
BizInvestify/
├── frontend/          # Next.js application
│   ├── src/
│   │   ├── app/       # App router pages
│   │   ├── components/ # React components
│   │   └── lib/       # API clients and utilities
│   └── package.json
├── backend/           # Laravel API
│   ├── app/
│   │   └── Http/
│   │       └── Controllers/Api/ # API controllers
│   ├── routes/api.php # API routes
│   └── composer.json
└── SETUP_INSTRUCTIONS.md
```

## 🔄 Development Workflow

### Making Changes:

**Frontend Changes:**
- Edit files in `frontend/src/`
- Changes auto-reload via Next.js hot reload
- Check browser console for errors

**Backend Changes:**
- Edit files in `backend/app/`
- Restart Laravel server if needed: `Ctrl+C` then `php artisan serve`
- Check logs: `backend/storage/logs/laravel.log`

### Adding New API Endpoints:
1. Create controller: `php artisan make:controller Api/YourController`
2. Add routes in `backend/routes/api.php`
3. Create frontend API client in `frontend/src/lib/api/`
4. Update frontend components to use new API

## 🌐 Production Deployment

### Backend (Laravel):
- Set `APP_ENV=production` in `.env`
- Configure real database (MySQL/PostgreSQL)
- Set up proper mail service (SendGrid, Mailgun, etc.)
- Configure web server (Apache/Nginx)

### Frontend (Next.js):
- Build: `npm run build`
- Deploy to Vercel, Netlify, or your preferred hosting
- Set production API URL in environment variables

## 📞 Support

If you encounter any issues:

1. Check the browser console for frontend errors
2. Check Laravel logs for backend errors: `backend/storage/logs/laravel.log`
3. Verify both servers are running on correct ports
4. Test API endpoints directly with curl or Postman

## 🎉 Success!

If everything is working correctly, you should see:
- ✅ Frontend running at `http://localhost:3000`
- ✅ Backend API running at `http://localhost:8000`
- ✅ Contact form submitting successfully
- ✅ No CORS errors in browser console
- ✅ API responses appearing in Laravel logs

Your BizInvestify platform is now fully functional! 🚀 