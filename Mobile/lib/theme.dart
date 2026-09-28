import 'package:flutter/material.dart';

abstract final class AppColors {
  static const ink = Color(0xFF17203A);
  static const navy = Color(0xFF172554);
  static const teal = Color(0xFF0F766E);
  static const mint = Color(0xFFDDF6EE);
  static const sky = Color(0xFFE0F2FE);
  static const amber = Color(0xFFFFE8A3);
  static const coral = Color(0xFFFFE2DA);
  static const canvas = Color(0xFFF5F6F8);
  static const border = Color(0xFFE3E6EC);
}

ThemeData buildTheme() {
  final scheme = ColorScheme.fromSeed(
    seedColor: AppColors.teal,
    primary: AppColors.navy,
    secondary: AppColors.teal,
    surface: Colors.white,
    error: const Color(0xFFB42318),
  );

  return ThemeData(
    useMaterial3: true,
    colorScheme: scheme,
    scaffoldBackgroundColor: AppColors.canvas,
    fontFamily: 'sans-serif',
    textTheme: const TextTheme(
      displaySmall: TextStyle(
        color: AppColors.ink,
        fontSize: 34,
        height: 1.08,
        fontWeight: FontWeight.w800,
        letterSpacing: -1.2,
      ),
      headlineMedium: TextStyle(
        color: AppColors.ink,
        fontSize: 26,
        height: 1.15,
        fontWeight: FontWeight.w800,
        letterSpacing: -0.6,
      ),
      titleLarge: TextStyle(
        color: AppColors.ink,
        fontSize: 20,
        fontWeight: FontWeight.w700,
      ),
      titleMedium: TextStyle(
        color: AppColors.ink,
        fontSize: 16,
        fontWeight: FontWeight.w700,
      ),
      bodyLarge: TextStyle(color: AppColors.ink, fontSize: 16, height: 1.5),
      bodyMedium: TextStyle(
        color: Color(0xFF525C73),
        fontSize: 14,
        height: 1.45,
      ),
      labelLarge: TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: Colors.white,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: AppColors.border),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: AppColors.border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: AppColors.teal, width: 2),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: Color(0xFFB42318)),
      ),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        minimumSize: const Size.fromHeight(54),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      ),
    ),
    cardTheme: CardThemeData(
      color: Colors.white,
      elevation: 0,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(24),
        side: const BorderSide(color: AppColors.border),
      ),
    ),
    navigationBarTheme: NavigationBarThemeData(
      height: 72,
      backgroundColor: Colors.white,
      indicatorColor: AppColors.mint,
      labelTextStyle: WidgetStateProperty.resolveWith(
        (states) => TextStyle(
          fontSize: 12,
          fontWeight: states.contains(WidgetState.selected)
              ? FontWeight.w700
              : FontWeight.w500,
          color: states.contains(WidgetState.selected)
              ? AppColors.navy
              : const Color(0xFF626A7C),
        ),
      ),
    ),
  );
}
