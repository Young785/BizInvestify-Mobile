import 'package:flutter_stripe/flutter_stripe.dart';
import 'package:flutter/material.dart';
import '../../core/services/api_service.dart';

class StripeService {
  Future<void> initialize({required String publishableKey}) async {
    Stripe.publishableKey = publishableKey;
    await Stripe.instance.applySettings();
  }

  Future<Map<String, dynamic>> payWithCard({
    required BuildContext context,
    required double amount,
    required String currency,
    int? productId,
    Map<String, dynamic>? metadata,
  }) async {
    // 1. Create intent via backend
    final intent = productId != null
        ? await apiService.createProductPaymentIntent(
            productId: productId,
            amount: amount,
            currency: currency,
          )
        : await apiService.createPaymentIntent({
            'amount': amount,
            'currency': currency,
            'metadata': metadata ?? {},
          });

    final clientSecret = intent['client_secret'] ?? intent['clientSecret'] ?? intent['client_secret_key'];
    if (clientSecret == null) {
      throw Exception('No client_secret returned by backend');
    }

    // 2. Present payment sheet (or card field) - simplified with CardField + confirm
    await Stripe.instance.initPaymentSheet(
      paymentSheetParameters: SetupPaymentSheetParameters(
        merchantDisplayName: 'BizInvestify',
        paymentIntentClientSecret: clientSecret,
        primaryButtonLabel: 'Pay',
      ),
    );

    await Stripe.instance.presentPaymentSheet();

    // 3. Poll status
    final intentId = intent['payment_intent_id'] ?? intent['id'];
    if (intentId == null) return {'status': 'unknown'};
    final statusRes = await apiService.getPaymentStatus(intentId);
    return statusRes;
  }
}

final stripeService = StripeService();
