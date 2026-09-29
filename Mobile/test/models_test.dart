import 'package:campus_connect/app_repository.dart';
import 'package:campus_connect/models.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('interpreta estados enviados por la API', () {
    expect(RequestStatusView.parse('en_proceso'), RequestStatus.inProgress);
    expect(RequestStatusView.parse('cerrada'), RequestStatus.resolved);
    expect(RequestStatusView.parse('desconocido'), RequestStatus.received);
  });

  test('interpreta el contrato real de Laravel', () {
    final request = CampusRequest.fromJson({
      'id': 42,
      'tracking_code': 'REQ-20260929-ABC123',
      'title': 'Proyector sin señal',
      'description': 'No detecta HDMI',
      'category': 'soporte_tecnologico',
      'location': 'Bloque B - Aula 204',
      'priority': 'alta',
      'status': 'en_proceso',
      'created_at': '2026-09-29T10:00:00.000000Z',
      'updated_at': '2026-09-29T11:00:00.000000Z',
      'assigned_staff': {'name': 'Carlos M.'},
      'comments': [
        {
          'comment': 'Estamos revisando el cableado.',
          'created_at': '2026-09-29T11:00:00.000000Z',
          'author': {'name': 'Carlos M.'},
        },
      ],
      'status_histories': [
        {
          'new_status': 'en_proceso',
          'notes': 'Asignado a soporte.',
          'created_at': '2026-09-29T11:00:00.000000Z',
        },
      ],
    });

    expect(request.id, 'REQ-20260929-ABC123');
    expect(request.apiId, '42');
    expect(request.category, 'Soporte tecnológico');
    expect(request.assignee, 'Carlos M.');
    expect(request.comments.single.author, 'Carlos M.');
    expect(request.tracking.single.title, 'Estado: En proceso');
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
