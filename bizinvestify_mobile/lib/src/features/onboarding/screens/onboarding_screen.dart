import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../../core/constants/app_typography.dart';

import '../../../shared/widgets/buttons/primary_button.dart';
import '../../auth/screens/login_screen.dart';

class OnboardingScreen extends ConsumerStatefulWidget {
  const OnboardingScreen({super.key});

  @override
  ConsumerState<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends ConsumerState<OnboardingScreen> {
  final PageController _pageController = PageController();
  int _currentPage = 0;

  final List<OnboardingPage> _pages = [
    OnboardingPage(
      title: 'Welcome to BizInvestify',
      subtitle: 'The revolutionary platform that connects businesses with investors',
      description: 'Whether you\'re looking to sell your business, seek investment, or find your next opportunity, we\'ve got you covered.',
      icon: Icons.rocket_launch,
      color: AppColors.primary500,
    ),
    OnboardingPage(
      title: 'Sell Products & Businesses',
      subtitle: 'List your products or entire business for sale',
      description: 'Create professional listings with detailed information, photos, and pricing. Reach thousands of potential buyers.',
      icon: Icons.store,
      color: AppColors.secondary500,
    ),
    OnboardingPage(
      title: 'Secure Investment',
      subtitle: 'Find the right investors for your business',
      description: 'Connect with verified investors who are interested in your industry. Secure funding with transparent terms.',
      icon: Icons.trending_up,
      color: AppColors.primary600,
    ),
    OnboardingPage(
      title: 'Real-time Communication',
      subtitle: 'Chat directly with buyers and investors',
      description: 'Built-in messaging system lets you communicate securely with potential partners. Close deals faster.',
      icon: Icons.chat_bubble,
      color: AppColors.secondary600,
    ),
  ];

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  void _nextPage() {
    if (_currentPage < _pages.length - 1) {
      _pageController.nextPage(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
      );
    } else {
      _navigateToLogin();
    }
  }

  void _skipOnboarding() {
    _navigateToLogin();
  }

  void _navigateToLogin() {
    Navigator.of(context).pushReplacement(
      PageRouteBuilder(
        pageBuilder: (context, animation, secondaryAnimation) =>
            const LoginScreen(),
        transitionsBuilder: (context, animation, secondaryAnimation, child) {
          return FadeTransition(opacity: animation, child: child);
        },
        transitionDuration: const Duration(milliseconds: 500),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.backgroundSecondary,
      body: SafeArea(
        child: Column(
          children: [
            // Skip Button
            Align(
              alignment: Alignment.topRight,
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: TextButton(
                  onPressed: _skipOnboarding,
                  child: Text(
                    'Skip',
                    style: AppTypography.bodyMedium.copyWith(
                      color: AppColors.textTertiary,
                      fontWeight: AppTypography.medium,
                    ),
                  ),
                ),
              ),
            ),
            
            // Page View
            Expanded(
              child: PageView.builder(
                controller: _pageController,
                onPageChanged: (index) {
                  setState(() {
                    _currentPage = index;
                  });
                },
                itemCount: _pages.length,
                itemBuilder: (context, index) {
                  return _buildPage(_pages[index]);
                },
              ),
            ),
            
            // Bottom Section
            Container(
              padding: const EdgeInsets.all(24.0),
              child: Column(
                children: [
                  // Page Indicators
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: List.generate(
                      _pages.length,
                      (index) => AnimatedContainer(
                        duration: const Duration(milliseconds: 300),
                        margin: const EdgeInsets.symmetric(horizontal: 4),
                        width: _currentPage == index ? 24 : 8,
                        height: 8,
                        decoration: BoxDecoration(
                          color: _currentPage == index
                              ? AppColors.primary500
                              : AppColors.textQuaternary,
                          borderRadius: BorderRadius.circular(4),
                        ),
                      ),
                    ),
                  ),
                  
                  const SizedBox(height: 32.0),
                  
                  // Action Buttons
                  Row(
                    children: [
                      if (_currentPage > 0)
                        Expanded(
                          child: OutlinedButton(
                            onPressed: () {
                              _pageController.previousPage(
                                duration: const Duration(milliseconds: 300),
                                curve: Curves.easeInOut,
                              );
                            },
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              side: const BorderSide(color: AppColors.primary500),
                            ),
                            child: Text(
                              'Previous',
                              style: AppTypography.buttonMedium.copyWith(
                                color: AppColors.primary500,
                              ),
                            ),
                          ),
                        ),
                      if (_currentPage > 0)
                        const SizedBox(width: 16.0),
                      Expanded(
                        flex: 2,
                        child: PrimaryButton(
                          onPressed: _nextPage,
                          child: Text(
                            _currentPage == _pages.length - 1 ? 'Get Started' : 'Next',
                            style: AppTypography.buttonMedium,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPage(OnboardingPage page) {
    return Padding(
      padding: const EdgeInsets.all(24.0),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          // Icon
          Container(
            width: 120,
            height: 120,
            decoration: BoxDecoration(
              color: page.color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(60),
            ),
            child: Icon(
              page.icon,
              size: 60,
              color: page.color,
            ),
          ),
          
          const SizedBox(height: 32.0),
          
          // Title
          Text(
            page.title,
            style: AppTypography.headlineMedium.copyWith(
              fontWeight: AppTypography.bold,
              color: AppColors.textPrimary,
            ),
            textAlign: TextAlign.center,
          ),
          
          const SizedBox(height: 16.0),
          
          // Subtitle
          Text(
            page.subtitle,
            style: AppTypography.titleMedium.copyWith(
              color: page.color,
              fontWeight: AppTypography.semibold,
            ),
            textAlign: TextAlign.center,
          ),
          
          const SizedBox(height: 24.0),
          
          // Description
          Text(
            page.description,
            style: AppTypography.bodyLarge.copyWith(
              color: AppColors.textTertiary,
              height: 1.6,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

class OnboardingPage {
  final String title;
  final String subtitle;
  final String description;
  final IconData icon;
  final Color color;

  OnboardingPage({
    required this.title,
    required this.subtitle,
    required this.description,
    required this.icon,
    required this.color,
  });
}
