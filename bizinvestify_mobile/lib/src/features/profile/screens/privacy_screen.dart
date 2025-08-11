import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_dimensions.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/services/api_service.dart';

class PrivacyScreen extends ConsumerWidget {
  const PrivacyScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Scaffold(
      appBar: AppBar(title: const Text('Privacy & Data')),
      body: ListView(
        padding: const EdgeInsets.all(AppDimensions.spacing16),
        children: [
          Text('Data Export', style: AppTypography.titleSmall.copyWith(fontWeight: AppTypography.bold)),
          const SizedBox(height: 12),
          ElevatedButton(
            onPressed: () async {
              // Export transactions as an example; extend as needed
              try {
                final bytes = await apiService.exportTransactions({});
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Exported ${bytes.length} bytes')));
                }
              } catch (e) {
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
                }
              }
            },
            child: const Text('Export My Data'),
          ),
          const SizedBox(height: 24),
          Text('Delete Account', style: AppTypography.titleSmall.copyWith(fontWeight: AppTypography.bold)),
          const SizedBox(height: 8),
          Text('Contact support to permanently remove your account and data.'),
        ],
      ),
    );
  }
}


