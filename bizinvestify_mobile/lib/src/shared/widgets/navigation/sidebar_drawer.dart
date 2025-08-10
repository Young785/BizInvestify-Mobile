import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../features/auth/providers/auth_provider.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';
import '../../../features/marketplace/screens/marketplace_screen.dart';
import '../../../features/messaging/screens/messages_screen.dart';
import '../../../features/orders/screens/orders_screen.dart';
import '../../../features/notifications/screens/notifications_screen.dart';
import '../../../features/profile/screens/profile_screen.dart';

class SidebarDrawer extends ConsumerWidget {
  const SidebarDrawer({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(userProvider);
    final hasPermission = ref.read(authProvider.notifier).hasPermission;

    List<_NavEntry> items = [
      _NavItem(title: 'Dashboard', icon: Icons.dashboard_outlined, onTap: () => Navigator.pop(context)),
      _NavItem(title: 'Messages', icon: Icons.message_outlined, onTap: () => _push(context, const MessagesScreen())),
    ];

    if (user?.role == 'seller' || hasPermission('products.view')) {
      items.addAll([
        _NavHeader('Seller'),
        _NavItem(title: 'Products', icon: Icons.inventory_2_outlined, onTap: () => _push(context, const MarketplaceScreen(initialTab: 0))),
        _NavItem(title: 'Businesses', icon: Icons.apartment_outlined, onTap: () => _push(context, const MarketplaceScreen(initialTab: 1))),
        _NavItem(title: 'Orders', icon: Icons.shopping_bag_outlined, onTap: () => _push(context, const OrdersScreen())),
      ]);
    }

    if (user?.role == 'buyer') {
      items.addAll([
        _NavHeader('Buyer'),
        _NavItem(title: 'Marketplace', icon: Icons.store_outlined, onTap: () => _push(context, const MarketplaceScreen())),
      ]);
    }

    items.addAll([
      _NavHeader('General'),
      _NavItem(title: 'Notifications', icon: Icons.notifications_outlined, onTap: () => _push(context, const NotificationsScreen())),
      _NavItem(title: 'Profile', icon: Icons.person_outline, onTap: () => _push(context, const ProfileScreen())),
    ]);

    if (hasPermission('admin.access')) {
      items.addAll([
        _NavHeader('Admin'),
        _NavItem(title: 'Users', icon: Icons.group_outlined),
        _NavItem(title: 'Roles & Permissions', icon: Icons.verified_user_outlined),
        _NavItem(title: 'KYC Review', icon: Icons.badge_outlined),
        _NavItem(title: 'Admin Transactions', icon: Icons.credit_card_outlined),
        _NavItem(title: 'Reports', icon: Icons.description_outlined),
        _NavItem(title: 'Settings', icon: Icons.settings_outlined),
      ]);
    }

    return Drawer(
      child: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildHeader(user),
            const Divider(height: 1),
            Expanded(
              child: ListView.builder(
                itemCount: items.length,
                itemBuilder: (context, index) {
                  final item = items[index];
                  if (item is _NavHeader) {
                    return Padding(
                      padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                      child: Text(
                        item.title,
                        style: AppTypography.captionMedium.copyWith(color: AppColors.textTertiary),
                      ),
                    );
                  }
                  final nav = item as _NavItem;
                  return ListTile(
                    leading: Icon(nav.icon, color: AppColors.textSecondary),
                    title: Text(nav.title, style: AppTypography.bodyMedium.copyWith(color: AppColors.textPrimary)),
                    onTap: () {
                      Navigator.pop(context);
                      nav.onTap?.call();
                    },
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader(User? user) {
    return ListTile(
      contentPadding: const EdgeInsets.all(16),
      leading: CircleAvatar(
        radius: 24,
        backgroundColor: AppColors.primary500,
        child: Text(
          (user?.firstName.isNotEmpty == true ? user!.firstName[0] : 'U').toUpperCase(),
          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
        ),
      ),
      title: Text(user?.fullName ?? 'Guest', style: AppTypography.titleSmall.copyWith(fontWeight: AppTypography.semibold)),
      subtitle: Text((user?.role ?? 'guest').toUpperCase(), style: AppTypography.captionSmall.copyWith(color: AppColors.textTertiary)),
    );
  }

  void _push(BuildContext context, Widget page) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => page));
  }
}

abstract class _NavEntry {}

class _NavHeader extends _NavEntry {
  final String title;
  _NavHeader(this.title);
}

class _NavItem extends _NavEntry {
  final String title;
  final IconData icon;
  final VoidCallback? onTap;
  _NavItem({required this.title, required this.icon, this.onTap});
}
