import 'package:flutter/material.dart';
import '../models/marketplace_models.dart';
import '../../../core/utils/formatters.dart';

class MarketplaceHeader extends StatelessWidget {
  final MarketplaceStats? stats;

  const MarketplaceHeader({
    super.key,
    this.stats,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            Colors.blue[600]!,
            Colors.blue[800]!,
          ],
        ),
      ),
      child: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Marketplace',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 28,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Discover investment opportunities',
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.9),
                          fontSize: 16,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.all(12),
                   decoration: BoxDecoration(
                     color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(
                    Icons.business_center,
                    color: Colors.white,
                    size: 28,
                  ),
                ),
              ],
            ),
            
            if (stats != null) ...[
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: _StatItem(
                      label: 'Businesses',
                      value: Formatters.formatNumber(stats!.totalBusinesses),
                      icon: Icons.business,
                    ),
                  ),
                  Expanded(
                    child: _StatItem(
                      label: 'Investments',
                      value: Formatters.formatNumber(stats!.totalInvestments),
                      icon: Icons.trending_up,
                    ),
                  ),
                  Expanded(
                    child: _StatItem(
                      label: 'Volume',
                      value: Formatters.formatCurrency(stats!.totalInvestmentVolume),
                      icon: Icons.account_balance_wallet,
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _StatItem extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;

  const _StatItem({
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
           color: Colors.white.withValues(alpha: 0.9),
          size: 20,
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
        Text(
          label,
           style: TextStyle(
             color: Colors.white.withValues(alpha: 0.8),
            fontSize: 12,
          ),
        ),
      ],
    );
  }
}
