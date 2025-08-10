# BizInvestify Mobile App

A comprehensive Flutter mobile application for the BizInvestify platform - a hybrid marketplace for products, businesses, and investments.

## 🚀 Features

### ✅ **Complete Authentication System**
- **User Registration & Login**: Email/password authentication
- **Role-Based Access**: Buyer, Seller, Admin roles
- **KYC Verification**: Identity verification system
- **Profile Management**: Complete user profile with settings
- **Security**: Password management and account security

### ✅ **Marketplace Features**
- **Product Listings**: Browse and search products
- **Business Listings**: Investment opportunities
- **Advanced Search**: Filter by category, industry, price
- **Real-time Updates**: Live data synchronization
- **Favorites & Wishlist**: Save items for later

### ✅ **Messaging System**
- **Real-time Chat**: Instant messaging between users
- **Conversation Management**: Organized chat threads
- **File Sharing**: Send images and documents
- **Notifications**: Push notifications for messages
- **Unread Counts**: Track unread messages

### ✅ **Dashboard & Analytics**
- **Business Overview**: Revenue, products, listings stats
- **Investment Tracking**: Monitor investment performance
- **Activity Feed**: Recent activities and updates
- **Quick Actions**: Fast access to common tasks
- **Performance Metrics**: Detailed analytics and insights

### ✅ **Profile & Settings**
- **User Profile**: Complete profile management
- **KYC Status**: Verification status tracking
- **Security Settings**: Password, 2FA, privacy
- **Notification Preferences**: Customize alerts
- **Help & Support**: In-app support system

## 🛠️ Technical Stack

### **Frontend Framework**
- **Flutter 3.32.5**: Cross-platform mobile development
- **Dart 3.8.1**: Programming language
- **Material Design 3**: Modern UI components

### **State Management**
- **Riverpod**: Advanced state management
- **Provider Pattern**: Dependency injection
- **Async State**: Loading, error, success states

### **API & Networking**
- **Dio**: HTTP client with interceptors
- **Retrofit**: Type-safe API client
- **JSON Serialization**: Automatic data parsing
- **Error Handling**: Comprehensive error management

### **Storage & Security**
- **Flutter Secure Storage**: Secure token storage
- **Shared Preferences**: Local data persistence
- **Biometric Authentication**: Face ID/Touch ID support

### **UI/UX Components**
- **Custom Design System**: Brand-consistent components
- **Responsive Design**: Adapts to all screen sizes
- **Animations**: Smooth transitions and micro-interactions
- **Accessibility**: Screen reader and accessibility support

## 📱 App Screens

### **1. Splash Screen**
- Animated logo and branding
- App initialization
- Smooth transition to onboarding

### **2. Onboarding**
- 4 informative slides
- Feature introduction
- Skip option available

### **3. Authentication**
- **Login Screen**: Email/password login
- **Register Screen**: User registration with role selection
- **Forgot Password**: Password recovery flow

### **4. Dashboard**
- **Welcome Message**: Personalized greeting
- **Stats Cards**: Revenue, products, listings, messages
- **Quick Actions**: Add product, list business, messages, analytics
- **Bottom Navigation**: Dashboard, Marketplace, Messages, Profile

### **5. Marketplace**
- **Products Tab**: Grid layout with search and filters
- **Businesses Tab**: Investment opportunities
- **Search & Filter**: Real-time search with category filters
- **Product Cards**: Images, pricing, seller info
- **Business Cards**: Valuation, funding goals, equity

### **6. Messages**
- **Conversation List**: All chat threads
- **Unread Badges**: Message count indicators
- **Search Conversations**: Find specific chats
- **Empty State**: Helpful guidance for new users

### **7. Profile**
- **User Info**: Avatar, name, email, role
- **KYC Status**: Verification status
- **Menu Items**: Edit profile, KYC, security, notifications
- **Logout**: Secure logout with confirmation

## 🔧 Setup Instructions

### **Prerequisites**
- Flutter SDK 3.32.5 or higher
- Dart 3.8.1 or higher
- Android Studio / VS Code
- Git

### **Installation**

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd bizinvestify_mobile
   ```

2. **Install dependencies**
   ```bash
   flutter pub get
   ```

3. **Configure API endpoint**
   - Update `lib/src/core/services/api_service.dart`
   - Set `_baseUrl` to your backend API URL
   - Default: `http://127.0.0.1:8000/api`

4. **Run the app**
   ```bash
   # For web (recommended for testing)
   flutter run -d chrome
   
   # For Android
   flutter run -d android
   
   # For iOS
   flutter run -d ios
   ```

### **Environment Configuration**

Create a `.env` file in the root directory:
```env
API_BASE_URL=http://127.0.0.1:8000/api
APP_NAME=BizInvestify
APP_VERSION=1.0.0
```

## 📊 API Integration

### **Authentication Endpoints**
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout
- `GET /api/auth/user` - Get current user

### **Marketplace Endpoints**
- `GET /api/products` - Get products with filters
- `GET /api/businesses` - Get businesses with filters
- `POST /api/products` - Create product
- `POST /api/businesses` - Create business

### **Messaging Endpoints**
- `GET /api/conversations` - Get conversations
- `GET /api/conversations/{id}/messages` - Get messages
- `POST /api/conversations/{id}/messages` - Send message

### **Dashboard Endpoints**
- `GET /api/dashboard/stats` - Get dashboard analytics
- `GET /api/dashboard/activity` - Get recent activity

## 🎨 Design System

### **Color Palette**
- **Primary Blue**: #1A73E8 (Trust Blue)
- **Accent Green**: #00C48C (Mint Green)
- **Text Dark**: #1C1C1E (Dark Charcoal)
- **Background**: #F5F7FA (Soft Light Grey)

### **Typography**
- **Font Family**: Inter
- **Font Weights**: Regular (400), Medium (500), Semibold (600), Bold (700)
- **Text Sizes**: Display, Headline, Title, Body, Caption

### **Spacing System**
- **Base Unit**: 8px grid system
- **Spacing Scale**: 4px, 8px, 12px, 16px, 24px, 32px, 48px, 64px

### **Border Radius**
- **Small**: 8px (buttons, small cards)
- **Medium**: 12px (cards, modals)
- **Large**: 16px (large cards, containers)

## 🧪 Testing

### **Unit Tests**
```bash
flutter test
```

### **Widget Tests**
```bash
flutter test test/widget_test.dart
```

### **Integration Tests**
```bash
flutter test integration_test/
```

## 📦 Build & Deploy

### **Android APK**
```bash
flutter build apk --release
```

### **Android App Bundle**
```bash
flutter build appbundle --release
```

### **iOS Archive**
```bash
flutter build ios --release
```

### **Web Build**
```bash
flutter build web --release
```

## 🔒 Security Features

- **Secure Token Storage**: Encrypted token storage
- **API Authentication**: Bearer token headers
- **Input Validation**: Form validation and sanitization
- **Error Handling**: Secure error messages
- **Session Management**: Automatic token refresh

## 📱 Platform Support

- **Android**: API level 21+ (Android 5.0+)
- **iOS**: iOS 12.0+
- **Web**: Modern browsers (Chrome, Firefox, Safari, Edge)
- **Desktop**: Windows, macOS, Linux (experimental)

## 🚀 Performance Optimizations

- **Image Caching**: Efficient image loading and caching
- **Lazy Loading**: On-demand content loading
- **State Management**: Optimized state updates
- **Memory Management**: Efficient memory usage
- **Network Optimization**: Request caching and optimization

## 📞 Support

For support and questions:
- **Email**: support@bizinvestify.com
- **Documentation**: [API Documentation](docs/API.md)
- **Issues**: [GitHub Issues](https://github.com/your-org/bizinvestify/issues)

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## 📈 Roadmap

### **Phase 1: Core Features** ✅
- [x] Authentication system
- [x] Marketplace features
- [x] Messaging system
- [x] Profile management

### **Phase 2: Advanced Features** 🔄
- [ ] Real-time notifications
- [ ] Payment integration
- [ ] Advanced analytics
- [ ] Offline support

### **Phase 3: Enterprise Features** 📋
- [ ] Multi-language support
- [ ] Advanced security
- [ ] Performance monitoring
- [ ] Custom branding

---

**BizInvestify Mobile App** - Your Business, Your Investment, Your Success! 🚀

# BizInvestify Mobile - Payments

## Stripe Setup
- Add your Stripe publishable key via Dart define at build time:

```
flutter run --dart-define=STRIPE_PUBLISHABLE_KEY=pk_test_xxx
```

- The app reads it from `kStripePublishableKey` and initializes Stripe on startup.
- PaymentSheet will try to use Stripe UI for Card payments and fallback to backend confirm if unavailable.

## Paystack Flow
- When using Paystack payment for products, the app opens `authorization_url` externally and then polls `/payments/status/{id}`.

## Bank Transfer
- Bank transfer creates a pending transaction and instructs the user to transfer manually; the app polls status.

## Wallet
- Wallet Top Up uses Card or Bank Transfer (Paystack hidden for wallet context).
- Withdraw requests currently post a support ticket until a dedicated endpoint is provided.
