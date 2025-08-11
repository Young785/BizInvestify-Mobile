import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_dimensions.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/services/api_service.dart';

class SecurityScreen extends ConsumerStatefulWidget {
  const SecurityScreen({super.key});

  @override
  ConsumerState<SecurityScreen> createState() => _SecurityScreenState();
}

class _SecurityScreenState extends ConsumerState<SecurityScreen> {
  final _current = TextEditingController();
  final _new = TextEditingController();
  final _confirm = TextEditingController();
  bool _loading = false;

  @override
  void dispose() {
    _current.dispose();
    _new.dispose();
    _confirm.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Security')),
      body: ListView(
        padding: const EdgeInsets.all(AppDimensions.spacing16),
        children: [
          Text('Password', style: AppTypography.titleSmall.copyWith(fontWeight: AppTypography.bold)),
          const SizedBox(height: 12),
          TextField(controller: _current, obscureText: true, decoration: const InputDecoration(labelText: 'Current Password')),
          const SizedBox(height: 12),
          TextField(controller: _new, obscureText: true, decoration: const InputDecoration(labelText: 'New Password')),
          const SizedBox(height: 12),
          TextField(controller: _confirm, obscureText: true, decoration: const InputDecoration(labelText: 'Confirm New Password')),
          const SizedBox(height: 12),
          ElevatedButton(
            onPressed: _loading
                ? null
                : () async {
                    setState(() => _loading = true);
                    try {
                      await apiService.changePassword(_current.text, _new.text, _confirm.text);
                      if (!mounted) return;
                      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Password updated')));
                    } catch (e) {
                      if (!mounted) return;
                      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
                    } finally {
                      if (mounted) setState(() => _loading = false);
                    }
                  },
            child: _loading
                ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                : const Text('Update Password'),
          ),
          const SizedBox(height: 24),
          Text('Two-Factor Authentication', style: AppTypography.titleSmall.copyWith(fontWeight: AppTypography.bold)),
          const SizedBox(height: 12),
          Wrap(spacing: 8, runSpacing: 8, children: [
            OutlinedButton(onPressed: _setup2FA, child: const Text('Setup 2FA')),
            OutlinedButton(onPressed: _disable2FA, child: const Text('Disable 2FA')),
          ])
        ],
      ),
    );
  }

  Future<void> _setup2FA() async {
    try {
      await apiService.setup2FA();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('2FA setup initiated')));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  Future<void> _disable2FA() async {
    try {
      await apiService.disable2FA('');
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('2FA disabled')));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }
}


