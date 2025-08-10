import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/services/api_service.dart';
import '../../payments/payment_controller.dart';

enum PaymentMethodType { card, paystack, bankTransfer }
enum PaymentContext { product, walletTopUp }

class PaymentSheet extends ConsumerStatefulWidget {
  final int? productId;
  final double amount;
  final String currency;
  final PaymentContext contextType;
  final Map<String, dynamic>? metadata;

  const PaymentSheet({
    super.key,
    this.productId,
    required this.amount,
    this.currency = 'USD',
    this.contextType = PaymentContext.product,
    this.metadata,
  });

  @override
  ConsumerState<PaymentSheet> createState() => _PaymentSheetState();
}

class _PaymentSheetState extends ConsumerState<PaymentSheet> {
  PaymentMethodType _selected = PaymentMethodType.card;
  bool _loading = false;
  String? _error;

  bool get _isWalletTopUp => widget.contextType == PaymentContext.walletTopUp;

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
          if (_isWalletTopUp) {
            result = await apiService.createPaymentIntent({
              'amount': widget.amount,
              'currency': widget.currency,
              'metadata': {
                'type': 'wallet_topup',
                ...?widget.metadata,
              }
            });
          } else {
            result = await apiService.createProductPaymentIntent(
              productId: widget.productId!,
              amount: widget.amount,
              currency: widget.currency,
            );
          }
          intentId = result['payment_intent_id'] ?? result['id'];
          if (intentId != null) {
            await apiService.confirmPayment(paymentIntentId: intentId);
          }
          break;
        case PaymentMethodType.paystack:
          if (_isWalletTopUp) {
            // Fallback to card/transfer for wallet since generic paystack endpoint isn't available
            throw Exception('Paystack not available for wallet top-up. Please use Card or Bank Transfer.');
          }
          result = await apiService.createPaystackProductPayment(
            productId: widget.productId!,
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
              if (!_isWalletTopUp && widget.productId != null) 'product_id': widget.productId,
              if (_isWalletTopUp) 'type': 'wallet_topup',
              ...?widget.metadata,
            },
          );
          intentId = result['payment_intent_id'] ?? result['id'] ?? result['reference'];
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
    final methods = <PaymentMethodType>[
      PaymentMethodType.card,
      if (!_isWalletTopUp) PaymentMethodType.paystack,
      PaymentMethodType.bankTransfer,
    ];

    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Text(
                  _isWalletTopUp ? 'Top Up Wallet' : 'Complete Payment',
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
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
            for (final m in methods) _buildMethodTile(
              title: m == PaymentMethodType.card
                  ? 'Card (Stripe)'
                  : m == PaymentMethodType.paystack
                      ? 'Paystack'
                      : 'Bank Transfer',
              subtitle: m == PaymentMethodType.card
                  ? 'Pay securely with your card'
                  : m == PaymentMethodType.paystack
                      ? 'Local payment for NGN'
                      : 'Pay with manual bank transfer',
              value: m,
              icon: m == PaymentMethodType.card
                  ? Icons.credit_card
                  : m == PaymentMethodType.paystack
                      ? Icons.account_balance_wallet
                      : Icons.account_balance,
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
                    : Text(_isWalletTopUp ? 'Top Up Now' : 'Pay Now'),
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
