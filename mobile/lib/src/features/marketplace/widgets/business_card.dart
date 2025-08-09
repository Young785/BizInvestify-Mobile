import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../models/marketplace_models.dart';
import '../../../core/utils/formatters.dart';

class BusinessCard extends StatelessWidget {
  final Business business;
  final VoidCallback onTap;
  final bool featured;
  final bool trending;

  const BusinessCard({
    super.key,
    required this.business,
    required this.onTap,
    this.featured = false,
    this.trending = false,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 2,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image and badges
            Stack(
              children: [
                ClipRRect(
                  borderRadius: const BorderRadius.vertical(
                    top: Radius.circular(16),
                  ),
                  child: AspectRatio(
                    aspectRatio: 16 / 9,
                    child: business.images.isNotEmpty
                        ? CachedNetworkImage(
                            imageUrl: business.images.first,
                            fit: BoxFit.cover,
                            placeholder: (context, url) => Container(
                              color: Colors.grey[200],
                              child: const Center(
                                child: CircularProgressIndicator(),
                              ),
                            ),
                            errorWidget: (context, url, error) => Container(
                              color: Colors.grey[200],
                              child: Icon(
                                Icons.business,
                                size: 48,
                                color: Colors.grey[400],
                              ),
                            ),
                          )
                        : Container(
                            color: Colors.grey[200],
                            child: Icon(
                              Icons.business,
                              size: 48,
                              color: Colors.grey[400],
                            ),
                          ),
                  ),
                ),
                
                // Badges
                Positioned(
                  top: 12,
                  left: 12,
                  child: Row(
                    children: [
                      if (featured)
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.orange,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Text(
                            'FEATURED',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      if (trending)
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.red,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Text(
                            'TRENDING',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                    ],
                  ),
                ),

                // Verification badge
                if (business.seller?.isVerified == true)
                  Positioned(
                    top: 12,
                    right: 12,
                    child: Container(
                      padding: const EdgeInsets.all(4),
                      decoration: BoxDecoration(
                        color: Colors.green,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(
                        Icons.verified,
                        color: Colors.white,
                        size: 16,
                      ),
                    ),
                  ),
              ],
            ),

            // Content
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Business name and industry
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            business.name,
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
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
                      ],
                    ),

                    const SizedBox(height: 8),

                    // Description
                    Text(
                      business.description,
                      style: TextStyle(
                        color: Colors.grey[600],
                        fontSize: 14,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),

                    const SizedBox(height: 12),

                    // Key metrics
                    Row(
                      children: [
                        Expanded(
                          child: _MetricItem(
                            label: 'Valuation',
                            value: Formatters.formatCurrency(business.valuation),
                            icon: Icons.account_balance_wallet_outlined,
                          ),
                        ),
                        Expanded(
                          child: _MetricItem(
                            label: 'Funding Goal',
                            value: Formatters.formatCurrency(business.fundingGoal),
                            icon: Icons.trending_up,
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 12),

                    // Additional info
                    Row(
                      children: [
                        Icon(
                          Icons.location_on_outlined,
                          size: 16,
                          color: Colors.grey[600],
                        ),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            business.location,
                            style: TextStyle(
                              color: Colors.grey[600],
                              fontSize: 12,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        Text(
                          'Founded ${business.foundedYear}',
                          style: TextStyle(
                            color: Colors.grey[600],
                            fontSize: 12,
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 12),

                    // Progress bar for equity offered
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Equity Offered',
                              style: TextStyle(
                                color: Colors.grey[600],
                                fontSize: 12,
                              ),
                            ),
                            Text(
                              '${business.equityOffered.toStringAsFixed(1)}%',
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        LinearProgressIndicator(
                          value: business.equityOffered / 100,
                          backgroundColor: Colors.grey[200],
                          valueColor: AlwaysStoppedAnimation<Color>(
                            business.equityOffered > 50
                                ? Colors.red[400]!
                                : business.equityOffered > 25
                                    ? Colors.orange[400]!
                                    : Colors.green[400]!,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
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
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(
              icon,
              size: 14,
              color: Colors.grey[600],
            ),
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
        const SizedBox(height: 2),
        Text(
          value,
          style: const TextStyle(
            fontWeight: FontWeight.bold,
            fontSize: 14,
          ),
        ),
      ],
    );
  }
}
