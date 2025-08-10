import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_dimensions.dart';
import '../../../core/services/api_service.dart';
import 'order_details_screen.dart';

class OrdersScreen extends ConsumerStatefulWidget {
  const OrdersScreen({super.key});

  @override
  ConsumerState<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends ConsumerState<OrdersScreen> {
  bool _loading = false;
  String? _error;
  List<Map<String, dynamic>> _transactions = [];

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
      final txs = await apiService.getPaymentsTransactionHistory();
      setState(() => _transactions = txs);
    } catch (e) {
      setState(() => _error = e.toString());
    } finally {
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background200,
      appBar: AppBar(
        title: const Text('My Orders'),
        backgroundColor: Colors.white,
        elevation: 1,
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? ListView(
                    children: [
                      Padding(
                        padding: const EdgeInsets.all(16),
                        child: Text(_error!, style: const TextStyle(color: Colors.red)),
                      )
                    ],
                  )
                : _transactions.isEmpty
                    ? ListView(
                        children: [
                          const SizedBox(height: 80),
                          Icon(Icons.receipt_long, size: 64, color: Colors.grey[400]),
                          const SizedBox(height: 12),
                          Center(
                            child: Text(
                              'No orders yet',
                              style: TextStyle(color: Colors.grey[600]),
                            ),
                          ),
                        ],
                      )
                    : ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemBuilder: (context, index) {
                          final tx = _transactions[index];
                          return _buildTransactionTile(tx);
                        },
                        separatorBuilder: (_, __) => const SizedBox(height: 12),
                        itemCount: _transactions.length,
                      ),
      ),
    );
  }

  Widget _buildTransactionTile(Map<String, dynamic> tx) {
    final String id = (tx['id'] ?? '').toString();
    final String type = (tx['type'] ?? 'payment').toString();
    final String status = (tx['status'] ?? 'pending').toString();
    final double amount = double.tryParse((tx['amount'] ?? '0').toString()) ?? 0.0;
    final String currency = (tx['currency'] ?? 'USD').toString();
    final String ref = (tx['reference'] ?? '').toString();
    final String createdAt = (tx['created_at'] ?? '').toString();

    Color statusColor;
    switch (status) {
      case 'completed':
      case 'success':
        statusColor = Colors.green;
        break;
      case 'failed':
      case 'cancelled':
        statusColor = Colors.red;
        break;
      default:
        statusColor = Colors.orange;
    }

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey[200]!),
      ),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: AppColors.primary500.withOpacity(0.1),
          child: Icon(Icons.payment, color: AppColors.primary500),
        ),
        title: Row(
          children: [
            Text('${currency.toUpperCase()} ${amount.toStringAsFixed(2)}',
                style: const TextStyle(fontWeight: FontWeight.w700)),
            const Spacer(),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: statusColor.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(status.toUpperCase(), style: TextStyle(color: statusColor, fontSize: 11)),
            ),
          ],
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text('Type: $type'),
            if (ref.isNotEmpty) Text('Ref: $ref'),
            if (createdAt.isNotEmpty)
              Text(
                createdAt,
                style: TextStyle(color: Colors.grey[600], fontSize: 12),
              ),
          ],
        ),
        onTap: id.isEmpty
            ? null
            : () {
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (_) => OrderDetailsScreen(transactionId: id),
                  ),
                );
              },
      ),
    );
  }
}
