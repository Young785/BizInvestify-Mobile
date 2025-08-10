import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../providers/marketplace_provider.dart';
import '../screens/product_details_screen.dart';

class ProductCard extends StatelessWidget {
  final Product product;
  final VoidCallback? onTap;

  const ProductCard({
    super.key,
    required this.product,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap ?? () {
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => ProductDetailsScreen(productId: product.id),
          ),
        );
      },
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildImage(),
            _buildInfo(context),
          ],
        ),
      ),
    );
  }

  Widget _buildImage() {
    return Expanded(
      flex: 3,
      child: Container(
        width: double.infinity,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.only(
            topLeft: Radius.circular(16.0),
            topRight: Radius.circular(16.0),
          ),
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.only(
            topLeft: Radius.circular(16.0),
            topRight: Radius.circular(16.0),
          ),
          child: product.images.isNotEmpty
              ? CachedNetworkImage(
                  imageUrl: product.images.first,
                  fit: BoxFit.cover,
                  placeholder: (context, url) => Container(
                    color: AppColors.surfaceBorder,
                    child: const Center(
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                      ),
                    ),
                  ),
                  errorWidget: (context, url, error) => Container(
                    color: AppColors.surfaceBorder,
                    child: const Icon(
                      Icons.image_not_supported,
                      color: AppColors.textQuaternary,
                    ),
                  ),
                )
              : Container(
                  color: AppColors.surfaceBorder,
                  child: const Icon(
                    Icons.image_not_supported,
                    color: AppColors.textQuaternary,
                  ),
                ),
        ),
      ),
    );
  }

  Widget _buildInfo(BuildContext context) {
    return Expanded(
      flex: 2,
      child: Padding(
        padding: const EdgeInsets.all(12.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Product Name
            Text(
              product.name,
              style: AppTypography.titleSmall.copyWith(
                fontWeight: AppTypography.semibold,
                color: AppColors.textPrimary,
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),

            const SizedBox(height: 4),

            // Category
            Container(
              padding: const EdgeInsets.symmetric(
                horizontal: 8,
                vertical: 2,
              ),
              decoration: BoxDecoration(
                color: AppColors.primary500.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                product.category,
                style: AppTypography.captionSmall.copyWith(
                  color: AppColors.primary500,
                  fontWeight: AppTypography.medium,
                ),
              ),
            ),

            const Spacer(),

            // Price and Seller
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                // Price
                Text(
                  '\$${product.price.toStringAsFixed(2)}',
                  style: AppTypography.titleMedium.copyWith(
                    fontWeight: AppTypography.bold,
                    color: AppColors.secondary500,
                  ),
                ),

                // Seller
                Expanded(
                  child: Text(
                    product.sellerName,
                    style: AppTypography.captionSmall.copyWith(
                      color: AppColors.textTertiary,
                    ),
                    textAlign: TextAlign.end,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
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
