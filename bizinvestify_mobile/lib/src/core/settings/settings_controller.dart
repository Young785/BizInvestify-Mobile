import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

class SettingsState {
  final ThemeMode themeMode;
  final Locale locale;
  const SettingsState({required this.themeMode, required this.locale});

  SettingsState copyWith({ThemeMode? themeMode, Locale? locale}) =>
      SettingsState(themeMode: themeMode ?? this.themeMode, locale: locale ?? this.locale);
}

class SettingsController extends StateNotifier<SettingsState> {
  SettingsController()
      : super(const SettingsState(themeMode: ThemeMode.light, locale: Locale('en')));

  static const _kThemeKey = 'settings_theme_mode';
  static const _kLocaleKey = 'settings_locale_code';

  Future<void> load() async {
    final prefs = await SharedPreferences.getInstance();
    final themeStr = prefs.getString(_kThemeKey);
    final localeCode = prefs.getString(_kLocaleKey);

    ThemeMode theme = ThemeMode.light;
    if (themeStr == 'dark') theme = ThemeMode.dark;
    if (themeStr == 'system') theme = ThemeMode.system;

    Locale locale = const Locale('en');
    if (localeCode != null && localeCode.isNotEmpty) {
      locale = Locale(localeCode);
    }

    state = SettingsState(themeMode: theme, locale: locale);
  }

  Future<void> setThemeMode(ThemeMode mode) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kThemeKey, mode == ThemeMode.dark ? 'dark' : mode == ThemeMode.system ? 'system' : 'light');
    state = state.copyWith(themeMode: mode);
  }

  Future<void> setLocale(Locale locale) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kLocaleKey, locale.languageCode);
    state = state.copyWith(locale: locale);
  }
}

final settingsProvider = StateNotifierProvider<SettingsController, SettingsState>((ref) {
  final controller = SettingsController();
  controller.load();
  return controller;
});
