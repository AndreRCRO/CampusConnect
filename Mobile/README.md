# Campus Connect Mobile

Aplicación Flutter para que estudiantes registren solicitudes universitarias, adjunten evidencia fotográfica y consulten su estado, trazabilidad y comentarios.

## Ejecutar

Sin backend, la app inicia en modo demostración:

```text
correo: estudiante@univalle.edu
contraseña: Campus123
```

```bash
flutter pub get
flutter run
```

Para consumir la API REST real:

```bash
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000
```

## Contrato REST esperado

- `POST /api/v1/auth/login` con `email`, `password` y `device_name`.
- `GET /api/v1/student/requests` para listar las solicitudes del estudiante.
- `GET /api/v1/student/requests/{id}` para detalle, comentarios y seguimiento.
- `POST /api/v1/student/requests` para registrar título, categoría, ubicación y descripción.
- `POST /api/v1/student/requests/{id}/media` como `multipart/form-data` para adjuntar la evidencia.

En Android Emulator, `10.0.2.2` apunta al equipo anfitrión. Inicia Laravel con `php artisan serve --host=0.0.0.0`. Los errores `401`, `422`, `5xx`, timeouts y fallas de conexión tienen mensajes recuperables en la interfaz.

## Verificación

```bash
flutter analyze
flutter test
```

Las pruebas cubren autenticación, validación de formularios, pantalla principal, creación, detalle, seguimiento, comentarios y normalización de estados de la API.

