import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/services/api_service.dart';

class AddBusinessScreen extends ConsumerStatefulWidget {
  const AddBusinessScreen({super.key});

  @override
  ConsumerState<AddBusinessScreen> createState() => _AddBusinessScreenState();
}

class _AddBusinessScreenState extends ConsumerState<AddBusinessScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _industryController = TextEditingController();
  final _valuationController = TextEditingController();
  final _fundingGoalController = TextEditingController();
  final _equityController = TextEditingController();
  bool _submitting = false;

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _submitting = true);

    try {
      final payload = {
        'name': _nameController.text.trim(),
        'description': _descriptionController.text.trim(),
        'industry': _industryController.text.trim(),
        'valuation': double.parse(_valuationController.text.trim()),
        'funding_goal': double.parse(_fundingGoalController.text.trim()),
        'equity_offered': double.parse(_equityController.text.trim()),
      };

      final res = await apiService.createBusiness(payload);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Business created')));
      Navigator.of(context).pop(res);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Add Business'),
        backgroundColor: Colors.white,
        elevation: 1,
      ),
      backgroundColor: AppColors.backgroundSecondary,
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _textField(
                controller: _nameController,
                labelText: 'Business Name',
                validator: (v) => v == null || v.trim().isEmpty ? 'Name is required' : null,
              ),
              const SizedBox(height: 12.0),
              _textField(
                controller: _descriptionController,
                labelText: 'Description',
                maxLines: 4,
              ),
              const SizedBox(height: 12.0),
              _textField(
                controller: _industryController,
                labelText: 'Industry',
                validator: (v) => v == null || v.trim().isEmpty ? 'Industry is required' : null,
              ),
              const SizedBox(height: 12.0),
              _textField(
                controller: _valuationController,
                labelText: 'Valuation',
                keyboardType: TextInputType.number,
                validator: _numberValidator,
              ),
              const SizedBox(height: 12.0),
              _textField(
                controller: _fundingGoalController,
                labelText: 'Funding Goal',
                keyboardType: TextInputType.number,
                validator: _numberValidator,
              ),
              const SizedBox(height: 12.0),
              _textField(
                controller: _equityController,
                labelText: 'Equity Offered (%)',
                keyboardType: TextInputType.number,
                validator: _numberValidator,
              ),
              const SizedBox(height: 24.0),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _submitting ? null : _submit,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.secondary500,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                  ),
                  child: _submitting
                      ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Text('Create Business'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String? _numberValidator(String? v) {
    final t = v?.trim();
    if (t == null || t.isEmpty) return 'Required';
    if (double.tryParse(t) == null) return 'Enter a valid number';
    return null;
  }

  Widget _textField({
    required TextEditingController controller,
    required String label,
    String? Function(String?)? validator,
    int maxLines = 1,
    TextInputType? keyboardType,
  }) {
    return TextFormField(
      controller: controller,
      maxLines: maxLines,
      keyboardType: keyboardType,
      decoration: InputDecoration(
        labelText: label,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        filled: true,
        fillColor: Colors.white,
      ),
      validator: validator,
    );
  }
}
