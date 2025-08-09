import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/marketplace_models.dart';
import '../providers/marketplace_provider.dart';
import '../../../core/utils/formatters.dart';
import '../../../core/utils/toast.dart';
import '../../../core/utils/feedback.dart';

class InvestmentDialog extends ConsumerStatefulWidget {
  final Business business;

  const InvestmentDialog({
    super.key,
    required this.business,
  });

  @override
  ConsumerState<InvestmentDialog> createState() => _InvestmentDialogState();
}

class _InvestmentDialogState extends ConsumerState<InvestmentDialog> {
  final _formKey = GlobalKey<FormState>();
  final _amountController = TextEditingController();
  final _messageController = TextEditingController();
  
  double _investmentAmount = 0;
  double _equityPercentage = 0;
  bool _isProcessing = false;

  @override
  void initState() {
    super.initState();
    _amountController.addListener(_calculateEquity);
  }

  @override
  void dispose() {
    _amountController.dispose();
    _messageController.dispose();
    super.dispose();
  }

  void _calculateEquity() {
    final amount = double.tryParse(_amountController.text.replaceAll(',', '')) ?? 0;
    setState(() {
      _investmentAmount = amount;
      _equityPercentage = widget.business.valuation > 0 
          ? (amount / widget.business.valuation) * 100 
          : 0;
    });
  }

  Future<void> _submitInvestment() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isProcessing = true;
    });

    try {
      final success = await ref.read(marketplaceProvider.notifier).createInvestment(
        businessId: widget.business.id,
        amount: _investmentAmount,
        message: _messageController.text.trim().isEmpty 
            ? null 
            : _messageController.text.trim(),
      );

      if (success && mounted) {
        Navigator.of(context).pop();
        AppToast.success('Investment submitted successfully!');
        Haptics.success();
      } else if (mounted) {
        final error = ref.read(marketplaceErrorProvider);
        AppToast.error(error ?? 'Failed to submit investment');
        Haptics.error();
      }
    } finally {
      if (mounted) {
        setState(() {
          _isProcessing = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Form(
            key: _formKey,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header
                Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Invest in ${widget.business.name}',
                            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            widget.business.industry,
                            style: TextStyle(
                              color: Colors.grey[600],
                              fontSize: 14,
                            ),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      onPressed: () => Navigator.of(context).pop(),
                      icon: const Icon(Icons.close),
                    ),
                  ],
                ),

                const SizedBox(height: 24),

                // Business valuation info
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.blue[50],
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.account_balance_wallet, color: Colors.blue[600]),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Business Valuation',
                              style: TextStyle(
                                color: Colors.blue[600],
                                fontSize: 12,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            Text(
                              Formatters.formatCurrency(widget.business.valuation),
                              style: TextStyle(
                                color: Colors.blue[800],
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            'Available Equity',
                            style: TextStyle(
                              color: Colors.blue[600],
                              fontSize: 12,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          Text(
                            '${widget.business.equityOffered.toStringAsFixed(1)}%',
                            style: TextStyle(
                              color: Colors.blue[800],
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 24),

                // Investment amount input
                Text(
                  'Investment Amount',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _amountController,
                  keyboardType: TextInputType.number,
                  inputFormatters: [
                    FilteringTextInputFormatter.digitsOnly,
                    _CurrencyInputFormatter(),
                  ],
                  decoration: InputDecoration(
                    hintText: '0',
                    prefixText: '\$',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    suffixText: 'USD',
                  ),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Please enter an investment amount';
                    }
                    final amount = double.tryParse(value.replaceAll(',', ''));
                    if (amount == null || amount <= 0) {
                      return 'Please enter a valid amount';
                    }
                    if (amount < 100) {
                      return 'Minimum investment is \$100';
                    }
                    return null;
                  },
                ),

                const SizedBox(height: 16),

                // Equity calculation
                if (_investmentAmount > 0) ...[
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.green[50],
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.green[200]!),
                    ),
                    child: Column(
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Your Investment',
                              style: TextStyle(
                                color: Colors.green[700],
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            Text(
                              Formatters.formatCurrency(_investmentAmount),
                              style: TextStyle(
                                color: Colors.green[800],
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Equity Percentage',
                              style: TextStyle(
                                color: Colors.green[700],
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            Text(
                              '${_equityPercentage.toStringAsFixed(2)}%',
                              style: TextStyle(
                                color: Colors.green[800],
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                              ),
                            ),
                          ],
                        ),
                        if (_equityPercentage > widget.business.equityOffered) ...[
                          const SizedBox(height: 8),
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: Colors.orange[100],
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              children: [
                                Icon(Icons.warning, color: Colors.orange[700], size: 16),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    'This exceeds available equity (${widget.business.equityOffered.toStringAsFixed(1)}%)',
                                    style: TextStyle(
                                      color: Colors.orange[700],
                                      fontSize: 12,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                ],

                // Message (optional)
                Text(
                  'Message to Business Owner (Optional)',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _messageController,
                  maxLines: 3,
                  maxLength: 500,
                  decoration: InputDecoration(
                    hintText: 'Introduce yourself and explain why you want to invest...',
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),

                const SizedBox(height: 24),

                // Actions
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: _isProcessing ? null : () => Navigator.of(context).pop(),
                        child: const Text('Cancel'),
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: _isProcessing ? null : _submitInvestment,
                        child: _isProcessing
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(strokeWidth: 2),
                              )
                            : const Text('Submit Investment'),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 16),

                // Disclaimer
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.grey[100],
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    'Investment proposals are subject to business owner approval. You will be notified once your proposal is reviewed.',
                    style: TextStyle(
                      color: Colors.grey[700],
                      fontSize: 12,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _CurrencyInputFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(
    TextEditingValue oldValue,
    TextEditingValue newValue,
  ) {
    if (newValue.text.isEmpty) {
      return newValue;
    }

    final int newValueInt = int.tryParse(newValue.text.replaceAll(',', '')) ?? 0;
    final String newText = newValueInt.toString().replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (match) => '${match[1]},',
    );

    return TextEditingValue(
      text: newText,
      selection: TextSelection.collapsed(offset: newText.length),
    );
  }
}
