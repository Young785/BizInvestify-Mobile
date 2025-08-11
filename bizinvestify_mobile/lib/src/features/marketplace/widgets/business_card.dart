import 'package:flutter/material.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../providers/marketplace_provider.dart';

class BusinessCard extends StatelessWidget {
  final Business business;
  final VoidCallback? onTap;

  const BusinessCard({
    super.key,
    required this.business,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16.0),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              Row(
                children: [
                  // Business Icon
                  Container(
                    width: 50,
                    height: 50,
                    decoration: BoxDecoration(
                      color: AppColors.primary500.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12.0),
                    ),
                    child: const Icon(
                      Icons.business,
                      color: AppColors.primary500,
                      size: 24,
                    ),
                  ),
                  
                  const SizedBox(width: 12.0),
                  
                  // Business Info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          business.name,
                          style: AppTypography.titleMedium.copyWith(
                            fontWeight: AppTypography.semibold,
                            color: AppColors.textPrimary,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        
                        const SizedBox(height: 4),
                        
                        Text(
                          business.industry,
                          style: AppTypography.bodySmall.copyWith(
                            color: AppColors.textTertiary,
                          ),
                        ),
                      ],
                    ),
                  ),
                  
                  // Status Badge
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: business.status == 'active'
                          ? AppColors.secondary500.withOpacity(0.1)
                          : AppColors.textQuaternary.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      business.status.toUpperCase(),
                      style: AppTypography.captionSmall.copyWith(
                        color: business.status == 'active'
                            ? AppColors.secondary500
                            : AppColors.textTertiary,
                        fontWeight: AppTypography.medium,
                      ),
                    ),
                  ),
                ],
              ),
              
              const SizedBox(height: 16.0),
              
              // Description
              Text(
                business.description,
                style: AppTypography.bodyMedium.copyWith(
                  color: AppColors.textTertiary,
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
              
              const SizedBox(height: 16.0),
              
              // Financial Info
              Row(
                children: [
                  // Valuation
                  Expanded(
                    child: _buildFinancialItem(
                      label: 'Valuation',
                      value: '\$${(business.valuation / 1000000).toStringAsFixed(1)}M',
                      icon: Icons.assessment,
                      color: AppColors.primary500,
                    ),
                  ),
                  
                  const SizedBox(width: 12.0),
                  
                  // Funding Goal
                  Expanded(
                    child: _buildFinancialItem(
                      label: 'Funding Goal',
                      value: '\$${(business.fundingGoal / 1000).toStringAsFixed(0)}K',
                      icon: Icons.trending_up,
                      color: AppColors.secondary500,
                    ),
                  ),
                  
                  const SizedBox(width: 12.0),
                  
                  // Equity Offered
                  Expanded(
                    child: _buildFinancialItem(
                      label: 'Equity',
                      value: '${business.equityOffered.toStringAsFixed(1)}%',
                      icon: Icons.pie_chart,
                      color: AppColors.primary600,
                    ),
                  ),
                ],
              ),
              
              const SizedBox(height: 16.0),
              
              // Footer
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  // Owner
                  Text(
                    'by ${business.ownerName}',
                    style: AppTypography.captionMedium.copyWith(
                      color: AppColors.textTertiary,
                    ),
                  ),
                  
                  // Invest Button
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 8,
                    ),
                    decoration: BoxDecoration(
                      color: AppColors.primary500,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      'Invest Now',
                      style: AppTypography.captionMedium.copyWith(
                        color: Colors.white,
                        fontWeight: AppTypography.medium,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildFinancialItem({
    required String label,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(
              icon,
              size: 16,
              color: color,
            ),
            const SizedBox(width: 4),
            Text(
              label,
              style: AppTypography.captionSmall.copyWith(
                color: AppColors.textTertiary,
              ),
            ),
          ],
        ),
        
        const SizedBox(height: 4),
        
        Text(
          value,
          style: AppTypography.titleSmall.copyWith(
            fontWeight: AppTypography.bold,
            color: AppColors.textPrimary,
          ),
        ),
      ],
    );
  }
}
