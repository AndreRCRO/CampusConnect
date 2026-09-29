import 'dart:async';
import 'dart:convert';

import 'package:http/http.dart' as http;

import 'models.dart';

class AppException implements Exception {
  const AppException(this.message);
  final String message;

  @override
  String toString() => message;
}

class AppRepository {
  AppRepository({http.Client? client}) : _client = client ?? http.Client();

  static const _baseUrl = String.fromEnvironment('API_BASE_URL');
  final http.Client _client;
  String? _token;
  final List<CampusRequest> _demoRequests = _seedRequests();

  bool get isDemo => _baseUrl.isEmpty;

  Future<Student> login(String email, String password) async {
    if (isDemo) {
      await Future<void>.delayed(const Duration(milliseconds: 550));
      if (email.toLowerCase() != 'estudiante@univalle.edu' ||
          password != 'Campus123') {
        throw const AppException(
          'Credenciales incorrectas. Usa el acceso de demostración.',
        );
      }
      _token = 'demo-token';
      return const Student(
        name: 'Ana Rodriguez',
        email: 'estudiante@univalle.edu',
      );
    }

    final response = await _send(
      () => _client.post(
        _uri('/api/v1/auth/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'email': email,
          'password': password,
          'device_name': 'campus_connect_mobile',
        }),
      ),
    );
    final data = _json(response);
    _token = data['token']?.toString() ?? data['access_token']?.toString();
    if (_token == null) {
      throw const AppException('La API no devolvió un token de acceso.');
    }
    return Student.fromJson(
      (data['user'] ?? data['estudiante']) as Map<String, dynamic>? ?? data,
    );
  }

  Future<List<CampusRequest>> getRequests() async {
    if (isDemo) {
      await Future<void>.delayed(const Duration(milliseconds: 350));
      return List.unmodifiable(_demoRequests);
    }
    final response = await _send(
      () => _client.get(_uri('/api/v1/student/requests'), headers: _headers()),
    );
    final decoded = jsonDecode(response.body);
    final list = decoded is List
        ? decoded
        : (decoded['data'] as List<dynamic>? ?? const []);
    return list
        .whereType<Map<String, dynamic>>()
        .map(CampusRequest.fromJson)
        .toList();
  }

  Future<CampusRequest> getRequest(String id) async {
    if (isDemo) {
      await Future<void>.delayed(const Duration(milliseconds: 250));
      return _demoRequests.firstWhere((request) => request.id == id);
    }
    final response = await _send(
      () => _client.get(
        _uri('/api/v1/student/requests/$id'),
        headers: _headers(),
      ),
    );
    final data = _json(response);
    return CampusRequest.fromJson(
      (data['request'] as Map<String, dynamic>?) ??
          (data['data'] as Map<String, dynamic>?) ??
          data,
    );
  }

  Future<CampusRequest> createRequest(CreateRequestInput input) async {
    if (isDemo) {
      await Future<void>.delayed(const Duration(milliseconds: 700));
      final now = DateTime.now();
      final request = CampusRequest(
        id: 'SOL-${1043 + _demoRequests.length}',
        title: input.title,
        category: input.category,
        location: input.location,
        description: input.description,
        status: RequestStatus.received,
        priority: 'Media',
        createdAt: now,
        updatedAt: now,
        assignee: 'Por asignar',
        evidencePath: input.evidencePath,
        tracking: [
          TrackingEvent(
            title: 'Solicitud recibida',
            description: 'Tu reporte fue registrado correctamente.',
            date: now,
          ),
        ],
      );
      _demoRequests.insert(0, request);
      return request;
    }

    final response = await _send(
      () => _client.post(
        _uri('/api/v1/student/requests'),
        headers: {..._headers(), 'Content-Type': 'application/json'},
        body: jsonEncode({
          'title': input.title,
          'category': _categoryCode(input.category),
          'location': input.location,
          'description': input.description,
          'priority': 'media',
        }),
      ),
    );
    final data = _json(response);
    final created = CampusRequest.fromJson(
      (data['request'] as Map<String, dynamic>?) ?? data,
    );

    if (input.evidencePath != null) {
      final upload = http.MultipartRequest(
        'POST',
        _uri('/api/v1/student/requests/${created.apiId}/media'),
      )..headers.addAll(_headers());
      upload.files.add(
        await http.MultipartFile.fromPath('file', input.evidencePath!),
      );
      final streamed = await _send(upload.send);
      final uploadResponse = await http.Response.fromStream(streamed);
      if (uploadResponse.statusCode < 200 || uploadResponse.statusCode >= 300) {
        _throwForStatus(uploadResponse.statusCode, uploadResponse.body);
      }
    }
    return getRequest(created.apiId);
  }

  void logout() => _token = null;

  Uri _uri(String path) =>
      Uri.parse('${_baseUrl.replaceFirst(RegExp(r'/$'), '')}$path');

  Map<String, String> _headers() => {
    'Accept': 'application/json',
    if (_token != null) 'Authorization': 'Bearer $_token',
  };

  Future<T> _send<T>(Future<T> Function() operation) async {
    try {
      final result = await operation().timeout(const Duration(seconds: 12));
      if (result is http.Response &&
          (result.statusCode < 200 || result.statusCode >= 300)) {
        _throwForStatus(result.statusCode, result.body);
      }
      return result;
    } on TimeoutException {
      throw const AppException(
        'La solicitud tardó demasiado. Revisa tu conexión e intenta otra vez.',
      );
    } on AppException {
      rethrow;
    } catch (_) {
      throw const AppException(
        'No se pudo conectar con el servicio. Revisa tu conexión.',
      );
    }
  }

  Map<String, dynamic> _json(http.Response response) {
    try {
      return jsonDecode(response.body) as Map<String, dynamic>;
    } catch (_) {
      throw const AppException(
        'La respuesta del servidor no tiene un formato válido.',
      );
    }
  }

  Never _throwForStatus(int statusCode, String body) {
    if (statusCode == 401) {
      _token = null;
      throw const AppException('Tu sesión expiró. Inicia sesión nuevamente.');
    }
    if (statusCode == 422) {
      throw AppException(
        _messageFromBody(body) ?? 'Revisa los datos ingresados.',
      );
    }
    if (statusCode >= 500) {
      throw const AppException(
        'El servicio no está disponible. Intenta más tarde.',
      );
    }
    throw AppException(
      _messageFromBody(body) ?? 'No se pudo completar la solicitud.',
    );
  }

  String? _messageFromBody(String body) {
    try {
      final json = jsonDecode(body) as Map<String, dynamic>;
      final errors = json['errors'];
      if (errors is Map && errors.isNotEmpty) {
        final first = errors.values.first;
        if (first is List && first.isNotEmpty) return first.first.toString();
      }
      return json['message']?.toString() ?? json['mensaje']?.toString();
    } catch (_) {
      return null;
    }
  }
}

String _categoryCode(String category) => switch (category.toLowerCase()) {
  'soporte tecnológico' => 'soporte_tecnologico',
  'infraestructura' => 'infraestructura',
  'equipamiento' => 'equipamiento',
  'mantenimiento' => 'mantenimiento',
  _ => 'otro',
};

List<CampusRequest> _seedRequests() {
  final now = DateTime.now();
  return [
    CampusRequest(
      id: 'SOL-1042',
      title: 'Proyector sin señal en aula 204',
      category: 'Soporte tecnológico',
      location: 'Bloque B · Aula 204',
      description:
          'El proyector enciende, pero no detecta ningún equipo por HDMI.',
      status: RequestStatus.inProgress,
      priority: 'Alta',
      createdAt: now.subtract(const Duration(days: 2)),
      updatedAt: now.subtract(const Duration(hours: 3)),
      assignee: 'Carlos M. · Soporte TI',
      comments: [
        RequestComment(
          author: 'Carlos M.',
          message:
              'Revisaremos el adaptador y el cableado durante el receso de la tarde.',
          date: now.subtract(const Duration(hours: 3)),
        ),
      ],
      tracking: [
        TrackingEvent(
          title: 'Solicitud recibida',
          description: 'Reporte registrado desde la app móvil.',
          date: now.subtract(const Duration(days: 2)),
        ),
        TrackingEvent(
          title: 'Responsable asignado',
          description: 'Soporte TI tomo el caso.',
          date: now.subtract(const Duration(days: 1, hours: 4)),
        ),
        TrackingEvent(
          title: 'Revision en curso',
          description: 'Se coordinó una inspección técnica.',
          date: now.subtract(const Duration(hours: 3)),
        ),
      ],
    ),
    CampusRequest(
      id: 'SOL-1038',
      title: 'Luminaria dañada en pasillo',
      category: 'Infraestructura',
      location: 'Bloque C · Piso 2',
      description: 'La luminaria frente al laboratorio permanece apagada.',
      status: RequestStatus.assigned,
      priority: 'Media',
      createdAt: now.subtract(const Duration(days: 5)),
      updatedAt: now.subtract(const Duration(days: 1)),
      assignee: 'Unidad de Mantenimiento',
      tracking: [
        TrackingEvent(
          title: 'Solicitud recibida',
          description: 'Reporte registrado correctamente.',
          date: now.subtract(const Duration(days: 5)),
        ),
        TrackingEvent(
          title: 'Responsable asignado',
          description: 'Caso derivado a Mantenimiento.',
          date: now.subtract(const Duration(days: 1)),
        ),
      ],
    ),
    CampusRequest(
      id: 'SOL-1021',
      title: 'Dispensador de agua sin servicio',
      category: 'Equipamiento',
      location: 'Biblioteca · Planta baja',
      description: 'El dispensador no suministra agua fría ni caliente.',
      status: RequestStatus.resolved,
      priority: 'Baja',
      createdAt: now.subtract(const Duration(days: 12)),
      updatedAt: now.subtract(const Duration(days: 8)),
      assignee: 'Unidad de Mantenimiento',
      comments: [
        RequestComment(
          author: 'Mantenimiento',
          message:
              'Se reemplazó el filtro y el equipo ya se encuentra operativo.',
          date: now.subtract(const Duration(days: 8)),
        ),
      ],
      tracking: [
        TrackingEvent(
          title: 'Solicitud recibida',
          description: 'Reporte registrado correctamente.',
          date: now.subtract(const Duration(days: 12)),
        ),
        TrackingEvent(
          title: 'Solicitud resuelta',
          description: 'Equipo verificado y puesto en servicio.',
          date: now.subtract(const Duration(days: 8)),
        ),
      ],
    ),
  ];
}
