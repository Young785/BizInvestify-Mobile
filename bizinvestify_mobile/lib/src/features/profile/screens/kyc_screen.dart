import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/constants/app_dimensions.dart';
import '../../../core/services/api_service.dart';

class KycScreen extends ConsumerStatefulWidget {
  const KycScreen({super.key});

  @override
  ConsumerState<KycScreen> createState() => _KycScreenState();
}

class _KycScreenState extends ConsumerState<KycScreen> {
  Map<String, dynamic>? _kyc;
  bool _loading = true;
  String? _error;
  final _idController = TextEditingController();

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
      final data = await apiService.getMyKyc();
      setState(() {
        _kyc = data;
        _loading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('KYC Verification')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.all(AppDimensions.spacing16),
                children: [
                  if (_error != null)
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(color: Colors.red[50], borderRadius: BorderRadius.circular(12)),
                      child: Text(_error!, style: const TextStyle(color: Colors.red)),
                    ),
                  _statusCard(),
                  const SizedBox(height: 16),
                  Text('Submit Details', style: AppTypography.titleSmall.copyWith(fontWeight: AppTypography.bold)),
                  const SizedBox(height: 8),
                  TextFormField(
                    controller: _idController,
                    decoration: const InputDecoration(labelText: 'Government ID Number'),
                  ),
                  const SizedBox(height: 12),
                  Row(children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: _upload,
                        child: const Text('Upload & Save'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: _submit,
                        child: const Text('Submit For Review'),
                      ),
                    ),
                  ]),
                ],
              ),
            ),
    );
  }

  Widget _statusCard() {
    final status = (_kyc?['status'] ?? 'pending').toString();
    final subtitle = (_kyc?['updated_at'] ?? _kyc?['created_at'] ?? '').toString();
    Color color;
    switch (status) {
      case 'approved':
        color = AppColors.success;
        break;
      case 'rejected':
        color = AppColors.error;
        break;
      default:
        color = AppColors.warning;
    }
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [
        BoxShadow(color: AppColors.shadowSoft, blurRadius: 12, offset: const Offset(0, 6)),
      ]),
      child: Row(
        children: [
          Container(width: 12, height: 12, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
          const SizedBox(width: 12),
          Expanded(
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('Status: ${status.toUpperCase()}', style: AppTypography.titleSmall.copyWith(fontWeight: AppTypography.bold)),
              if (subtitle.isNotEmpty)
                Text(subtitle, style: AppTypography.captionSmall.copyWith(color: AppColors.textTertiary)),
            ]),
          )
        ],
      ),
    );
  }

  Future<void> _upload() async {
    try {
      await apiService.uploadKyc({'id_number': _idController.text.trim()});
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('KYC details saved.')));
      await _load();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  Future<void> _submit() async {
    try {
      await apiService.submitKyc();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('KYC submitted for review.')));
      await _load();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }
}


