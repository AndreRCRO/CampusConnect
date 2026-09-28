enum RequestStatus { received, assigned, inProgress, resolved }

extension RequestStatusView on RequestStatus {
  String get label => switch (this) {
    RequestStatus.received => 'Recibida',
    RequestStatus.assigned => 'Asignada',
    RequestStatus.inProgress => 'En proceso',
    RequestStatus.resolved => 'Resuelta',
  };

  static RequestStatus parse(String value) => switch (value.toLowerCase()) {
    'asignada' || 'assigned' => RequestStatus.assigned,
    'en_proceso' || 'in_progress' || 'en proceso' => RequestStatus.inProgress,
    'resuelta' || 'resolved' || 'cerrada' => RequestStatus.resolved,
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
        json['author']?.toString() ??
        json['autor']?.toString() ??
        'Administracion',
    message: json['message']?.toString() ?? json['mensaje']?.toString() ?? '',
    date:
        DateTime.tryParse(
          json['date']?.toString() ?? json['fecha']?.toString() ?? '',
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
    title:
        json['title']?.toString() ??
        json['titulo']?.toString() ??
        'Actualizacion',
    description:
        json['description']?.toString() ??
        json['descripcion']?.toString() ??
        '',
    date:
        DateTime.tryParse(
          json['date']?.toString() ?? json['fecha']?.toString() ?? '',
        ) ??
        DateTime.now(),
    completed: json['completed'] as bool? ?? true,
  );
}

class CampusRequest {
  const CampusRequest({
    required this.id,
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
  });

  final String id;
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
      id: json['id']?.toString() ?? '',
      title:
          json['title']?.toString() ??
          json['titulo']?.toString() ??
          'Solicitud',
      category:
          json['category']?.toString() ??
          json['categoria']?.toString() ??
          'Otro',
      location:
          json['location']?.toString() ?? json['ubicacion']?.toString() ?? '',
      description:
          json['description']?.toString() ??
          json['descripcion']?.toString() ??
          '',
      status: RequestStatusView.parse(
        json['status']?.toString() ?? json['estado']?.toString() ?? '',
      ),
      priority:
          json['priority']?.toString() ??
          json['prioridad']?.toString() ??
          'Media',
      createdAt: created,
      updatedAt:
          DateTime.tryParse(
            json['updated_at']?.toString() ??
                json['fecha_actualizacion']?.toString() ??
                '',
          ) ??
          created,
      assignee:
          json['assignee']?.toString() ??
          json['responsable']?.toString() ??
          'Por asignar',
      evidencePath:
          json['evidence_url']?.toString() ?? json['evidencia_url']?.toString(),
      comments:
          (json['comments'] as List<dynamic>? ??
                  json['comentarios'] as List<dynamic>? ??
                  const [])
              .whereType<Map<String, dynamic>>()
              .map(RequestComment.fromJson)
              .toList(),
      tracking:
          (json['tracking'] as List<dynamic>? ??
                  json['seguimiento'] as List<dynamic>? ??
                  const [])
              .whereType<Map<String, dynamic>>()
              .map(TrackingEvent.fromJson)
              .toList(),
    );
  }
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
