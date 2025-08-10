import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/services/api_service.dart';
import '../../payments/payment_controller.dart';
import 'package:url_launcher/url_launcher.dart';

enum PaymentMethodType { card, paystack, bankTransfer }

class PaymentSheet extends ConsumerStatefulWidget {
  final int productId;
  final double amount;
  final String currency;

  const PaymentSheet({
    super.key,
    required this.productId,
    required this.amount,
    this.currency = 'USD',
  });

  @override
  ConsumerState<PaymentSheet> createState() => _PaymentSheetState();
}

class _PaymentSheetState extends ConsumerState<PaymentSheet> {
  PaymentMethodType _selected = PaymentMethodType.card;
  bool _loading = false;
  String? _error;

  Future<void> _pay() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      Map<String, dynamic> result;
      String? intentId;
      switch (_selected) {
        case PaymentMethodType.card:
          result = await apiService.createProductPaymentIntent(
            productId: widget.productId,
            amount: widget.amount,
            currency: widget.currency,
          );
          intentId = result['payment_intent_id'] ?? result['id'];
          if (intentId != null) {
            await apiService.confirmPayment(paymentIntentId: intentId);
          }
          break;
        case PaymentMethodType.paystack:
          result = await apiService.createPaystackProductPayment(
            productId: widget.productId,
            amount: widget.amount,
            currency: widget.currency == 'USD' ? 'NGN' : widget.currency,
          );
          final authUrl = result['authorization_url'] ?? result['auth_url'];
          if (authUrl != null) {
            final uri = Uri.parse(authUrl.toString());
            if (await canLaunchUrl(uri)) {
              await launchUrl(uri, mode: LaunchMode.externalApplication);
            }
          }
          intentId = result['payment_intent_id'] ?? result['id'] ?? result['reference'];
          break;
        case PaymentMethodType.bankTransfer:
          result = await apiService.processBankTransfer(
            amount: widget.amount,
            currency: widget.currency,
            metadata: {
              'product_id': widget.productId,
              'note': 'Manual bank transfer for product purchase',
            },
          );
          intentId = result['payment_intent_id'] ?? result['id'];
          break;
      }

      Map<String, dynamic>? poll;
      if (intentId != null) {
        poll = await paymentController.pollPaymentStatus(intentId);
      }

      if (!mounted) return;
      Navigator.of(context).pop({'result': result, 'poll': poll});
    } catch (e) {
      setState(() {
        _error = e.toString();
      });
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Text(
                  'Complete Payment',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const Spacer(),
                IconButton(
                  icon: const Icon(Icons.close),
                  onPressed: _loading ? null : () => Navigator.of(context).pop(),
                )
              ],
            ),
            const SizedBox(height: 8),
            Text(
              'Amount: ${widget.currency} ${widget.amount.toStringAsFixed(2)}',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 16),
            const Text('Choose payment method', style: TextStyle(fontWeight: FontWeight.w600)),
            const SizedBox(height: 8),
            _buildMethodTile(
              title: 'Card (Stripe)',
              subtitle: 'Pay securely with your card',
              value: PaymentMethodType.card,
              icon: Icons.credit_card,
            ),
            _buildMethodTile(
              title: 'Paystack',
              subtitle: 'Local payment for NGN',
              value: PaymentMethodType.paystack,
              icon: Icons.account_balance_wallet,
            ),
            _buildMethodTile(
              title: 'Bank Transfer',
              subtitle: 'Pay with manual bank transfer',
              value: PaymentMethodType.bankTransfer,
              icon: Icons.account_balance,
            ),
            if (_error != null) ...[
              const SizedBox(height: 12),
              Text(_error!, style: const TextStyle(color: Colors.red)),
            ],
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _loading ? null : _pay,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary500,
                  foregroundColor: Colors.white,
                ),
                child: _loading
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Text('Pay Now'),
              ),
            ),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }

  Widget _buildMethodTile({
    required String title,
    required String subtitle,
    required PaymentMethodType value,
    required IconData icon,
  }) {
    return RadioListTile<PaymentMethodType>(
      value: value,
      groupValue: _selected,
      onChanged: _loading ? null : (v) => setState(() => _selected = v!),
      title: Row(
        children: [
          Icon(icon, color: AppColors.primary500),
          const SizedBox(width: 8),
          Text(title, style: const TextStyle(fontWeight: FontWeight.w600)),
        ],
      ),
      subtitle: Text(subtitle),
    );
  }
}
