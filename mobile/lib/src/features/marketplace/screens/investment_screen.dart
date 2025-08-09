import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../providers/marketplace_provider.dart';
import '../models/marketplace_models.dart';
import '../../../core/utils/formatters.dart';

class InvestmentScreen extends ConsumerStatefulWidget {
  const InvestmentScreen({super.key});

  @override
  ConsumerState<InvestmentScreen> createState() => _InvestmentScreenState();
}

class _InvestmentScreenState extends ConsumerState<InvestmentScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    
    // Load investments on screen load
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(marketplaceProvider.notifier).loadInvestments();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final investments = ref.watch(investmentsProvider);
    final isLoading = ref.watch(marketplaceLoadingProvider);
    final error = ref.watch(marketplaceErrorProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Investments'),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Active'),
            Tab(text: 'History'),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              ref.read(marketplaceProvider.notifier).loadInvestments();
            },
          ),
        ],
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          // Active Investments Tab
          _buildInvestmentsList(
            investments.where((inv) => 
              inv.status == 'pending' || inv.status == 'approved'
            ).toList(),
            isLoading,
            error,
            'No active investments',
            'Your active investment proposals will appear here.',
          ),
          
          // Investment History Tab
          _buildInvestmentsList(
            investments.where((inv) => 
              inv.status == 'completed' || 
              inv.status == 'rejected' || 
              inv.status == 'cancelled'
            ).toList(),
            isLoading,
            error,
            'No investment history',
            'Your completed and past investments will appear here.',
          ),
        ],
      ),
    );
  }

  Widget _buildInvestmentsList(
    List<Investment> investments,
    bool isLoading,
    String? error,
    String emptyTitle,
    String emptyMessage,
  ) {
    if (isLoading && investments.isEmpty) {
      return ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: 6,
        itemBuilder: (context, index) => Container(
          margin: const EdgeInsets.only(bottom: 16),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.grey[200],
            borderRadius: BorderRadius.circular(12),
          ),
          height: 96,
        ),
      );
    }

    if (error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 64, color: Colors.red),
            const SizedBox(height: 16),
            Text(
              'Failed to load investments',
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            const SizedBox(height: 8),
            Text(error),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () {
                ref.read(marketplaceProvider.notifier).clearError();
                ref.read(marketplaceProvider.notifier).loadInvestments();
              },
              child: const Text('Retry'),
            ),
          ],
        ),
      );
    }

    if (investments.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.trending_up,
                size: 64,
                color: Colors.grey[400],
              ),
              const SizedBox(height: 16),
              Text(
                emptyTitle,
                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                  color: Colors.grey[600],
                ),
              ),
              const SizedBox(height: 8),
              Text(
                emptyMessage,
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey[600]),
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () {
                  context.push('/marketplace');
                },
                child: const Text('Explore Opportunities'),
              ),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () async {
        await ref.read(marketplaceProvider.notifier).loadInvestments();
      },
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: investments.length,
        itemBuilder: (context, index) {
          final investment = investments[index];
          return _InvestmentCard(investment: investment);
        },
      ),
    );
  }
}

class _InvestmentCard extends StatelessWidget {
  final Investment investment;

  const _InvestmentCard({required this.investment});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
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
                        investment.business?.name ?? 'Business',
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        investment.business?.industry ?? '',
                        style: TextStyle(
                          color: Colors.grey[600],
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
                ),
                _StatusChip(status: investment.status),
              ],
            ),

            const SizedBox(height: 16),

            // Investment details
            Row(
              children: [
                Expanded(
                  child: _DetailItem(
                    label: 'Investment Amount',
                    value: Formatters.formatCurrency(investment.amount),
                    icon: Icons.attach_money,
                  ),
                ),
                Expanded(
                  child: _DetailItem(
                    label: 'Equity Percentage',
                    value: '${investment.equityPercentage.toStringAsFixed(2)}%',
                    icon: Icons.pie_chart,
                  ),
                ),
              ],
            ),

            const SizedBox(height: 16),

            // Message if available
            if (investment.message != null && investment.message!.isNotEmpty) ...[
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.grey[50],
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Your Message',
                      style: TextStyle(
                        color: Colors.grey[700],
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      investment.message!,
                      style: const TextStyle(fontSize: 14),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
            ],

            // Footer
            Row(
              children: [
                Icon(
                  Icons.calendar_today,
                  size: 16,
                  color: Colors.grey[600],
                ),
                const SizedBox(width: 4),
                Text(
                  'Submitted ${Formatters.formatRelativeTime(investment.createdAt)}',
                  style: TextStyle(
                    color: Colors.grey[600],
                    fontSize: 12,
                  ),
                ),
                const Spacer(),
                TextButton(
                  onPressed: () {
                    if (investment.business != null) {
                      context.push('/business/${investment.business!.id}');
                    }
                  },
                  child: const Text('View Business'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _StatusChip extends StatelessWidget {
  final String status;

  const _StatusChip({required this.status});

  @override
  Widget build(BuildContext context) {
    Color backgroundColor;
    Color textColor;
    IconData icon;

    switch (status.toLowerCase()) {
      case 'pending':
        backgroundColor = Colors.orange[100]!;
        textColor = Colors.orange[800]!;
        icon = Icons.schedule;
        break;
      case 'approved':
        backgroundColor = Colors.blue[100]!;
        textColor = Colors.blue[800]!;
        icon = Icons.check_circle;
        break;
      case 'completed':
        backgroundColor = Colors.green[100]!;
        textColor = Colors.green[800]!;
        icon = Icons.check_circle;
        break;
      case 'rejected':
        backgroundColor = Colors.red[100]!;
        textColor = Colors.red[800]!;
        icon = Icons.cancel;
        break;
      case 'cancelled':
        backgroundColor = Colors.grey[200]!;
        textColor = Colors.grey[800]!;
        icon = Icons.cancel;
        break;
      default:
        backgroundColor = Colors.grey[200]!;
        textColor = Colors.grey[800]!;
        icon = Icons.help;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: textColor),
          const SizedBox(width: 4),
          Text(
            Formatters.formatInvestmentStatus(status),
            style: TextStyle(
              color: textColor,
              fontSize: 12,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }
}

class _DetailItem extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;

  const _DetailItem({
    required this.label,
    required this.value,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(icon, size: 16, color: Colors.grey[600]),
            const SizedBox(width: 4),
            Text(
              label,
              style: TextStyle(
                color: Colors.grey[600],
                fontSize: 12,
              ),
            ),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: const TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 16,
          ),
        ),
      ],
    );
  }
}
