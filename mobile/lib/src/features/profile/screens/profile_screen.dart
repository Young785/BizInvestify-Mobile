import 'package:flutter/material.dart';
import '../../../core/constants/colors.dart';
// Removed unused import: dimensions
import '../../../core/constants/typography.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundLight,
      appBar: AppBar(
        title: Text('Profile', style: AppTypography.headlineSmall),
        backgroundColor: Colors.transparent,
      ),
      body: const Center(
        child: Text('Profile Screen'),
      ),
    );
  }
}
