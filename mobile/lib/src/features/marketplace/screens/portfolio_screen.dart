import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../providers/marketplace_provider.dart';
import '../models/marketplace_models.dart';
import '../../../core/utils/formatters.dart';

class PortfolioScreen extends ConsumerStatefulWidget {
  const PortfolioScreen({super.key});

  @override
  ConsumerState<PortfolioScreen> createState() => _PortfolioScreenState();
}

class _PortfolioScreenState extends ConsumerState<PortfolioScreen> {
  @override
  void initState() {
    super.initState();
    
    // Load portfolio data on screen load
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final notifier = ref.read(marketplaceProvider.notifier);
      notifier.loadPortfolio();
      notifier.loadInvestments();
    });
  }

  @override
  Widget build(BuildContext context) {
    final portfolio = ref.watch(portfolioProvider);
    final investments = ref.watch(investmentsProvider);
    final isLoading = ref.watch(marketplaceLoadingProvider);
    final error = ref.watch(marketplaceErrorProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Portfolio'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              final notifier = ref.read(marketplaceProvider.notifier);
              notifier.loadPortfolio();
              notifier.loadInvestments();
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          final notifier = ref.read(marketplaceProvider.notifier);
          await Future.wait([
            notifier.loadPortfolio(),
            notifier.loadInvestments(),
          ]);
        },
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Portfolio Overview
              if (portfolio != null) ...[
                _PortfolioOverviewCard(portfolio: portfolio),
                const SizedBox(height: 24),
              ],

              // Error handling
              if (error != null) ...[
                Container(
                  padding: const EdgeInsets.all(16),
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(
                    color: Colors.red[50],
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.red[200]!),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.error_outline, color: Colors.red[600]),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          error,
                          style: TextStyle(color: Colors.red[800]),
                        ),
                      ),
                      TextButton(
                        onPressed: () {
                          ref.read(marketplaceProvider.notifier).clearError();
                          final notifier = ref.read(marketplaceProvider.notifier);
                          notifier.loadPortfolio();
                          notifier.loadInvestments();
                        },
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                ),
              ],

              // Investments Section
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Your Investments',
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  TextButton(
                    onPressed: () {
                      context.push('/investments');
                    },
                    child: const Text('View All'),
                  ),
                ],
              ),

              const SizedBox(height: 16),

              // Loading state
              if (isLoading && investments.isEmpty)
                const LinearProgressIndicator(),

              // Empty state
              if (!isLoading && investments.isEmpty)
                _EmptyInvestmentsCard(),

              // Investment cards
              if (investments.isNotEmpty) ...[
                ...investments.take(5).map((investment) => 
                  Padding(
                    padding: const EdgeInsets.only(bottom: 16),
                    child: _InvestmentSummaryCard(investment: investment),
                  ),
                ),
                if (investments.length > 5)
                  Center(
                    child: TextButton(
                      onPressed: () {
                        context.push('/investments');
                      },
                      child: Text('View ${investments.length - 5} more investments'),
                    ),
                  ),
              ],

              const SizedBox(height: 24),

              // Performance Metrics (if available)
              if (portfolio != null && portfolio.investments.isNotEmpty) ...[
                Text(
                  'Performance Metrics',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 16),
                _PerformanceMetricsCard(portfolio: portfolio),
              ],
            ],
          ),
        ),
      ),
    );
  }
}

class _PortfolioOverviewCard extends StatelessWidget {
  final Portfolio portfolio;

  const _PortfolioOverviewCard({required this.portfolio});

  @override
  Widget build(BuildContext context) {
    final roi = portfolio.totalInvested > 0 
        ? ((portfolio.totalReturns - portfolio.totalInvested) / portfolio.totalInvested) * 100
        : 0.0;

    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Colors.blue[600]!,
              Colors.blue[800]!,
            ],
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Portfolio Value',
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.9),
                          fontSize: 16,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        Formatters.formatCurrency(portfolio.portfolioValue),
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 32,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: roi >= 0 
                        ? Colors.green.withValues(alpha: 0.2)
                        : Colors.red.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        roi >= 0 ? Icons.trending_up : Icons.trending_down,
                        color: roi >= 0 ? Colors.green[200] : Colors.red[200],
                        size: 16,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        Formatters.formatROI(roi),
                        style: TextStyle(
                          color: roi >= 0 ? Colors.green[200] : Colors.red[200],
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),

            const SizedBox(height: 24),

            Row(
              children: [
                Expanded(
                  child: _OverviewMetric(
                    label: 'Total Invested',
                    value: Formatters.formatCurrency(portfolio.totalInvested),
                    icon: Icons.trending_up,
                  ),
                ),
                Expanded(
                  child: _OverviewMetric(
                    label: 'Active Investments',
                    value: portfolio.activeInvestments.toString(),
                    icon: Icons.business_center,
                  ),
                ),
                Expanded(
                  child: _OverviewMetric(
                    label: 'Total Returns',
                    value: Formatters.formatCurrency(portfolio.totalReturns),
                    icon: Icons.account_balance_wallet,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _OverviewMetric extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;

  const _OverviewMetric({
    required this.label,
    required this.value,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(
          icon,
          color: Colors.white.withValues(alpha: 0.8),
          size: 20,
        ),
        const SizedBox(height: 8),
        Text(
          value,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: TextStyle(
            color: Colors.white.withValues(alpha: 0.8),
            fontSize: 12,
          ),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }
}

class _InvestmentSummaryCard extends StatelessWidget {
  final Investment investment;

  const _InvestmentSummaryCard({required this.investment});

  @override
  Widget build(BuildContext context) {
    return Card(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: Colors.blue[100],
          child: Icon(
            Icons.business,
            color: Colors.blue[700],
          ),
        ),
        title: Text(
          investment.business?.name ?? 'Business',
          style: const TextStyle(fontWeight: FontWeight.w600),
        ),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 4),
            Text(investment.business?.industry ?? ''),
            const SizedBox(height: 4),
            Row(
              children: [
                Text(
                  Formatters.formatCurrency(investment.amount),
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    color: Colors.green,
                  ),
                ),
                Text(' • ${investment.equityPercentage.toStringAsFixed(1)}% equity'),
              ],
            ),
          ],
        ),
        trailing: Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
          decoration: BoxDecoration(
            color: _getStatusColor(investment.status).withValues(alpha: 0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Text(
            Formatters.formatInvestmentStatus(investment.status),
            style: TextStyle(
              color: _getStatusColor(investment.status),
              fontSize: 12,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
        onTap: () {
          if (investment.business != null) {
            context.push('/business/${investment.business!.id}');
          }
        },
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'completed':
        return Colors.green;
      case 'approved':
        return Colors.blue;
      case 'pending':
        return Colors.orange;
      case 'rejected':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }
}

class _EmptyInvestmentsCard extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Card(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          children: [
            Icon(
              Icons.trending_up,
              size: 64,
              color: Colors.grey[400],
            ),
            const SizedBox(height: 16),
            Text(
              'No investments yet',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Start building your investment portfolio by exploring business opportunities.',
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
}

class _PerformanceMetricsCard extends StatelessWidget {
  final Portfolio portfolio;

  const _PerformanceMetricsCard({required this.portfolio});

  @override
  Widget build(BuildContext context) {
    final avgInvestment = portfolio.activeInvestments > 0 
        ? portfolio.totalInvested / portfolio.activeInvestments 
        : 0.0;

    return Card(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Row(
              children: [
                Expanded(
                  child: _MetricItem(
                    label: 'Average Investment',
                    value: Formatters.formatCurrency(avgInvestment),
                    icon: Icons.bar_chart,
                  ),
                ),
                Expanded(
                  child: _MetricItem(
                    label: 'Best Performing',
                    value: 'TechCorp', // TODO: Calculate from actual data
                    icon: Icons.star,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: _MetricItem(
                    label: 'Diversification',
                    value: '${portfolio.investments.map((i) => i.business?.industry).toSet().length} industries',
                    icon: Icons.donut_small,
                  ),
                ),
                Expanded(
                  child: _MetricItem(
                    label: 'Risk Level',
                    value: 'Moderate', // TODO: Calculate risk score
                    icon: Icons.shield,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _MetricItem extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;

  const _MetricItem({
    required this.label,
    required this.value,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(icon, color: Colors.grey[600], size: 24),
        const SizedBox(height: 8),
        Text(
          value,
          style: const TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 16,
          ),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: TextStyle(
            color: Colors.grey[600],
            fontSize: 12,
          ),
          textAlign: TextAlign.center,
        ),
      ],
    );
  }
}
