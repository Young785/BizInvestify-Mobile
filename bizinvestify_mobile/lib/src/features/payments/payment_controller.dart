import 'dart:async';
import '../../../core/services/api_service.dart';

class PaymentController {
  final ApiService _api = apiService;

  Future<Map<String, dynamic>> pollPaymentStatus(
    String paymentIntentId, {
    Duration interval = const Duration(seconds: 2),
    int maxAttempts = 30,
  }) async {
    int attempts = 0;
    while (attempts < maxAttempts) {
      attempts += 1;
      final res = await _api.getPaymentStatus(paymentIntentId);
      final status = (res['status'] ?? '').toString().toLowerCase();

      if (status == 'succeeded' || status == 'success' || status == 'completed') {
        return {
          'status': 'success',
          'data': res,
        };
      }

      if (status == 'failed' || status == 'cancelled' || status == 'canceled') {
        return {
          'status': 'failed',
          'data': res,
        };
      }

      // pending/processing -> wait
      await Future.delayed(interval);
    }

    return {
      'status': 'timeout',
      'data': null,
    };
  }
}

final paymentController = PaymentController();
