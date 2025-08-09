# BizInvestify Payment Integration Guide

## 🎯 Overview

This guide covers the complete payment system integration for BizInvestify, including Stripe, Paystack, escrow services, and wallet management.

## 🏗️ Architecture

### Payment Providers
- **Stripe**: Primary payment processor for international transactions
- **Paystack**: Regional payment processor for African markets
- **Escrow System**: Internal escrow for business acquisitions
- **Wallet System**: Internal balance management

### Transaction Types
1. **Product Purchases**: Direct payments for products
2. **Business Investments**: Investment payments with equity tracking
3. **Business Acquisitions**: Escrow-based transactions
4. **Refunds**: Partial or full refunds
5. **Payouts**: Seller payouts and commission distributions

## 🔧 Setup & Configuration

### Environment Variables

```bash
# Stripe Configuration
STRIPE_SECRET=sk_test_...
STRIPE_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_COMMISSION_RATE=0.05
STRIPE_INVESTMENT_FEE_RATE=0.03

# Paystack Configuration
PAYSTACK_SECRET_KEY=sk_test_...
PAYSTACK_PUBLIC_KEY=pk_test_...
PAYSTACK_WEBHOOK_SECRET=whsec_...
PAYSTACK_COMMISSION_RATE=0.05
PAYSTACK_INVESTMENT_FEE_RATE=0.03
```

### Database Schema

```sql
-- Transactions table
CREATE TABLE transactions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('purchase', 'investment', 'escrow', 'refund', 'payout', 'deposit') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    payment_method ENUM('stripe', 'paystack', 'escrow', 'transfer') NOT NULL,
    reference_id VARCHAR(255),
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add payment fields to users table
ALTER TABLE users ADD COLUMN stripe_customer_id VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN stripe_account_id VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN paystack_customer_code VARCHAR(255) NULL;
```

## 📱 API Endpoints

### Stripe Payments

#### Product Purchase
```http
POST /api/payments/product-intent
Content-Type: application/json
Authorization: Bearer {token}

{
    "product_id": 123,
    "quantity": 1
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "client_secret": "pi_..._secret_...",
        "payment_intent_id": "pi_...",
        "transaction_id": 456,
        "amount": 99.99
    }
}
```

#### Business Investment
```http
POST /api/payments/investment-intent
Content-Type: application/json
Authorization: Bearer {token}

{
    "business_id": 789,
    "amount": 5000.00
}
```

### Paystack Payments

#### Product Purchase
```http
POST /api/payments/paystack/product
Content-Type: application/json
Authorization: Bearer {token}

{
    "product_id": 123,
    "quantity": 1
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "authorization_url": "https://checkout.paystack.com/...",
        "reference": "BIZ_1234567890_123",
        "transaction_id": 456,
        "amount": 9999
    }
}
```

#### Payment Verification
```http
POST /api/payments/paystack/verify
Content-Type: application/json
Authorization: Bearer {token}

{
    "reference": "BIZ_1234567890_123"
}
```

### Escrow System

#### Create Escrow Transaction
```http
POST /api/payments/escrow/create
Content-Type: application/json
Authorization: Bearer {token}

{
    "business_id": 789,
    "amount": 50000.00
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "escrow_id": "ESCROW_1234567890_123",
        "transaction_id": 456,
        "amount": 50000.00,
        "status": "pending"
    }
}
```

#### Release Escrow Funds
```http
POST /api/payments/escrow/release
Content-Type: application/json
Authorization: Bearer {token}

{
    "escrow_id": "ESCROW_1234567890_123",
    "conditions": {
        "due_diligence_completed": true,
        "legal_documents_signed": true,
        "transfer_completed": true
    }
}
```

### Wallet Management

#### Get Wallet Balance
```http
GET /api/payments/wallet-balance
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "balance": 1250.75,
        "currency": "USD"
    }
}
```

#### Transaction History
```http
GET /api/payments/transaction-history?limit=20&type=purchase
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 123,
            "type": "purchase",
            "amount": 99.99,
            "currency": "USD",
            "status": "completed",
            "payment_method": "stripe",
            "created_at": "2024-01-15T10:30:00Z"
        }
    ],
    "total": 1
}
```

## 🔄 Webhook Handling

### Stripe Webhooks

**Endpoint:** `POST /api/webhooks/stripe`

**Events Handled:**
- `payment_intent.succeeded`: Confirm payment and update transaction
- `payment_intent.payment_failed`: Mark transaction as failed
- `account.updated`: Update seller account status

### Paystack Webhooks

**Endpoint:** `POST /api/webhooks/paystack`

**Events Handled:**
- `charge.success`: Verify and confirm payment
- `transfer.success`: Confirm seller payout

## 💳 Frontend Integration

### Stripe Elements Integration

```typescript
import { loadStripe } from '@stripe/stripe-js';
import { Elements, CardElement, useStripe, useElements } from '@stripe/react-stripe-js';

const stripePromise = loadStripe(process.env.NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY!);

const PaymentForm = ({ clientSecret, onSuccess }) => {
  const stripe = useStripe();
  const elements = useElements();

  const handleSubmit = async (event) => {
    event.preventDefault();
    
    const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
      payment_method: {
        card: elements.getElement(CardElement),
      },
    });

    if (error) {
      console.error('Payment failed:', error);
    } else if (paymentIntent.status === 'succeeded') {
      onSuccess(paymentIntent);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <CardElement />
      <button type="submit">Pay</button>
    </form>
  );
};
```

### Paystack Integration

```typescript
const handlePaystackPayment = async (authorizationUrl: string) => {
  // Redirect to Paystack checkout
  window.location.href = authorizationUrl;
};

// Handle callback from Paystack
const verifyPaystackPayment = async (reference: string) => {
  try {
    const response = await api.post('/payments/paystack/verify', { reference });
    if (response.data.success) {
      // Payment successful
      console.log('Payment verified:', response.data);
    }
  } catch (error) {
    console.error('Payment verification failed:', error);
  }
};
```

## 🛡️ Security Features

### Payment Security
- **HTTPS Only**: All payment endpoints require HTTPS
- **Webhook Verification**: Signature verification for webhooks
- **Input Validation**: Comprehensive validation for all payment data
- **Rate Limiting**: API rate limiting to prevent abuse
- **Audit Logging**: Complete transaction logging

### Escrow Security
- **Multi-party Authorization**: Requires buyer, seller, and admin approval
- **Condition-based Release**: Funds only released when conditions are met
- **Dispute Resolution**: Built-in dispute handling system
- **Legal Compliance**: Meets regulatory requirements

## 📊 Commission System

### Commission Rates
- **Product Sales**: 5% platform commission
- **Business Investments**: 3% investment processing fee
- **Business Acquisitions**: 2% escrow fee

### Commission Calculation
```php
// Calculate commission
$commission = $paymentService->calculateCommission($amount, $type);

// Example: $100 product sale
$commission = $paymentService->calculateCommission(100, 'sale'); // Returns 5.00

// Example: $1000 investment
$commission = $paymentService->calculateCommission(1000, 'investment'); // Returns 30.00
```

## 🔄 Refund System

### Refund Process
1. **Request Refund**: User or admin initiates refund
2. **Validation**: Check if transaction is eligible for refund
3. **Process Refund**: Initiate refund through payment provider
4. **Update Records**: Create refund transaction and update original transaction
5. **Notify Parties**: Send notifications to buyer and seller

### Refund API
```http
POST /api/payments/refund
Content-Type: application/json
Authorization: Bearer {token}

{
    "transaction_id": 123,
    "amount": 50.00  // Optional: partial refund
}
```

## 📱 Mobile Integration

### Flutter Payment Integration

```dart
// Stripe payment in Flutter
Future<void> processStripePayment(String clientSecret) async {
  try {
    final paymentIntent = await Stripe.instance.confirmPayment(
      clientSecret,
      PaymentMethodParams.card(
        paymentMethodData: PaymentMethodData(),
      ),
    );
    
    if (paymentIntent.status == PaymentIntentsStatus.Succeeded) {
      // Payment successful
      print('Payment successful: ${paymentIntent.id}');
    }
  } catch (e) {
    print('Payment failed: $e');
  }
}

// Paystack payment in Flutter
Future<void> processPaystackPayment(String authorizationUrl) async {
  final result = await Navigator.push(
    context,
    MaterialPageRoute(
      builder: (context) => PaystackWebView(authorizationUrl: authorizationUrl),
    ),
  );
  
  if (result != null) {
    // Handle payment result
    await verifyPaystackPayment(result['reference']);
  }
}
```

## 🧪 Testing

### Test Cards

#### Stripe Test Cards
- **Success**: `4242 4242 4242 4242`
- **Decline**: `4000 0000 0000 0002`
- **3D Secure**: `4000 0025 0000 3155`

#### Paystack Test Cards
- **Success**: `4084 0840 8408 4081`
- **Decline**: `4084 0840 8408 4082`

### Test Scenarios
1. **Successful Payment**: Complete payment flow
2. **Failed Payment**: Handle declined cards
3. **Partial Refund**: Process partial refunds
4. **Escrow Release**: Test escrow conditions
5. **Webhook Handling**: Verify webhook processing

## 🚀 Deployment

### Production Checklist
- [ ] Set production API keys
- [ ] Configure webhook endpoints
- [ ] Set up SSL certificates
- [ ] Configure database backups
- [ ] Set up monitoring and alerts
- [ ] Test all payment flows
- [ ] Verify compliance requirements

### Monitoring
- **Transaction Monitoring**: Track all payment activities
- **Error Alerting**: Get notified of payment failures
- **Performance Metrics**: Monitor API response times
- **Fraud Detection**: Implement fraud prevention measures

## 📚 Best Practices

### Security
1. **Never store sensitive data**: Don't store card details
2. **Use webhooks**: Rely on webhooks for payment status
3. **Validate everything**: Validate all input data
4. **Log everything**: Maintain comprehensive audit logs
5. **Test thoroughly**: Test all payment scenarios

### User Experience
1. **Clear error messages**: Provide helpful error messages
2. **Loading states**: Show loading indicators during payment
3. **Confirmation emails**: Send payment confirmations
4. **Mobile optimization**: Ensure mobile-friendly payment flows
5. **Multiple payment options**: Offer various payment methods

### Compliance
1. **PCI DSS**: Ensure PCI compliance
2. **GDPR**: Handle personal data properly
3. **Local regulations**: Comply with local payment laws
4. **Tax reporting**: Handle tax reporting requirements
5. **Audit trails**: Maintain complete audit trails

## 🔧 Troubleshooting

### Common Issues

#### Payment Declined
- Check card details
- Verify card has sufficient funds
- Check for fraud filters
- Contact payment provider support

#### Webhook Failures
- Verify webhook endpoint URL
- Check webhook signature
- Ensure endpoint is accessible
- Review webhook logs

#### Escrow Issues
- Verify all conditions are met
- Check user permissions
- Review escrow terms
- Contact support if needed

### Debug Tools
- **Stripe Dashboard**: Monitor payments and webhooks
- **Paystack Dashboard**: Track transactions and transfers
- **Application Logs**: Review detailed error logs
- **Database Queries**: Check transaction records

## 📞 Support

### Payment Provider Support
- **Stripe**: https://support.stripe.com
- **Paystack**: https://paystack.com/support

### Internal Support
- **Technical Issues**: tech@bizinvestify.com
- **Payment Issues**: payments@bizinvestify.com
- **Escrow Support**: escrow@bizinvestify.com

---

*This guide is maintained by the BizInvestify development team and should be updated as the payment system evolves.*

**Last Updated**: December 2024  
**Version**: 1.0.0  
**Status**: Production Ready 