import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../providers/marketplace_provider.dart';
import '../models/marketplace_models.dart';
import '../../../core/utils/formatters.dart';
import '../widgets/investment_dialog.dart';

class BusinessDetailScreen extends ConsumerStatefulWidget {
  final String businessId;

  const BusinessDetailScreen({
    super.key,
    required this.businessId,
  });

  @override
  ConsumerState<BusinessDetailScreen> createState() => _BusinessDetailScreenState();
}

class _BusinessDetailScreenState extends ConsumerState<BusinessDetailScreen> {
  @override
  Widget build(BuildContext context) {
    final businessAsync = ref.watch(businessProvider(widget.businessId));
    final investmentsAsync = ref.watch(businessInvestmentsProvider(widget.businessId));

    return Scaffold(
      body: businessAsync.when(
        loading: () => const LinearProgressIndicator(),
        error: (error, stack) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 64, color: Colors.red),
              const SizedBox(height: 16),
              Text(
                'Failed to load business details',
                style: Theme.of(context).textTheme.headlineSmall,
              ),
              const SizedBox(height: 8),
              Text(error.toString()),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.invalidate(businessProvider(widget.businessId)),
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
        data: (business) {
          if (business == null) {
            return const Center(
              child: Text('Business not found'),
            );
          }

          return CustomScrollView(
            slivers: [
              // App Bar with image
              SliverAppBar(
                expandedHeight: 300,
                pinned: true,
                flexibleSpace: FlexibleSpaceBar(
                  background: business.images.isNotEmpty
                      ? CachedNetworkImage(
                          imageUrl: business.images.first,
                          fit: BoxFit.cover,
                          placeholder: (context, url) => Container(
                            color: Colors.grey[200],
                            child: const Center(child: LinearProgressIndicator()),
                          ),
                          errorWidget: (context, url, error) => Container(
                            color: Colors.grey[200],
                            child: const Icon(Icons.business, size: 64),
                          ),
                        )
                      : Container(
                          color: Colors.grey[200],
                          child: const Icon(Icons.business, size: 64),
                        ),
                ),
                actions: [
                  IconButton(
                    icon: const Icon(Icons.share),
                    onPressed: () {
                      // TODO: Implement share functionality
                    },
                  ),
                  IconButton(
                    icon: const Icon(Icons.favorite_border),
                    onPressed: () {
                      // TODO: Implement wishlist functionality
                    },
                  ),
                ],
              ),

              // Business Details
              SliverToBoxAdapter(
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
                                  business.name,
                                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 8,
                                        vertical: 4,
                                      ),
                                      decoration: BoxDecoration(
                                        color: Colors.blue[50],
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text(
                                        business.industry,
                                        style: TextStyle(
                                          color: Colors.blue[700],
                                          fontSize: 12,
                                          fontWeight: FontWeight.w500,
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    if (business.seller?.isVerified == true)
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 8,
                                          vertical: 4,
                                        ),
                                        decoration: BoxDecoration(
                                          color: Colors.green[50],
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Icon(
                                              Icons.verified,
                                              color: Colors.green[700],
                                              size: 14,
                                            ),
                                            const SizedBox(width: 4),
                                            Text(
                                              'Verified',
                                              style: TextStyle(
                                                color: Colors.green[700],
                                                fontSize: 12,
                                                fontWeight: FontWeight.w500,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(
                                Formatters.formatCurrency(business.valuation),
                                style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                                  fontWeight: FontWeight.bold,
                                  color: Colors.green[600],
                                ),
                              ),
                              Text(
                                'Valuation',
                                style: TextStyle(
                                  color: Colors.grey[600],
                                  fontSize: 12,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),

                      const SizedBox(height: 16),

                      // Key Metrics
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Key Metrics',
                                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 16),
                              Row(
                                children: [
                                  Expanded(
                                    child: _MetricCard(
                                      label: 'Funding Goal',
                                      value: Formatters.formatCurrency(business.fundingGoal),
                                      icon: Icons.trending_up,
                                    ),
                                  ),
                                  const SizedBox(width: 16),
                                  Expanded(
                                    child: _MetricCard(
                                      label: 'Equity Offered',
                                      value: '${business.equityOffered.toStringAsFixed(1)}%',
                                      icon: Icons.pie_chart,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 16),
                              Row(
                                children: [
                                  Expanded(
                                    child: _MetricCard(
                                      label: 'Revenue',
                                      value: Formatters.formatCurrency(business.revenue),
                                      icon: Icons.attach_money,
                                    ),
                                  ),
                                  const SizedBox(width: 16),
                                  Expanded(
                                    child: _MetricCard(
                                      label: 'Employees',
                                      value: business.employeesCount.toString(),
                                      icon: Icons.people,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Description
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'About This Business',
                                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 12),
                              Text(
                                business.description,
                                style: const TextStyle(fontSize: 16, height: 1.5),
                              ),
                            ],
                          ),
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Business Details
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Business Details',
                                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 16),
                              _DetailRow(
                                label: 'Founded',
                                value: business.foundedYear.toString(),
                                icon: Icons.calendar_today,
                              ),
                              _DetailRow(
                                label: 'Location',
                                value: business.location,
                                icon: Icons.location_on,
                              ),
                              _DetailRow(
                                label: 'Profit Margin',
                                value: '${business.profitMargin.toStringAsFixed(1)}%',
                                icon: Icons.trending_up,
                              ),
                              _DetailRow(
                                label: 'Views',
                                value: Formatters.formatNumber(business.viewsCount),
                                icon: Icons.visibility,
                              ),
                            ],
                          ),
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Investments Section
                      investmentsAsync.when(
                        loading: () => const Card(
                          child: Padding(
                            padding: EdgeInsets.all(16),
                            child: Center(child: CircularProgressIndicator()),
                          ),
                        ),
                        error: (error, stack) => const SizedBox.shrink(),
                        data: (investments) => investments.isNotEmpty
                            ? Card(
                                child: Padding(
                                  padding: const EdgeInsets.all(16),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Recent Investments (${investments.length})',
                                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                      const SizedBox(height: 16),
                                      ...investments.take(3).map((investment) => 
                                        _InvestmentItem(investment: investment),
                                      ),
                                      if (investments.length > 3)
                                        TextButton(
                                          onPressed: () {
                                            // TODO: Show all investments
                                          },
                                          child: Text('View all ${investments.length} investments'),
                                        ),
                                    ],
                                  ),
                                ),
                              )
                            : const SizedBox.shrink(),
                      ),

                      const SizedBox(height: 100), // Bottom padding
                    ],
                  ),
                ),
              ),
            ],
          );
        },
      ),
      floatingActionButton: businessAsync.maybeWhen(
        data: (business) => business != null
            ? FloatingActionButton.extended(
                onPressed: () => _showInvestmentDialog(context, business),
                icon: const Icon(Icons.trending_up),
                label: const Text('Invest'),
              )
            : null,
        orElse: () => null,
      ),
    );
  }

  void _showInvestmentDialog(BuildContext context, Business business) {
    showDialog(
      context: context,
      builder: (context) => InvestmentDialog(business: business),
    );
  }
}

class _MetricCard extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;

  const _MetricCard({
    required this.label,
    required this.value,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.grey[50],
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
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
      ),
    );
  }
}

class _DetailRow extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;

  const _DetailRow({
    required this.label,
    required this.value,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Icon(icon, size: 20, color: Colors.grey[600]),
          const SizedBox(width: 12),
          Text(
            label,
            style: TextStyle(
              color: Colors.grey[600],
              fontSize: 14,
            ),
          ),
          const Spacer(),
          Text(
            value,
            style: const TextStyle(
              fontWeight: FontWeight.w500,
              fontSize: 14,
            ),
          ),
        ],
      ),
    );
  }
}

class _InvestmentItem extends StatelessWidget {
  final Investment investment;

  const _InvestmentItem({required this.investment});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.grey[50],
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 16,
            backgroundColor: Colors.blue[100],
            child: Text(
              (investment.investor?.name ?? 'U').substring(0, 1).toUpperCase(),
              style: TextStyle(
                color: Colors.blue[700],
                fontWeight: FontWeight.bold,
                fontSize: 14,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  investment.investor?.name ?? 'Anonymous',
                  style: const TextStyle(
                    fontWeight: FontWeight.w500,
                    fontSize: 14,
                  ),
                ),
                Text(
                  Formatters.formatRelativeTime(investment.createdAt),
                  style: TextStyle(
                    color: Colors.grey[600],
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                Formatters.formatCurrency(investment.amount),
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 14,
                ),
              ),
              Text(
                '${investment.equityPercentage.toStringAsFixed(1)}% equity',
                style: TextStyle(
                  color: Colors.grey[600],
                  fontSize: 12,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
