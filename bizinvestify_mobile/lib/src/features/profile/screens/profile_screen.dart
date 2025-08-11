import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../../auth/providers/auth_provider.dart';
import '../../orders/screens/orders_screen.dart';
import '../../wallet/screens/wallet_screen.dart';
import '../../notifications/screens/notifications_screen.dart';
import '../../../core/settings/settings_controller.dart';
import 'package:image_picker/image_picker.dart';
import 'kyc_screen.dart';
import 'security_screen.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  late TextEditingController _firstName;
  late TextEditingController _lastName;
  late TextEditingController _phone;

  @override
  void initState() {
    super.initState();
    final user = ref.read(userProvider);
    _firstName = TextEditingController(text: user?.firstName ?? '');
    _lastName = TextEditingController(text: user?.lastName ?? '');
    _phone = TextEditingController(text: user?.phone ?? '');
    // Ensure fresh data
    ref.read(authProvider.notifier).refreshUser();
  }

  @override
  void dispose() {
    _firstName.dispose();
    _lastName.dispose();
    _phone.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(userProvider);
    final authState = ref.watch(authProvider);
    
    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      body: user == null
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: () async => ref.read(authProvider.notifier).refreshUser(),
              child: CustomScrollView(
                slivers: [
                  SliverAppBar(
                    pinned: true,
                    expandedHeight: 200,
                    backgroundColor: AppColors.primary600,
                    flexibleSpace: FlexibleSpaceBar(
                      titlePadding: const EdgeInsets.only(left: 16, bottom: 16),
                      title: Text(user.displayName, style: const TextStyle(color: Colors.white)),
                      background: Container(
                        decoration: const BoxDecoration(
                          gradient: LinearGradient(
                            colors: [AppColors.primary600, AppColors.accent600],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                        ),
                        child: SafeArea(
                          child: Align(
                            alignment: Alignment.bottomLeft,
                            child: Padding(
                              padding: const EdgeInsets.all(16.0),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  GestureDetector(
                                    onTap: () => _pickAndUploadAvatar(),
                                    child: Stack(
                                      children: [
                                        CircleAvatar(
                                          radius: 38,
                                          backgroundColor: Colors.white.withOpacity(0.2),
                                          backgroundImage: user.profilePicture != null ? NetworkImage(user.profilePicture!) : null,
                                          child: user.profilePicture == null
                                              ? const Icon(Icons.person, color: Colors.white, size: 34)
                                              : null,
                                        ),
                                        Positioned(
                                          right: -2,
                                          bottom: -2,
                                          child: Container(
                                            padding: const EdgeInsets.all(6),
                                            decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
                                            child: const Icon(Icons.camera_alt, color: AppColors.primary600, size: 16),
                                          ),
                                        )
                                      ],
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Column(
                                    mainAxisSize: MainAxisSize.min,
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(user.email, style: const TextStyle(color: Colors.white70)),
                                      const SizedBox(height: 4),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                        decoration: BoxDecoration(color: Colors.white.withOpacity(0.15), borderRadius: BorderRadius.circular(8)),
                                        child: Text(user.role.toUpperCase(), style: const TextStyle(color: Colors.white, fontSize: 11)),
                                      )
                                    ],
                                  )
                                ],
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                    actions: [
                      IconButton(icon: const Icon(Icons.notifications_none, color: Colors.white), onPressed: () {
                        Navigator.of(context).push(MaterialPageRoute(builder: (_) => const NotificationsScreen()));
                      })
                    ],
                  ),

                  SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        children: [
                          _buildProfileStats(user),
                          const SizedBox(height: 16),
                          _buildEditableForm(context, authState),
                          const SizedBox(height: 24),
                          _buildMenuItems(context, ref),
                          const SizedBox(height: 24),
                          _buildOutlinedLogout(context, ref, authState),
                        ],
                      ),
                    ),
                  )
                ],
              ),
            ),
    );
  }

  // Header replaced by SliverAppBar

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
        const SizedBox(width: 16.0),
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
      padding: const EdgeInsets.all(16.0),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16.0),
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
          const SizedBox(height: 8.0),
          Text(
            value,
            style: AppTypography.titleSmall.copyWith(
              fontWeight: AppTypography.bold,
              color: AppColors.textPrimary,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 4.0),
          Text(
            title,
            style: AppTypography.captionSmall.copyWith(
              color: AppColors.textTertiary,
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
          icon: Icons.person,
          title: 'Edit Profile',
          subtitle: 'Update your personal information',
          onTap: () {},
        ),
        _buildMenuItem(
          icon: Icons.verified_user,
          title: 'KYC Verification',
          subtitle: 'Manage your identity verification',
          onTap: () {
            Navigator.of(context).push(MaterialPageRoute(builder: (_) => const KycScreen()));
          },
        ),
        _buildMenuItem(
          icon: Icons.security,
          title: 'Security',
          subtitle: '2FA, passwords and devices',
          onTap: () {
            Navigator.of(context).push(MaterialPageRoute(builder: (_) => const SecurityScreen()));
          },
        ),
        _buildMenuItem(
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
          icon: Icons.help_outline,
          title: 'Help & Support',
          subtitle: 'Get assistance and FAQs',
          onTap: () {},
        ),
        _buildMenuItem(
          icon: Icons.info_outline,
          title: 'About',
          subtitle: 'Learn more about BizInvestify',
          onTap: () {},
        ),
        _buildMenuItem(
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
      margin: const EdgeInsets.only(bottom: 12.0),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16.0),
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
            color: AppColors.textPrimary,
          ),
        ),
        subtitle: Text(
          subtitle,
          style: AppTypography.bodySmall.copyWith(
            color: AppColors.textTertiary,
          ),
        ),
        trailing: const Icon(
          Icons.chevron_right,
          color: AppColors.textQuaternary,
        ),
        onTap: onTap,
      ),
    );
  }

  Widget _buildOutlinedLogout(BuildContext context, WidgetRef ref, AuthState authState) {
    return SizedBox(
      width: double.infinity,
      child: OutlinedButton(
        onPressed: authState.isLoading ? null : () => _showLogoutDialog(context, ref),
        style: OutlinedButton.styleFrom(
          padding: const EdgeInsets.symmetric(vertical: 14),
          side: const BorderSide(color: Colors.red),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          foregroundColor: Colors.red,
        ),
        child: authState.isLoading
            ? const SizedBox(
                height: 18,
                width: 18,
                child: CircularProgressIndicator(strokeWidth: 2, valueColor: AlwaysStoppedAnimation<Color>(Colors.red)),
              )
            : const Text('Log Out'),
      ),
    );
  }

  Widget _buildEditableForm(BuildContext context, AuthState authState) {
    ref.watch(userProvider)!;
    return Form(
      key: _formKey,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Align(
            alignment: Alignment.centerLeft,
            child: Text('Personal Information', style: AppTypography.titleSmall.copyWith(fontWeight: AppTypography.bold)),
          ),
          const SizedBox(height: 12),
          Row(children: [
            Expanded(child: _field('First name', _firstName)),
            const SizedBox(width: 12),
            Expanded(child: _field('Last name', _lastName)),
          ]),
          const SizedBox(height: 12),
          _field('Phone', _phone, keyboardType: TextInputType.phone),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: authState.isLoading
                  ? null
                  : () async {
                      if (!_formKey.currentState!.validate()) return;
                      final ok = await ref.read(authProvider.notifier).updateProfile({
                        'first_name': _firstName.text.trim(),
                        'last_name': _lastName.text.trim(),
                        'phone': _phone.text.trim(),
                      });
                      if (!mounted) return;
                      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ok ? 'Profile updated' : 'Update failed')));
                    },
              child: authState.isLoading
                  ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : const Text('Save Changes'),
            ),
          )
        ],
      ),
    );
  }

  Widget _field(String label, TextEditingController controller, {TextInputType? keyboardType}) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      decoration: InputDecoration(labelText: label),
      validator: (v) => (v == null || v.trim().isEmpty) ? 'Required' : null,
    );
  }

  Future<void> _pickAndUploadAvatar() async {
    final picker = ImagePicker();
    final image = await picker.pickImage(source: ImageSource.gallery, maxWidth: 1200, imageQuality: 85);
    if (image == null) return;
    await ref.read(authProvider.notifier).uploadProfilePhoto(image.path);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Profile photo updated')));
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

  // Deprecated helper removed: role color now handled in header badge styling

  Color _getKycStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'verified':
        return AppColors.secondary500;
      case 'pending':
        return AppColors.warning500;
      case 'rejected':
        return AppColors.error500;
      default:
        return AppColors.textTertiary;
    }
  }

  String _formatDate(DateTime date) {
    return '${date.month}/${date.year}';
  }
}
