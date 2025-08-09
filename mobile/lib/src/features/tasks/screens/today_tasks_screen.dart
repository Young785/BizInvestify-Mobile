import 'package:flutter/material.dart';
import '../../../core/constants/colors.dart';
// Removed unused import: dimensions
import '../../../core/constants/typography.dart';

class TodayTasksScreen extends StatelessWidget {
  const TodayTasksScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundLight,
      appBar: AppBar(
        title: Text('Today\'s Tasks', style: AppTypography.headlineSmall),
        backgroundColor: Colors.transparent,
      ),
      body: const Center(
        child: Text('Today\'s Tasks Screen'),
      ),
    );
  }
}
