# BizInvestify Technical Specification

## 🏗️ Architecture Overview

### Frontend Architecture (Next.js 14)
```
src/
├── app/                    # App Router (Next.js 14)
│   ├── (auth)/            # Authentication routes
│   ├── (dashboard)/       # Protected dashboard routes
│   ├── (marketplace)/     # Public marketplace routes
│   └── api/               # API routes
├── components/            # Reusable components
│   ├── ui/               # Base UI components
│   ├── forms/            # Form components
│   ├── layout/           # Layout components
│   └── features/         # Feature-specific components
├── hooks/                # Custom React hooks
├── lib/                  # Utilities and configurations
├── types/                # TypeScript type definitions
└── styles/               # Global styles and Tailwind config
```

### Backend Architecture
- **API Routes**: Next.js API routes for backend functionality
- **Database**: PostgreSQL with Prisma ORM
- **Authentication**: NextAuth.js with JWT
- **File Storage**: AWS S3 or Cloudinary for images/documents
- **Real-time**: Socket.io for chat functionality

## 🗄️ Database Schema

### Core Tables

#### Users
```sql
users (
  id UUID PRIMARY KEY,
  email VARCHAR UNIQUE NOT NULL,
  password_hash VARCHAR,
  role ENUM('seller', 'buyer', 'admin') NOT NULL,
  first_name VARCHAR,
  last_name VARCHAR,
  phone VARCHAR,
  avatar_url VARCHAR,
  kyc_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
  kyc_documents JSONB,
  trust_score DECIMAL(3,2) DEFAULT 0.00,
  is_verified BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
)
```

#### Products
```sql
products (
  id UUID PRIMARY KEY,
  seller_id UUID REFERENCES users(id),
  title VARCHAR NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  currency VARCHAR(3) DEFAULT 'USD',
  category VARCHAR,
  tags TEXT[],
  images JSONB,
  status ENUM('active', 'sold', 'inactive') DEFAULT 'active',
  inventory_count INTEGER DEFAULT 1,
  views_count INTEGER DEFAULT 0,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
)
```

#### Businesses
```sql
businesses (
  id UUID PRIMARY KEY,
  seller_id UUID REFERENCES users(id),
  name VARCHAR NOT NULL,
  description TEXT,
  industry VARCHAR,
  valuation DECIMAL(15,2),
  funding_goal DECIMAL(15,2),
  equity_offered DECIMAL(5,2),
  pitch_deck_url VARCHAR,
  business_plan TEXT,
  revenue DECIMAL(15,2),
  profit_margin DECIMAL(5,2),
  employees_count INTEGER,
  founded_year INTEGER,
  location VARCHAR,
  status ENUM('active', 'funded', 'sold', 'inactive') DEFAULT 'active',
  views_count INTEGER DEFAULT 0,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
)
```

#### Investments
```sql
investments (
  id UUID PRIMARY KEY,
  investor_id UUID REFERENCES users(id),
  business_id UUID REFERENCES businesses(id),
  amount DECIMAL(15,2) NOT NULL,
  equity_percentage DECIMAL(5,2),
  status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
  message TEXT,
  created_at TIMESTAMP DEFAULT NOW(),
  updated_at TIMESTAMP DEFAULT NOW()
)
```

#### Messages
```sql
messages (
  id UUID PRIMARY KEY,
  sender_id UUID REFERENCES users(id),
  receiver_id UUID REFERENCES users(id),
  listing_id UUID, -- Can be product or business
  listing_type ENUM('product', 'business'),
  message TEXT NOT NULL,
  is_read BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT NOW()
)
```

#### Transactions
```sql
transactions (
  id UUID PRIMARY KEY,
  buyer_id UUID REFERENCES users(id),
  seller_id UUID REFERENCES users(id),
  listing_id UUID,
  listing_type ENUM('product', 'business'),
  amount DECIMAL(15,2) NOT NULL,
  commission_amount DECIMAL(15,2),
  payment_method VARCHAR,
  status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  stripe_payment_intent_id VARCHAR,
  created_at TIMESTAMP DEFAULT NOW()
)
```

## 🔌 API Endpoints

### Authentication
```
POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout
GET  /api/auth/me
POST /api/auth/kyc-upload
```

### Users
```
GET    /api/users/profile
PUT    /api/users/profile
GET    /api/users/{id}
PUT    /api/users/kyc-status
```

### Products
```
GET    /api/products
POST   /api/products
GET    /api/products/{id}
PUT    /api/products/{id}
DELETE /api/products/{id}
GET    /api/products/search
```

### Businesses
```
GET    /api/businesses
POST   /api/businesses
GET    /api/businesses/{id}
PUT    /api/businesses/{id}
DELETE /api/businesses/{id}
GET    /api/businesses/search
```

### Investments
```
GET    /api/investments
POST   /api/investments
GET    /api/investments/{id}
PUT    /api/investments/{id}
GET    /api/businesses/{id}/investments
```

### Messages
```
GET    /api/messages
POST   /api/messages
GET    /api/messages/conversations
PUT    /api/messages/{id}/read
```

### Transactions
```
GET    /api/transactions
POST   /api/transactions
GET    /api/transactions/{id}
```

## 🎨 Component Structure

### UI Components
```typescript
// Base UI Components
- Button (Primary, Secondary, Outline, Ghost)
- Input (Text, Email, Password, Textarea)
- Card (Default, Hover, Interactive)
- Modal (Default, Confirmation, Form)
- Badge (Status, Category, Notification)
- Avatar (User, Business)
- Loading (Spinner, Skeleton)
- Alert (Success, Error, Warning, Info)
```

### Layout Components
```typescript
// Layout Components
- Header (with navigation and user menu)
- Sidebar (collapsible navigation)
- Footer
- Container (responsive wrapper)
- Grid (responsive grid system)
```

### Feature Components
```typescript
// Seller Portal Components
- DashboardStats
- ProductForm
- BusinessForm
- ListingCard
- EarningsChart
- KYCStatus

// Buyer Portal Components
- MarketplaceGrid
- SearchFilters
- InvestmentModal
- CartSummary
- OrderHistory

// Admin Portal Components
- AdminDashboard
- UserManagement
- ListingModeration
- AnalyticsChart
```

## 🔐 Security Implementation

### Authentication Flow
1. **Registration**: Email/password with role selection
2. **Login**: Email/password with JWT token
3. **KYC**: Document upload and verification
4. **Role-based Access**: Seller, Buyer, Admin permissions

### Data Protection
- Password hashing with bcrypt
- JWT token expiration and refresh
- Input validation and sanitization
- SQL injection prevention with Prisma
- XSS protection with Content Security Policy

### Payment Security
- Stripe integration for secure payments
- PCI compliance for payment data
- Encrypted transaction storage
- Fraud detection and monitoring

## 📱 Mobile App Considerations

### React Native Structure
```
src/
├── components/           # Reusable components
├── screens/             # Screen components
├── navigation/          # Navigation configuration
├── services/            # API services
├── store/              # Redux store
├── utils/              # Utility functions
└── types/              # TypeScript types
```

### Mobile-Specific Features
- **Camera Integration**: Product photo capture
- **Push Notifications**: Real-time updates
- **Offline Storage**: Local data caching
- **Biometric Auth**: Fingerprint/Face ID
- **Location Services**: Nearby listings

## 🚀 Deployment Strategy

### Development Environment
- **Local**: Next.js dev server with PostgreSQL
- **Staging**: Vercel preview deployments
- **Production**: Vercel with custom domain

### Database Migration
- **Development**: Local PostgreSQL
- **Staging**: Supabase or Railway
- **Production**: AWS RDS or Supabase

### Environment Variables
```env
# Database
DATABASE_URL=
NEXTAUTH_SECRET=
NEXTAUTH_URL=

# Authentication
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=

# Payments
STRIPE_PUBLISHABLE_KEY=
STRIPE_SECRET_KEY=

# File Storage
CLOUDINARY_URL=
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_REGION=
AWS_BUCKET_NAME=

# Real-time
PUSHER_APP_ID=
PUSHER_KEY=
PUSHER_SECRET=
```

## 📊 Performance Optimization

### Frontend
- **Code Splitting**: Dynamic imports for routes
- **Image Optimization**: Next.js Image component
- **Caching**: React Query for API data
- **Bundle Analysis**: Webpack bundle analyzer

### Backend
- **Database Indexing**: Optimized queries
- **API Caching**: Redis for frequently accessed data
- **CDN**: Static asset delivery
- **Compression**: Gzip/Brotli compression

## 🔄 Development Workflow

### Git Flow
1. **Feature Branches**: `feature/component-name`
2. **Bug Fixes**: `fix/issue-description`
3. **Hotfixes**: `hotfix/critical-fix`
4. **Releases**: `release/version-number`

### Code Quality
- **ESLint**: Code linting and formatting
- **Prettier**: Code formatting
- **Husky**: Pre-commit hooks
- **TypeScript**: Type safety
- **Testing**: Jest and React Testing Library

---

**Note**: This specification will be updated as development progresses and requirements evolve. 