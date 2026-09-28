import 'package:campus_connect/app_repository.dart';
import 'package:campus_connect/models.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('interpreta estados enviados por la API', () {
    expect(RequestStatusView.parse('en_proceso'), RequestStatus.inProgress);
    expect(RequestStatusView.parse('cerrada'), RequestStatus.resolved);
    expect(RequestStatusView.parse('desconocido'), RequestStatus.received);
  });

  test('rechaza credenciales incorrectas en modo demostración', () async {
    final repository = AppRepository();

    expect(
      () => repository.login('otro@univalle.edu', 'incorrecta'),
      throwsA(isA<AppException>()),
    );
  });

  test('registra una solicitud y la agrega al seguimiento', () async {
    final repository = AppRepository();
    await repository.login('estudiante@univalle.edu', 'Campus123');
    final before = await repository.getRequests();

    final created = await repository.createRequest(
      const CreateRequestInput(
        title: 'Puerta del aula bloqueada',
        category: 'Infraestructura',
        location: 'Bloque A · Aula 101',
        description: 'La cerradura no permite abrir la puerta desde afuera.',
        evidencePath: 'evidencia.jpg',
      ),
    );

    final after = await repository.getRequests();
    expect(created.status, RequestStatus.received);
    expect(created.tracking, isNotEmpty);
    expect(after.length, before.length + 1);
    expect(after.first.id, created.id);
  });
}
