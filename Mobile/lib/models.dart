enum RequestStatus { received, assigned, inProgress, resolved, rejected }

extension RequestStatusView on RequestStatus {
  String get label => switch (this) {
    RequestStatus.received => 'Recibida',
    RequestStatus.assigned => 'Asignada',
    RequestStatus.inProgress => 'En proceso',
    RequestStatus.resolved => 'Resuelta',
    RequestStatus.rejected => 'Rechazada',
  };

  static RequestStatus parse(String value) => switch (value.toLowerCase()) {
    'asignada' || 'assigned' => RequestStatus.assigned,
    'en_proceso' || 'in_progress' || 'en proceso' => RequestStatus.inProgress,
    'resuelta' ||
    'resuelto' ||
    'resolved' ||
    'cerrada' ||
    'cerrado' => RequestStatus.resolved,
    'rechazada' || 'rechazado' || 'rejected' => RequestStatus.rejected,
    _ => RequestStatus.received,
  };
}

class Student {
  const Student({required this.name, required this.email});

  final String name;
  final String email;

  factory Student.fromJson(Map<String, dynamic> json) => Student(
    name:
        json['name']?.toString() ?? json['nombre']?.toString() ?? 'Estudiante',
    email: json['email']?.toString() ?? '',
  );
}

class RequestComment {
  const RequestComment({
    required this.author,
    required this.message,
    required this.date,
  });

  final String author;
  final String message;
  final DateTime date;

  factory RequestComment.fromJson(Map<String, dynamic> json) => RequestComment(
    author:
        _nestedName(json['author']) ??
        json['autor']?.toString() ??
        'Administración',
    message:
        json['comment']?.toString() ??
        json['message']?.toString() ??
        json['mensaje']?.toString() ??
        '',
    date:
        DateTime.tryParse(
          json['created_at']?.toString() ??
              json['date']?.toString() ??
              json['fecha']?.toString() ??
              '',
        ) ??
        DateTime.now(),
  );
}

class TrackingEvent {
  const TrackingEvent({
    required this.title,
    required this.description,
    required this.date,
    this.completed = true,
  });

  final String title;
  final String description;
  final DateTime date;
  final bool completed;

  factory TrackingEvent.fromJson(Map<String, dynamic> json) => TrackingEvent(
    title: json['new_status'] == null
        ? json['title']?.toString() ??
              json['titulo']?.toString() ??
              'Actualización'
        : 'Estado: ${RequestStatusView.parse(json['new_status'].toString()).label}',
    description:
        json['notes']?.toString() ??
        json['description']?.toString() ??
        json['descripcion']?.toString() ??
        '',
    date:
        DateTime.tryParse(
          json['created_at']?.toString() ??
              json['date']?.toString() ??
              json['fecha']?.toString() ??
              '',
        ) ??
        DateTime.now(),
    completed: json['completed'] as bool? ?? true,
  );
}

class CampusRequest {
  const CampusRequest({
    required this.id,
    String? apiId,
    required this.title,
    required this.category,
    required this.location,
    required this.description,
    required this.status,
    required this.priority,
    required this.createdAt,
    required this.updatedAt,
    required this.assignee,
    this.evidencePath,
    this.comments = const [],
    this.tracking = const [],
  }) : apiId = apiId ?? id;

  final String id;
  final String apiId;
  final String title;
  final String category;
  final String location;
  final String description;
  final RequestStatus status;
  final String priority;
  final DateTime createdAt;
  final DateTime updatedAt;
  final String assignee;
  final String? evidencePath;
  final List<RequestComment> comments;
  final List<TrackingEvent> tracking;

  factory CampusRequest.fromJson(Map<String, dynamic> json) {
    final created =
        DateTime.tryParse(
          json['created_at']?.toString() ??
              json['fecha_creacion']?.toString() ??
              '',
        ) ??
        DateTime.now();
    return CampusRequest(
      id: json['tracking_code']?.toString() ?? json['id']?.toString() ?? '',
      apiId: json['id']?.toString(),
      title:
          json['title']?.toString() ??
          json['titulo']?.toString() ??
          'Solicitud',
      category: _categoryLabel(
        json['category']?.toString() ?? json['categoria']?.toString() ?? 'otro',
      ),
      location:
          json['location']?.toString() ??
          _nestedValue(json['institutional_resource'], 'location') ??
          json['ubicacion']?.toString() ??
          'Ubicación no especificada',
      description:
          json['description']?.toString() ??
          json['descripcion']?.toString() ??
          '',
      status: _statusFromRequest(json),
      priority: _humanize(
        json['priority']?.toString() ??
            json['prioridad']?.toString() ??
            'media',
      ),
      createdAt: created,
      updatedAt:
          DateTime.tryParse(
            json['updated_at']?.toString() ??
                json['fecha_actualizacion']?.toString() ??
                '',
          ) ??
          created,
      assignee:
          _nestedName(json['assigned_staff']) ??
          json['assignee']?.toString() ??
          json['responsable']?.toString() ??
          'Por asignar',
      evidencePath:
          _firstMediaPath(json['media_evidences']) ??
          json['evidence_url']?.toString() ??
          json['evidencia_url']?.toString(),
      comments:
          (json['comments'] as List<dynamic>? ??
                  json['comentarios'] as List<dynamic>? ??
                  const [])
              .whereType<Map<String, dynamic>>()
              .map(RequestComment.fromJson)
              .toList(),
      tracking:
          (json['tracking'] as List<dynamic>? ??
                  json['status_histories'] as List<dynamic>? ??
                  json['timeline'] as List<dynamic>? ??
                  json['seguimiento'] as List<dynamic>? ??
                  const [])
              .whereType<Map<String, dynamic>>()
              .map(TrackingEvent.fromJson)
              .toList(),
    );
  }
}

RequestStatus _statusFromRequest(Map<String, dynamic> json) {
  final value = json['status']?.toString() ?? json['estado']?.toString() ?? '';
  if (value == 'pendiente' && json['assigned_staff'] != null) {
    return RequestStatus.assigned;
  }
  return RequestStatusView.parse(value);
}

String _humanize(String value) {
  final text = value.replaceAll('_', ' ').trim();
  return text.isEmpty ? '' : '${text[0].toUpperCase()}${text.substring(1)}';
}

String _categoryLabel(String value) => switch (value.toLowerCase()) {
  'soporte_tecnologico' => 'Soporte tecnológico',
  'infraestructura' => 'Infraestructura',
  'equipamiento' => 'Equipamiento',
  'mantenimiento' => 'Mantenimiento',
  _ => 'Otro',
};

String? _nestedName(dynamic value) => _nestedValue(value, 'name');

String? _nestedValue(dynamic value, String key) {
  if (value is Map<String, dynamic>) return value[key]?.toString();
  return null;
}

String? _firstMediaPath(dynamic value) {
  if (value is! List || value.isEmpty) return null;
  final first = value.first;
  return first is Map<String, dynamic> ? first['file_path']?.toString() : null;
}

class CreateRequestInput {
  const CreateRequestInput({
    required this.title,
    required this.category,
    required this.location,
    required this.description,
    this.evidencePath,
  });

  final String title;
  final String category;
  final String location;
  final String description;
  final String? evidencePath;
}
