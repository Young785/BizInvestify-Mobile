import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../../../core/constants/app_dimensions.dart';
import '../../../shared/widgets/buttons/primary_button.dart';
import '../../auth/providers/auth_provider.dart';
import '../../orders/screens/orders_screen.dart';
import '../../wallet/screens/wallet_screen.dart';
import '../../notifications/screens/notifications_screen.dart';
import '../../../core/settings/settings_controller.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(userProvider);
    final authState = ref.watch(authProvider);
    
    return Scaffold(
      backgroundColor: AppColors.background200,
      appBar: AppBar(
        title: const Text('Profile'),
        backgroundColor: Colors.white,
        elevation: 1,
        actions: [
          IconButton(
            icon: const Icon(Icons.settings),
            onPressed: () {
              // TODO: Navigate to settings
            },
          ),
        ],
      ),
      body: user == null
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(AppDimensions.spacing16),
              child: Column(
                children: [
                  // Profile Header
                  _buildProfileHeader(user),
                  
                  const SizedBox(height: AppDimensions.spacing24),
                  
                  // Profile Stats
                  _buildProfileStats(user),
                  
                  const SizedBox(height: AppDimensions.spacing24),
                  
                  // Menu Items
                  _buildMenuItems(context, ref),
                  
                  const SizedBox(height: AppDimensions.spacing32),
                  
                  // Logout Button
                  _buildLogoutButton(context, ref, authState),
                ],
              ),
            ),
    );
  }

  Widget _buildProfileHeader(User user) {
    return Container(
      padding: const EdgeInsets.all(AppDimensions.spacing24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(AppDimensions.borderRadiusLarge),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          // Avatar
          Container(
            width: 100,
            height: 100,
            decoration: BoxDecoration(
              color: AppColors.primary500.withOpacity(0.1),
              borderRadius: BorderRadius.circular(50),
            ),
            child: user.profilePicture != null
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(50),
                    child: Image.network(
                      user.profilePicture!,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) => const Icon(
                        Icons.person,
                        size: 50,
                        color: AppColors.primary500,
                      ),
                    ),
                  )
                : const Icon(
                    Icons.person,
                    size: 50,
                    color: AppColors.primary500,
                  ),
          ),
          
          const SizedBox(height: AppDimensions.spacing16),
          
          // Name
          Text(
            user.fullName,
            style: AppTypography.headlineSmall.copyWith(
              fontWeight: AppTypography.bold,
              color: AppColors.text800,
            ),
          ),
          
          const SizedBox(height: AppDimensions.spacing8),
          
          // Email
          Text(
            user.email,
            style: AppTypography.bodyMedium.copyWith(
              color: AppColors.text600,
            ),
          ),
          
          const SizedBox(height: AppDimensions.spacing16),
          
          // Role Badge
          Container(
            padding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical: 8,
            ),
            decoration: BoxDecoration(
              color: _getRoleColor(user.role).withOpacity(0.1),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(
              user.role.toUpperCase(),
              style: AppTypography.captionMedium.copyWith(
                color: _getRoleColor(user.role),
                fontWeight: AppTypography.medium,
              ),
            ),
          ),
          
          const SizedBox(height: AppDimensions.spacing16),
          
          // Verification Status
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                user.emailVerified ? Icons.verified : Icons.warning,
                size: 16,
                color: user.emailVerified ? AppColors.accent500 : AppColors.warning,
              ),
              const SizedBox(width: 4),
              Text(
                user.emailVerified ? 'Email Verified' : 'Email Not Verified',
                style: AppTypography.captionMedium.copyWith(
                  color: user.emailVerified ? AppColors.accent500 : AppColors.warning,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildProfileStats(User user) {
    return Row(
      children: [
        Expanded(
          child: _buildStatCard(
            title: 'KYC Status',
            value: user.kycStatus.toUpperCase(),
            icon: Icons.verified_user,
            color: _getKycStatusColor(user.kycStatus),
          ),
        ),
        const SizedBox(width: AppDimensions.spacing16),
        Expanded(
          child: _buildStatCard(
            title: 'Member Since',
            value: _formatDate(user.createdAt),
            icon: Icons.calendar_today,
            color: AppColors.primary500,
          ),
        ),
      ],
    );
  }

  Widget _buildStatCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(AppDimensions.spacing16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(AppDimensions.borderRadiusLarge),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          Icon(
            icon,
            color: color,
            size: 24,
          ),
          const SizedBox(height: AppDimensions.spacing8),
          Text(
            value,
            style: AppTypography.titleSmall.copyWith(
              fontWeight: AppTypography.bold,
              color: AppColors.text800,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: AppDimensions.spacing4),
          Text(
            title,
            style: AppTypography.captionSmall.copyWith(
              color: AppColors.text600,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildMenuItems(BuildContext context, WidgetRef ref) {
    return Column(
      children: [
        _buildMenuItem(
          context: context,
          icon: Icons.account_balance_wallet,
          title: 'Wallet',
          subtitle: 'View balance and transactions',
          onTap: () {
            Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const WalletScreen()),
            );
          },
        ),
        _buildMenuItem(
          context: context,
          icon: Icons.person,
          title: 'Edit Profile',
          subtitle: 'Update your personal information',
          onTap: () {},
        ),
        _buildMenuItem(
          context: context,
          icon: Icons.verified_user,
          title: 'KYC Verification',
          subtitle: 'Manage your identity verification',
          onTap: () {},
        ),
        _buildMenuItem(
          context: context,
          icon: Icons.security,
          title: 'Security',
          subtitle: '2FA, passwords and devices',
          onTap: () {},
        ),
        _buildMenuItem(
          context: context,
          icon: Icons.shopping_bag,
          title: 'My Orders',
          subtitle: 'View your transaction history',
          onTap: () {
            Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const OrdersScreen()),
            );
          },
        ),
        _buildMenuItem(
          context: context,
          icon: Icons.notifications,
          title: 'Notifications',
          subtitle: 'Manage your alerts',
          onTap: () {
            Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const NotificationsScreen()),
            );
          },
        ),
        _buildMenuItem(
          context: context,
          icon: Icons.help_outline,
          title: 'Help & Support',
          subtitle: 'Get assistance and FAQs',
          onTap: () {},
        ),
        _buildMenuItem(
          context: context,
          icon: Icons.info_outline,
          title: 'About',
          subtitle: 'Learn more about BizInvestify',
          onTap: () {},
        ),
        _buildMenuItem(
          context: context,
          icon: Icons.color_lens,
          title: 'Theme',
          subtitle: 'Light / Dark / System',
          onTap: () async {
            final mode = await showModalBottomSheet<ThemeMode>(
              context: context,
              builder: (_) => SafeArea(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    ListTile(title: const Text('Light'), onTap: () => Navigator.pop(context, ThemeMode.light)),
                    ListTile(title: const Text('Dark'), onTap: () => Navigator.pop(context, ThemeMode.dark)),
                    ListTile(title: const Text('System'), onTap: () => Navigator.pop(context, ThemeMode.system)),
                  ],
                ),
              ),
            );
            if (mode != null) {
              await ref.read(settingsProvider.notifier).setThemeMode(mode);
              if (!context.mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Theme updated')));
            }
          },
        ),
        _buildMenuItem(
          context: context,
          icon: Icons.language,
          title: 'Language',
          subtitle: 'Select app language',
          onTap: () async {
            final code = await showModalBottomSheet<String>(
              context: context,
              builder: (_) => SafeArea(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    ListTile(title: const Text('English'), onTap: () => Navigator.pop(context, 'en')),
                    ListTile(title: const Text('French'), onTap: () => Navigator.pop(context, 'fr')),
                  ],
                ),
              ),
            );
            if (code != null) {
              await ref.read(settingsProvider.notifier).setLocale(Locale(code));
              if (!context.mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Language updated')));
            }
          },
        ),
      ],
    );
  }

  Widget _buildMenuItem({
    required String title,
    required String subtitle,
    required IconData icon,
    Color color = AppColors.primary500,
    required VoidCallback onTap,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: AppDimensions.spacing12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(AppDimensions.borderRadiusLarge),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ListTile(
        leading: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: color.withOpacity(0.1),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(
            icon,
            color: color,
            size: 20,
          ),
        ),
        title: Text(
          title,
          style: AppTypography.titleSmall.copyWith(
            fontWeight: AppTypography.semibold,
            color: AppColors.text800,
          ),
        ),
        subtitle: Text(
          subtitle,
          style: AppTypography.bodySmall.copyWith(
            color: AppColors.text600,
          ),
        ),
        trailing: const Icon(
          Icons.chevron_right,
          color: AppColors.text400,
        ),
        onTap: onTap,
      ),
    );
  }

  Widget _buildLogoutButton(BuildContext context, WidgetRef ref, AuthState authState) {
    return PrimaryButton(
      onPressed: authState.isLoading ? null : () => _showLogoutDialog(context, ref),
      isFullWidth: true,
      backgroundColor: Colors.red[600],
      child: authState.isLoading
          ? const SizedBox(
              height: 20,
              width: 20,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
              ),
            )
          : const Text('Logout'),
    );
  }

  void _showLogoutDialog(BuildContext context, WidgetRef ref) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Logout'),
        content: const Text('Are you sure you want to logout?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(context).pop();
              ref.read(authProvider.notifier).logout();
              // Navigate to login screen
              Navigator.of(context).pushNamedAndRemoveUntil(
                '/login',
                (route) => false,
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red[600],
              foregroundColor: Colors.white,
            ),
            child: const Text('Logout'),
          ),
        ],
      ),
    );
  }

  Color _getRoleColor(String role) {
    switch (role.toLowerCase()) {
      case 'admin':
        return Colors.red;
      case 'seller':
        return AppColors.accent500;
      case 'buyer':
        return AppColors.primary500;
      default:
        return AppColors.text600;
    }
  }

  Color _getKycStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'verified':
        return AppColors.accent500;
      case 'pending':
        return AppColors.warning;
      case 'rejected':
        return AppColors.error;
      default:
        return AppColors.text600;
    }
  }

  String _formatDate(DateTime date) {
    return '${date.month}/${date.year}';
  }
}
