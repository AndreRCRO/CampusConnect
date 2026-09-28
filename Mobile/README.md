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
flutter run --dart-define=API_BASE_URL=https://api.example.edu
```

## Contrato REST esperado

- `POST /api/auth/login` con `email` y `password`; devuelve `token` (o `access_token`) y `user`.
- `GET /api/solicitudes`; devuelve una lista o `{ "data": [...] }`.
- `GET /api/solicitudes/{id}`; devuelve la solicitud o `{ "data": {...} }`.
- `POST /api/solicitudes` como `multipart/form-data` con `titulo`, `categoria`, `ubicacion`, `descripcion` y `evidencia`.

El cliente acepta nombres de propiedades en español o inglés para facilitar la integración inicial. Los errores `401`, `422`, `5xx`, timeouts y fallas de conexión tienen mensajes recuperables en la interfaz.

## Verificación

```bash
flutter analyze
flutter test
```

Las pruebas cubren autenticación, validación de formularios, pantalla principal, creación, detalle, seguimiento, comentarios y normalización de estados de la API.

