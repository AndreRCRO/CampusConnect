import 'package:campus_connect/app_repository.dart';
import 'package:campus_connect/main.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  Future<void> openApp(WidgetTester tester) async {
    tester.view.physicalSize = const Size(390, 844);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
    await tester.pumpWidget(CampusConnectApp(repository: AppRepository()));
  }

  Future<void> login(WidgetTester tester) async {
    await tester.enterText(
      find.widgetWithText(TextFormField, 'Correo institucional'),
      'estudiante@univalle.edu',
    );
    await tester.enterText(
      find.widgetWithText(TextFormField, 'Contraseña'),
      'Campus123',
    );
    await tester.tap(find.widgetWithText(FilledButton, 'Ingresar'));
    await tester.pumpAndSettle();
  }

  testWidgets('valida los campos obligatorios del inicio de sesión', (
    tester,
  ) async {
    await openApp(tester);

    await tester.tap(find.widgetWithText(FilledButton, 'Ingresar'));
    await tester.pump();

    expect(find.text('Ingresa tu correo institucional.'), findsOneWidget);
    expect(find.text('Ingresa tu contraseña.'), findsOneWidget);
  });

  testWidgets('permite iniciar sesión y muestra la pantalla principal', (
    tester,
  ) async {
    await openApp(tester);
    await login(tester);

    expect(find.text('Hola, Ana'), findsOneWidget);
    expect(find.text('Crear una solicitud'), findsOneWidget);
    expect(find.text('En seguimiento'), findsOneWidget);
  });

  testWidgets('abre el detalle con seguimiento y comentarios', (tester) async {
    await openApp(tester);
    await login(tester);

    await tester.drag(find.byType(ListView).first, const Offset(0, -350));
    await tester.pumpAndSettle();
    final requestTitle = find.text('Proyector sin señal en aula 204');
    await tester.tap(requestTitle);
    await tester.pumpAndSettle();

    expect(find.text('SOL-1042'), findsOneWidget);
    await tester.drag(find.byType(ListView).last, const Offset(0, -650));
    await tester.pumpAndSettle();
    expect(find.text('Seguimiento'), findsOneWidget);
    await tester.drag(find.byType(ListView).last, const Offset(0, -700));
    await tester.pumpAndSettle();
    expect(find.text('Comentarios'), findsOneWidget);
    expect(find.textContaining('Revisaremos el adaptador'), findsOneWidget);
  });

  testWidgets('valida el formulario de nueva solicitud', (tester) async {
    await openApp(tester);
    await login(tester);

    await tester.tap(find.text('Nueva'));
    await tester.pumpAndSettle();
    final submit = find.byKey(const Key('create-request-submit'));
    await tester.ensureVisible(submit);
    await tester.tap(submit);
    await tester.pump();

    expect(find.text('Escribe un título.'), findsOneWidget);
    expect(find.text('Selecciona una categoría.'), findsOneWidget);
    expect(find.text('Indica dónde ocurre.'), findsOneWidget);
    expect(find.text('Describe lo ocurrido.'), findsOneWidget);
  });
}
