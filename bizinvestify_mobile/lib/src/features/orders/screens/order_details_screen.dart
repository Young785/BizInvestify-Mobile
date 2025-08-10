import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/services/api_service.dart';

class OrderDetailsScreen extends ConsumerStatefulWidget {
  final String transactionId;
  const OrderDetailsScreen({super.key, required this.transactionId});

  @override
  ConsumerState<OrderDetailsScreen> createState() => _OrderDetailsScreenState();
}

class _OrderDetailsScreenState extends ConsumerState<OrderDetailsScreen> {
  bool _loading = false;
  String? _error;
  Map<String, dynamic>? _tx;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final res = await apiService.getTransaction(widget.transactionId);
      setState(() => _tx = res);
    } catch (e) {
      setState(() => _error = e.toString());
    } finally {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final tx = _tx;
    return Scaffold(
      appBar: AppBar(
        title: const Text('Order Details'),
        backgroundColor: Colors.white,
        elevation: 1,
      ),
      backgroundColor: AppColors.backgroundSecondary,
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(child: Padding(padding: const EdgeInsets.all(16), child: Text(_error!, style: const TextStyle(color: Colors.red))))
              : tx == null
                  ? const Center(child: Text('Order not found'))
                  : ListView(
                      padding: const EdgeInsets.all(16),
                      children: [
                        _buildHeader(tx),
                        const SizedBox(height: 16),
                        _buildSection('Payment Info', [
                          _row('Amount', _money(tx['amount'], tx['currency'])),
                          _row('Status', _status(tx['status'])),
                          _row('Type', tx['type'] ?? 'N/A'),
                          _row('Reference', tx['reference'] ?? 'N/A'),
                          _row('Created At', tx['created_at'] ?? 'N/A'),
                        ]),
                        const SizedBox(height: 16),
                        if (tx['metadata'] != null)
                          _buildSection('Metadata', (tx['metadata'] as Map).entries
                              .map<Widget>((e) => _row(e.key.toString(), e.value.toString()))
                              .toList()),
                      ],
                    ),
    );
  }

  Widget _buildHeader(Map<String, dynamic> tx) {
    final amount = _money(tx['amount'], tx['currency']);
    final statusText = _status(tx['status']);
    Color statusColor;
    switch ((tx['status'] ?? '').toString().toLowerCase()) {
      case 'completed':
      case 'success':
        statusColor = Colors.green;
        break;
      case 'failed':
      case 'cancelled':
      case 'canceled':
        statusColor = Colors.red;
        break;
      default:
        statusColor = Colors.orange;
    }

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[200]!),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(amount, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800)),
          const SizedBox(height: 8),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(statusText.toUpperCase(), style: TextStyle(color: statusColor, fontSize: 11)),
              ),
              const SizedBox(width: 8),
              Text('#${(tx['id'] ?? '').toString()}', style: TextStyle(color: Colors.grey[700])),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSection(String title, List<Widget> children) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[200]!),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(fontWeight: FontWeight.w700)),
          const SizedBox(height: 12),
          ...children,
        ],
      ),
    );
  }

  Widget _row(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        children: [
          Expanded(child: Text(label, style: TextStyle(color: Colors.grey[700]))),
          Text(value, style: const TextStyle(fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }

  String _money(dynamic amountRaw, dynamic currencyRaw) {
    final amount = double.tryParse((amountRaw ?? '0').toString()) ?? 0.0;
    final currency = (currencyRaw ?? 'USD').toString();
    return '${currency.toUpperCase()} ${amount.toStringAsFixed(2)}';
    }

  String _status(dynamic statusRaw) {
    return (statusRaw ?? 'pending').toString();
  }
}
