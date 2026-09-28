import 'package:flutter/material.dart';

import 'app_repository.dart';
import 'models.dart';
import 'screens.dart';
import 'theme.dart';

void main() {
  runApp(CampusConnectApp(repository: AppRepository()));
}

class CampusConnectApp extends StatefulWidget {
  const CampusConnectApp({super.key, required this.repository});

  final AppRepository repository;

  @override
  State<CampusConnectApp> createState() => _CampusConnectAppState();
}

class _CampusConnectAppState extends State<CampusConnectApp> {
  Student? _student;

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Campus Connect',
      debugShowCheckedModeBanner: false,
      theme: buildTheme(),
      home: _student == null
          ? LoginScreen(
              repository: widget.repository,
              onAuthenticated: (student) => setState(() => _student = student),
            )
          : HomeShell(
              repository: widget.repository,
              student: _student!,
              onLogout: () {
                widget.repository.logout();
                setState(() => _student = null);
              },
            ),
    );
  }
}
