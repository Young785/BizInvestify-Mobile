import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/constants/app_dimensions.dart';
import '../../../core/services/api_service.dart';

class DevicesScreen extends ConsumerStatefulWidget {
  const DevicesScreen({super.key});

  @override
  ConsumerState<DevicesScreen> createState() => _DevicesScreenState();
}

class _DevicesScreenState extends ConsumerState<DevicesScreen> {
  List<Map<String, dynamic>> _sessions = const [];
  bool _loading = true;
  String? _error;

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
      // Reuse '/me' for device history if backend returns it; otherwise leave placeholder
      final me = await apiService.getCurrentUser();
      final sessions = (me['sessions'] as List?)?.cast<Map<String, dynamic>>() ?? <Map<String, dynamic>>[];
      setState(() {
        _sessions = sessions;
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
      appBar: AppBar(title: const Text('Devices & Sessions')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView.separated(
                padding: const EdgeInsets.all(AppDimensions.spacing16),
                itemBuilder: (_, i) {
                  if (_error != null && i == 0) {
                    return Text(_error!, style: const TextStyle(color: Colors.red));
                  }
                  final s = _sessions[i];
                  return ListTile(
                    leading: const Icon(Icons.devices_other),
                    title: Text(s['device']?.toString() ?? 'Unknown device', style: AppTypography.bodyMedium),
                    subtitle: Text(s['ip']?.toString() ?? ''),
                    trailing: Text(s['last_seen']?.toString() ?? ''),
                  );
                },
                separatorBuilder: (_, __) => const SizedBox(height: 8),
                itemCount: _sessions.isEmpty ? 0 : _sessions.length,
              ),
            ),
    );
  }
}


