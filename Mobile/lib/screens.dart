import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import 'app_repository.dart';
import 'models.dart';
import 'theme.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({
    super.key,
    required this.repository,
    required this.onAuthenticated,
  });

  final AppRepository repository;
  final ValueChanged<Student> onAuthenticated;

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;
  bool _loading = false;
  String? _error;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final student = await widget.repository.login(
        _emailController.text.trim(),
        _passwordController.text,
      );
      if (mounted) widget.onAuthenticated(student);
    } on AppException catch (error) {
      if (mounted) setState(() => _error = error.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) => SingleChildScrollView(
            padding: EdgeInsets.symmetric(
              horizontal: constraints.maxWidth >= 700
                  ? constraints.maxWidth * .22
                  : 24,
              vertical: 24,
            ),
            child: ConstrainedBox(
              constraints: BoxConstraints(
                minHeight: constraints.maxHeight - 48,
              ),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const _BrandMark(),
                    const SizedBox(height: 40),
                    Text(
                      'Tu campus,\nmás cerca.',
                      style: Theme.of(context).textTheme.displaySmall,
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'Reporta una necesidad y sigue su avance desde un solo lugar.',
                      style: Theme.of(context).textTheme.bodyLarge,
                    ),
                    const SizedBox(height: 32),
                    TextFormField(
                      controller: _emailController,
                      keyboardType: TextInputType.emailAddress,
                      autofillHints: const [AutofillHints.email],
                      textInputAction: TextInputAction.next,
                      decoration: const InputDecoration(
                        labelText: 'Correo institucional',
                        hintText: 'nombre@univalle.edu',
                        prefixIcon: Icon(Icons.alternate_email_rounded),
                      ),
                      validator: (value) {
                        final email = value?.trim() ?? '';
                        if (email.isEmpty) {
                          return 'Ingresa tu correo institucional.';
                        }
                        if (!RegExp(
                          r'^[^@\s]+@[^@\s]+\.[^@\s]+$',
                        ).hasMatch(email)) {
                          return 'Ingresa un correo válido.';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _passwordController,
                      obscureText: _obscurePassword,
                      autofillHints: const [AutofillHints.password],
                      textInputAction: TextInputAction.done,
                      onFieldSubmitted: (_) {
                        if (!_loading) _submit();
                      },
                      decoration: InputDecoration(
                        labelText: 'Contraseña',
                        prefixIcon: const Icon(Icons.lock_outline_rounded),
                        suffixIcon: IconButton(
                          tooltip: _obscurePassword
                              ? 'Mostrar contraseña'
                              : 'Ocultar contraseña',
                          onPressed: () => setState(
                            () => _obscurePassword = !_obscurePassword,
                          ),
                          icon: Icon(
                            _obscurePassword
                                ? Icons.visibility_outlined
                                : Icons.visibility_off_outlined,
                          ),
                        ),
                      ),
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Ingresa tu contraseña.';
                        }
                        if (value.length < 6) {
                          return 'Debe tener al menos 6 caracteres.';
                        }
                        return null;
                      },
                    ),
                    if (_error != null) ...[
                      const SizedBox(height: 16),
                      _InlineMessage(message: _error!, isError: true),
                    ],
                    const SizedBox(height: 24),
                    FilledButton.icon(
                      onPressed: _loading ? null : _submit,
                      icon: _loading
                          ? const SizedBox.square(
                              dimension: 20,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Icon(Icons.arrow_forward_rounded),
                      label: Text(_loading ? 'Ingresando…' : 'Ingresar'),
                    ),
                    if (widget.repository.isDemo) ...[
                      const SizedBox(height: 20),
                      Semantics(
                        label: 'Credenciales del modo demostración',
                        child: Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: AppColors.sky,
                            borderRadius: BorderRadius.circular(18),
                          ),
                          child: const Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Icon(
                                Icons.science_outlined,
                                color: AppColors.navy,
                              ),
                              SizedBox(width: 12),
                              Expanded(
                                child: Text(
                                  'Modo demostración\nestudiante@univalle.edu\nCampus123',
                                  style: TextStyle(
                                    height: 1.45,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class HomeShell extends StatefulWidget {
  const HomeShell({
    super.key,
    required this.repository,
    required this.student,
    required this.onLogout,
  });

  final AppRepository repository;
  final Student student;
  final VoidCallback onLogout;

  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  int _index = 0;
  bool _loading = true;
  String? _error;
  List<CampusRequest> _requests = const [];

  @override
  void initState() {
    super.initState();
    _loadRequests();
  }

  Future<void> _loadRequests() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final requests = await widget.repository.getRequests();
      if (mounted) setState(() => _requests = requests);
    } on AppException catch (error) {
      if (mounted) setState(() => _error = error.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _openRequest(CampusRequest request) {
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => RequestDetailScreen(
          repository: widget.repository,
          requestId: request.apiId,
          initialRequest: request,
        ),
      ),
    );
  }

  Future<void> _created(CampusRequest request) async {
    await _loadRequests();
    if (!mounted) return;
    setState(() => _index = 1);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('${request.id} registrada correctamente.'),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final pages = [
      HomeDashboard(
        student: widget.student,
        requests: _requests,
        loading: _loading,
        error: _error,
        isDemo: widget.repository.isDemo,
        onRetry: _loadRequests,
        onCreate: () => setState(() => _index = 2),
        onViewAll: () => setState(() => _index = 1),
        onOpen: _openRequest,
        onLogout: widget.onLogout,
      ),
      RequestsScreen(
        requests: _requests,
        loading: _loading,
        error: _error,
        onRetry: _loadRequests,
        onOpen: _openRequest,
      ),
      CreateRequestScreen(repository: widget.repository, onCreated: _created),
    ];

    return Scaffold(
      body: SafeArea(
        child: IndexedStack(index: _index, children: pages),
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _index,
        onDestinationSelected: (value) => setState(() => _index = value),
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.grid_view_outlined),
            selectedIcon: Icon(Icons.grid_view_rounded),
            label: 'Inicio',
          ),
          NavigationDestination(
            icon: Icon(Icons.receipt_long_outlined),
            selectedIcon: Icon(Icons.receipt_long_rounded),
            label: 'Solicitudes',
          ),
          NavigationDestination(
            icon: Icon(Icons.add_circle_outline_rounded),
            selectedIcon: Icon(Icons.add_circle_rounded),
            label: 'Nueva',
          ),
        ],
      ),
    );
  }
}

class HomeDashboard extends StatelessWidget {
  const HomeDashboard({
    super.key,
    required this.student,
    required this.requests,
    required this.loading,
    required this.error,
    required this.isDemo,
    required this.onRetry,
    required this.onCreate,
    required this.onViewAll,
    required this.onOpen,
    required this.onLogout,
  });

  final Student student;
  final List<CampusRequest> requests;
  final bool loading;
  final String? error;
  final bool isDemo;
  final VoidCallback onRetry;
  final VoidCallback onCreate;
  final VoidCallback onViewAll;
  final ValueChanged<CampusRequest> onOpen;
  final VoidCallback onLogout;

  @override
  Widget build(BuildContext context) {
    final active = requests
        .where(
          (item) =>
              item.status != RequestStatus.resolved &&
              item.status != RequestStatus.rejected,
        )
        .length;
    final resolved = requests
        .where((item) => item.status == RequestStatus.resolved)
        .length;
    return RefreshIndicator(
      onRefresh: () async => onRetry(),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 32),
        children: [
          Row(
            children: [
              const _BrandMark(compact: true),
              const Spacer(),
              PopupMenuButton<String>(
                tooltip: 'Cuenta de estudiante',
                onSelected: (value) {
                  if (value == 'logout') onLogout();
                },
                itemBuilder: (_) => const [
                  PopupMenuItem(value: 'logout', child: Text('Cerrar sesión')),
                ],
                child: CircleAvatar(
                  radius: 24,
                  backgroundColor: AppColors.mint,
                  foregroundColor: AppColors.navy,
                  child: Text(
                    _initials(student.name),
                    style: const TextStyle(fontWeight: FontWeight.w800),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 28),
          Text(
            'Hola, ${student.name.split(' ').first}',
            style: Theme.of(context).textTheme.headlineMedium,
          ),
          const SizedBox(height: 6),
          Text(
            '¿Qué necesita atención hoy?',
            style: Theme.of(context).textTheme.bodyLarge,
          ),
          if (isDemo) ...[
            const SizedBox(height: 16),
            const _InlineMessage(
              message:
                  'Estás viendo datos de demostración. Configura API_BASE_URL para usar el backend.',
            ),
          ],
          const SizedBox(height: 20),
          LayoutBuilder(
            builder: (context, constraints) {
              final width = (constraints.maxWidth - 12) / 2;
              return Wrap(
                spacing: 12,
                runSpacing: 12,
                children: [
                  _MetricCard(
                    width: width,
                    value: '$active',
                    label: 'En seguimiento',
                    icon: Icons.track_changes_rounded,
                    color: AppColors.amber,
                  ),
                  _MetricCard(
                    width: width,
                    value: '$resolved',
                    label: 'Resueltas',
                    icon: Icons.check_circle_outline_rounded,
                    color: AppColors.mint,
                  ),
                ],
              );
            },
          ),
          const SizedBox(height: 12),
          Material(
            color: AppColors.navy,
            borderRadius: BorderRadius.circular(24),
            child: InkWell(
              onTap: onCreate,
              borderRadius: BorderRadius.circular(24),
              child: Padding(
                padding: const EdgeInsets.all(22),
                child: Row(
                  children: [
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Crear una solicitud',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 20,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          SizedBox(height: 6),
                          Text(
                            'Cuéntanos qué ocurre y adjunta una foto.',
                            style: TextStyle(
                              color: Color(0xFFD6DCF0),
                              height: 1.4,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 16),
                    Container(
                      width: 52,
                      height: 52,
                      decoration: const BoxDecoration(
                        color: Colors.white,
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.arrow_forward_rounded,
                        color: AppColors.navy,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          const SizedBox(height: 28),
          _SectionHeader(
            title: 'Actividad reciente',
            action: 'Ver todas',
            onPressed: onViewAll,
          ),
          const SizedBox(height: 12),
          if (loading)
            const _LoadingBlock()
          else if (error != null)
            _ErrorBlock(message: error!, onRetry: onRetry)
          else if (requests.isEmpty)
            _EmptyBlock(onCreate: onCreate)
          else
            ...requests
                .take(2)
                .map(
                  (request) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: RequestCard(
                      request: request,
                      onTap: () => onOpen(request),
                    ),
                  ),
                ),
        ],
      ),
    );
  }
}

class RequestsScreen extends StatefulWidget {
  const RequestsScreen({
    super.key,
    required this.requests,
    required this.loading,
    required this.error,
    required this.onRetry,
    required this.onOpen,
  });

  final List<CampusRequest> requests;
  final bool loading;
  final String? error;
  final VoidCallback onRetry;
  final ValueChanged<CampusRequest> onOpen;

  @override
  State<RequestsScreen> createState() => _RequestsScreenState();
}

class _RequestsScreenState extends State<RequestsScreen> {
  RequestStatus? _filter;

  @override
  Widget build(BuildContext context) {
    final visible = _filter == null
        ? widget.requests
        : widget.requests
              .where((request) => request.status == _filter)
              .toList();
    return RefreshIndicator(
      onRefresh: () async => widget.onRetry(),
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 24, 20, 32),
        children: [
          Text(
            'Mis solicitudes',
            style: Theme.of(context).textTheme.headlineMedium,
          ),
          const SizedBox(height: 6),
          Text(
            'Consulta cada reporte y su avance.',
            style: Theme.of(context).textTheme.bodyLarge,
          ),
          const SizedBox(height: 20),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                FilterChip(
                  label: const Text('Todas'),
                  selected: _filter == null,
                  onSelected: (_) => setState(() => _filter = null),
                ),
                const SizedBox(width: 8),
                ...RequestStatus.values.map(
                  (status) => Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: FilterChip(
                      label: Text(status.label),
                      selected: _filter == status,
                      onSelected: (_) => setState(() => _filter = status),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
          if (widget.loading)
            const _LoadingBlock()
          else if (widget.error != null)
            _ErrorBlock(message: widget.error!, onRetry: widget.onRetry)
          else if (visible.isEmpty)
            const _NoResultsBlock()
          else
            ...visible.map(
              (request) => Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: RequestCard(
                  request: request,
                  onTap: () => widget.onOpen(request),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class CreateRequestScreen extends StatefulWidget {
  const CreateRequestScreen({
    super.key,
    required this.repository,
    required this.onCreated,
  });

  final AppRepository repository;
  final ValueChanged<CampusRequest> onCreated;

  @override
  State<CreateRequestScreen> createState() => _CreateRequestScreenState();
}

class _CreateRequestScreenState extends State<CreateRequestScreen> {
  static const _categories = [
    'Infraestructura',
    'Soporte tecnológico',
    'Equipamiento',
    'Mantenimiento',
    'Otro',
  ];

  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _locationController = TextEditingController();
  final _descriptionController = TextEditingController();
  String? _category;
  XFile? _evidence;
  bool _loading = false;
  String? _error;

  @override
  void dispose() {
    _titleController.dispose();
    _locationController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  Future<void> _pickEvidence(ImageSource source) async {
    Navigator.of(context).pop();
    try {
      final file = await ImagePicker().pickImage(
        source: source,
        imageQuality: 78,
        maxWidth: 1800,
      );
      if (file != null && mounted) setState(() => _evidence = file);
    } catch (_) {
      if (mounted) {
        setState(
          () => _error =
              'No se pudo acceder a la cámara o galería. Revisa los permisos.',
        );
      }
    }
  }

  void _showImageSource() {
    showModalBottomSheet<void>(
      context: context,
      showDragHandle: true,
      builder: (context) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 8, 20, 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text(
                'Adjuntar evidencia',
                style: Theme.of(context).textTheme.titleLarge,
              ),
              const SizedBox(height: 16),
              ListTile(
                leading: const Icon(Icons.photo_camera_outlined),
                title: const Text('Tomar una foto'),
                onTap: () => _pickEvidence(ImageSource.camera),
              ),
              ListTile(
                leading: const Icon(Icons.photo_library_outlined),
                title: const Text('Elegir de la galería'),
                onTap: () => _pickEvidence(ImageSource.gallery),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_evidence == null) {
      setState(
        () => _error = 'Adjunta una fotografía para respaldar la solicitud.',
      );
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final request = await widget.repository.createRequest(
        CreateRequestInput(
          title: _titleController.text.trim(),
          category: _category!,
          location: _locationController.text.trim(),
          description: _descriptionController.text.trim(),
          evidencePath: _evidence!.path,
        ),
      );
      if (!mounted) return;
      _formKey.currentState!.reset();
      _titleController.clear();
      _locationController.clear();
      _descriptionController.clear();
      setState(() {
        _category = null;
        _evidence = null;
      });
      widget.onCreated(request);
    } on AppException catch (error) {
      if (mounted) setState(() => _error = error.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(20, 24, 20, 32),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'Nueva solicitud',
            style: Theme.of(context).textTheme.headlineMedium,
          ),
          const SizedBox(height: 6),
          Text(
            'Describe el problema con datos claros y una foto.',
            style: Theme.of(context).textTheme.bodyLarge,
          ),
          const SizedBox(height: 24),
          Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                TextFormField(
                  controller: _titleController,
                  textCapitalization: TextCapitalization.sentences,
                  textInputAction: TextInputAction.next,
                  maxLength: 80,
                  decoration: const InputDecoration(
                    labelText: 'Título *',
                    hintText: 'Ej. Proyector sin señal',
                  ),
                  validator: (value) {
                    final text = value?.trim() ?? '';
                    if (text.isEmpty) return 'Escribe un título.';
                    if (text.length < 6) {
                      return 'Describe el problema con al menos 6 caracteres.';
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  isExpanded: true,
                  initialValue: _category,
                  decoration: const InputDecoration(labelText: 'Categoría *'),
                  items: _categories
                      .map(
                        (category) => DropdownMenuItem(
                          value: category,
                          child: Text(category),
                        ),
                      )
                      .toList(),
                  onChanged: (value) => setState(() => _category = value),
                  validator: (value) =>
                      value == null ? 'Selecciona una categoría.' : null,
                ),
                const SizedBox(height: 16),
                TextFormField(
                  controller: _locationController,
                  textCapitalization: TextCapitalization.words,
                  textInputAction: TextInputAction.next,
                  decoration: const InputDecoration(
                    labelText: 'Ubicación *',
                    hintText: 'Bloque, piso y ambiente',
                    prefixIcon: Icon(Icons.location_on_outlined),
                  ),
                  validator: (value) => (value?.trim().isEmpty ?? true)
                      ? 'Indica dónde ocurre.'
                      : null,
                ),
                const SizedBox(height: 16),
                TextFormField(
                  controller: _descriptionController,
                  textCapitalization: TextCapitalization.sentences,
                  minLines: 4,
                  maxLines: 6,
                  maxLength: 500,
                  decoration: const InputDecoration(
                    labelText: 'Descripción *',
                    hintText: 'Explica qué sucede y desde cuándo.',
                    alignLabelWithHint: true,
                  ),
                  validator: (value) {
                    final text = value?.trim() ?? '';
                    if (text.isEmpty) return 'Describe lo ocurrido.';
                    if (text.length < 15) {
                      return 'Agrega un poco más de detalle.';
                    }
                    return null;
                  },
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Evidencia fotográfica *',
            style: Theme.of(context).textTheme.titleMedium,
          ),
          const SizedBox(height: 8),
          Text(
            'Evita incluir rostros o información personal.',
            style: Theme.of(context).textTheme.bodyMedium,
          ),
          const SizedBox(height: 12),
          if (_evidence == null)
            OutlinedButton.icon(
              onPressed: _loading ? null : _showImageSource,
              style: OutlinedButton.styleFrom(
                minimumSize: const Size.fromHeight(112),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(20),
                ),
                side: const BorderSide(color: AppColors.teal),
              ),
              icon: const Icon(Icons.add_a_photo_outlined),
              label: const Text('Tomar o elegir una foto'),
            )
          else
            Semantics(
              label: 'Vista previa de la evidencia seleccionada',
              child: Stack(
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(20),
                    child: Image.file(
                      File(_evidence!.path),
                      height: 190,
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (_, _, _) => Container(
                        height: 112,
                        color: AppColors.sky,
                        alignment: Alignment.center,
                        child: const Text('Fotografía lista para adjuntar'),
                      ),
                    ),
                  ),
                  Positioned(
                    top: 8,
                    right: 8,
                    child: IconButton.filled(
                      tooltip: 'Quitar fotografía',
                      onPressed: () => setState(() => _evidence = null),
                      icon: const Icon(Icons.close_rounded),
                    ),
                  ),
                ],
              ),
            ),
          if (_error != null) ...[
            const SizedBox(height: 16),
            _InlineMessage(message: _error!, isError: true),
          ],
          const SizedBox(height: 24),
          FilledButton.icon(
            key: const Key('create-request-submit'),
            onPressed: _loading ? null : _submit,
            icon: _loading
                ? const SizedBox.square(
                    dimension: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Icon(Icons.send_rounded),
            label: Text(_loading ? 'Enviando…' : 'Registrar solicitud'),
          ),
        ],
      ),
    );
  }
}

class RequestDetailScreen extends StatefulWidget {
  const RequestDetailScreen({
    super.key,
    required this.repository,
    required this.requestId,
    required this.initialRequest,
  });

  final AppRepository repository;
  final String requestId;
  final CampusRequest initialRequest;

  @override
  State<RequestDetailScreen> createState() => _RequestDetailScreenState();
}

class _RequestDetailScreenState extends State<RequestDetailScreen> {
  late CampusRequest _request = widget.initialRequest;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final request = await widget.repository.getRequest(widget.requestId);
      if (mounted) setState(() => _request = request);
    } on AppException catch (error) {
      if (mounted) setState(() => _error = error.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_request.id),
        backgroundColor: AppColors.canvas,
        surfaceTintColor: Colors.transparent,
      ),
      body: SafeArea(
        top: false,
        child: RefreshIndicator(
          onRefresh: _load,
          child: ListView(
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 32),
            children: [
              if (_loading) const LinearProgressIndicator(minHeight: 2),
              if (_error != null) ...[
                _ErrorBlock(message: _error!, onRetry: _load),
                const SizedBox(height: 16),
              ],
              Container(
                padding: const EdgeInsets.all(22),
                decoration: BoxDecoration(
                  color: _statusColor(_request.status),
                  borderRadius: BorderRadius.circular(24),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    StatusPill(status: _request.status),
                    const SizedBox(height: 16),
                    Text(
                      _request.title,
                      style: Theme.of(context).textTheme.headlineMedium,
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        const Icon(Icons.schedule_rounded, size: 18),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Actualizada ${_relativeDate(_request.updatedAt)}',
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Información',
                        style: Theme.of(context).textTheme.titleLarge,
                      ),
                      const SizedBox(height: 16),
                      _DetailRow(
                        icon: Icons.category_outlined,
                        label: 'Categoría',
                        value: _request.category,
                      ),
                      _DetailRow(
                        icon: Icons.location_on_outlined,
                        label: 'Ubicación',
                        value: _request.location,
                      ),
                      _DetailRow(
                        icon: Icons.flag_outlined,
                        label: 'Prioridad',
                        value: _request.priority,
                      ),
                      _DetailRow(
                        icon: Icons.person_outline_rounded,
                        label: 'Responsable',
                        value: _request.assignee,
                      ),
                      const Divider(height: 32),
                      Text(
                        _request.description,
                        style: Theme.of(context).textTheme.bodyLarge,
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),
              Text(
                'Seguimiento',
                style: Theme.of(context).textTheme.titleLarge,
              ),
              const SizedBox(height: 12),
              Card(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(20, 20, 20, 8),
                  child: _request.tracking.isEmpty
                      ? const Padding(
                          padding: EdgeInsets.only(bottom: 12),
                          child: Text('Aún no hay movimientos registrados.'),
                        )
                      : Column(
                          children: [
                            for (
                              var index = 0;
                              index < _request.tracking.length;
                              index++
                            )
                              _TimelineItem(
                                event: _request.tracking[index],
                                isLast: index == _request.tracking.length - 1,
                              ),
                          ],
                        ),
                ),
              ),
              const SizedBox(height: 24),
              Text(
                'Comentarios',
                style: Theme.of(context).textTheme.titleLarge,
              ),
              const SizedBox(height: 12),
              if (_request.comments.isEmpty)
                const Card(
                  child: Padding(
                    padding: EdgeInsets.all(20),
                    child: Text(
                      'Todavía no hay comentarios del equipo responsable.',
                    ),
                  ),
                )
              else
                ..._request.comments.map(
                  (comment) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: Card(
                      child: Padding(
                        padding: const EdgeInsets.all(20),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                const CircleAvatar(
                                  radius: 18,
                                  backgroundColor: AppColors.mint,
                                  child: Icon(
                                    Icons.support_agent_rounded,
                                    size: 20,
                                    color: AppColors.navy,
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Text(
                                    comment.author,
                                    style: Theme.of(
                                      context,
                                    ).textTheme.titleMedium,
                                  ),
                                ),
                                Text(
                                  _shortDate(comment.date),
                                  style: Theme.of(context).textTheme.bodyMedium,
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),
                            Text(
                              comment.message,
                              style: Theme.of(context).textTheme.bodyLarge,
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}

class RequestCard extends StatelessWidget {
  const RequestCard({super.key, required this.request, required this.onTap});

  final CampusRequest request;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(24),
        child: Padding(
          padding: const EdgeInsets.all(18),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      request.id,
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                  ),
                  StatusPill(status: request.status),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                request.title,
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  const Icon(
                    Icons.location_on_outlined,
                    size: 18,
                    color: Color(0xFF626A7C),
                  ),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      request.location,
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                  ),
                  const Icon(
                    Icons.chevron_right_rounded,
                    color: AppColors.navy,
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class StatusPill extends StatelessWidget {
  const StatusPill({super.key, required this.status});

  final RequestStatus status;

  @override
  Widget build(BuildContext context) {
    return Semantics(
      label: 'Estado: ${status.label}',
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
        decoration: BoxDecoration(
          color: _statusColor(status),
          borderRadius: BorderRadius.circular(99),
          border: Border.all(color: _statusInk(status).withValues(alpha: .18)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(_statusIcon(status), size: 15, color: _statusInk(status)),
            const SizedBox(width: 6),
            Text(
              status.label,
              style: TextStyle(
                color: _statusInk(status),
                fontSize: 12,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _BrandMark extends StatelessWidget {
  const _BrandMark({this.compact = false});
  final bool compact;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: compact ? 40 : 48,
          height: compact ? 40 : 48,
          decoration: BoxDecoration(
            color: AppColors.navy,
            borderRadius: BorderRadius.circular(compact ? 13 : 16),
          ),
          child: Icon(
            Icons.hub_rounded,
            color: Colors.white,
            size: compact ? 22 : 27,
          ),
        ),
        const SizedBox(width: 12),
        Text(
          'Campus\nConnect',
          style: TextStyle(
            color: AppColors.ink,
            height: .95,
            fontSize: compact ? 15 : 18,
            fontWeight: FontWeight.w900,
            letterSpacing: -.4,
          ),
        ),
      ],
    );
  }
}

class _MetricCard extends StatelessWidget {
  const _MetricCard({
    required this.width,
    required this.value,
    required this.label,
    required this.icon,
    required this.color,
  });
  final double width;
  final String value;
  final String label;
  final IconData icon;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: width,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(24),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: AppColors.navy),
          const SizedBox(height: 18),
          Text(value, style: Theme.of(context).textTheme.headlineMedium),
          const SizedBox(height: 2),
          Text(label, style: const TextStyle(fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}

class _SectionHeader extends StatelessWidget {
  const _SectionHeader({
    required this.title,
    required this.action,
    required this.onPressed,
  });
  final String title;
  final String action;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: Text(title, style: Theme.of(context).textTheme.titleLarge),
        ),
        TextButton(onPressed: onPressed, child: Text(action)),
      ],
    );
  }
}

class _InlineMessage extends StatelessWidget {
  const _InlineMessage({required this.message, this.isError = false});
  final String message;
  final bool isError;

  @override
  Widget build(BuildContext context) {
    final color = isError ? const Color(0xFFFFE4E0) : AppColors.sky;
    final ink = isError ? const Color(0xFF8A1C13) : AppColors.navy;
    return Semantics(
      liveRegion: true,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(16),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(
              isError
                  ? Icons.error_outline_rounded
                  : Icons.info_outline_rounded,
              color: ink,
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                message,
                style: TextStyle(color: ink, fontWeight: FontWeight.w600),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _LoadingBlock extends StatelessWidget {
  const _LoadingBlock();

  @override
  Widget build(BuildContext context) => const Card(
    child: Padding(
      padding: EdgeInsets.all(28),
      child: Center(child: CircularProgressIndicator()),
    ),
  );
}

class _ErrorBlock extends StatelessWidget {
  const _ErrorBlock({required this.message, required this.onRetry});
  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) => Card(
    child: Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
        children: [
          const Icon(
            Icons.cloud_off_rounded,
            size: 34,
            color: Color(0xFF8A1C13),
          ),
          const SizedBox(height: 10),
          Text(message, textAlign: TextAlign.center),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: onRetry,
            icon: const Icon(Icons.refresh_rounded),
            label: const Text('Reintentar'),
          ),
        ],
      ),
    ),
  );
}

class _EmptyBlock extends StatelessWidget {
  const _EmptyBlock({required this.onCreate});
  final VoidCallback onCreate;

  @override
  Widget build(BuildContext context) => Card(
    child: Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        children: [
          const Icon(Icons.inbox_outlined, size: 40, color: AppColors.teal),
          const SizedBox(height: 12),
          const Text('Todavía no tienes solicitudes.'),
          const SizedBox(height: 12),
          OutlinedButton(
            onPressed: onCreate,
            child: const Text('Crear la primera'),
          ),
        ],
      ),
    ),
  );
}

class _NoResultsBlock extends StatelessWidget {
  const _NoResultsBlock();

  @override
  Widget build(BuildContext context) => const Card(
    child: Padding(
      padding: EdgeInsets.all(24),
      child: Column(
        children: [
          Icon(Icons.filter_alt_off_outlined, size: 38, color: AppColors.teal),
          SizedBox(height: 12),
          Text('No hay solicitudes con este estado.'),
        ],
      ),
    ),
  );
}

class _DetailRow extends StatelessWidget {
  const _DetailRow({
    required this.icon,
    required this.label,
    required this.value,
  });
  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 14),
    child: Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 20, color: AppColors.teal),
        const SizedBox(width: 12),
        SizedBox(
          width: 92,
          child: Text(label, style: Theme.of(context).textTheme.bodyMedium),
        ),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(fontWeight: FontWeight.w700),
          ),
        ),
      ],
    ),
  );
}

class _TimelineItem extends StatelessWidget {
  const _TimelineItem({required this.event, required this.isLast});
  final TrackingEvent event;
  final bool isLast;

  @override
  Widget build(BuildContext context) => IntrinsicHeight(
    child: Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 28,
          child: Column(
            children: [
              Container(
                width: 18,
                height: 18,
                decoration: const BoxDecoration(
                  color: AppColors.teal,
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.check_rounded,
                  size: 13,
                  color: Colors.white,
                ),
              ),
              if (!isLast)
                Expanded(
                  child: Container(width: 2, color: const Color(0xFFB7DDD6)),
                ),
            ],
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Padding(
            padding: const EdgeInsets.only(bottom: 20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  event.title,
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                const SizedBox(height: 3),
                Text(event.description),
                const SizedBox(height: 4),
                Text(
                  _shortDate(event.date),
                  style: Theme.of(context).textTheme.bodyMedium,
                ),
              ],
            ),
          ),
        ),
      ],
    ),
  );
}

Color _statusColor(RequestStatus status) => switch (status) {
  RequestStatus.received => AppColors.sky,
  RequestStatus.assigned => AppColors.coral,
  RequestStatus.inProgress => AppColors.amber,
  RequestStatus.resolved => AppColors.mint,
  RequestStatus.rejected => const Color(0xFFFFE4E0),
};

Color _statusInk(RequestStatus status) => switch (status) {
  RequestStatus.received => const Color(0xFF075985),
  RequestStatus.assigned => const Color(0xFF9A3412),
  RequestStatus.inProgress => const Color(0xFF713F12),
  RequestStatus.resolved => const Color(0xFF166534),
  RequestStatus.rejected => const Color(0xFF8A1C13),
};

IconData _statusIcon(RequestStatus status) => switch (status) {
  RequestStatus.received => Icons.inbox_outlined,
  RequestStatus.assigned => Icons.person_pin_circle_outlined,
  RequestStatus.inProgress => Icons.autorenew_rounded,
  RequestStatus.resolved => Icons.check_circle_outline_rounded,
  RequestStatus.rejected => Icons.cancel_outlined,
};

String _initials(String name) {
  final words = name.trim().split(RegExp(r'\s+'));
  return words
      .take(2)
      .map((word) => word.isEmpty ? '' : word[0].toUpperCase())
      .join();
}

String _shortDate(DateTime date) =>
    '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}';

String _relativeDate(DateTime date) {
  final difference = DateTime.now().difference(date);
  if (difference.inMinutes < 60) {
    return 'hace ${difference.inMinutes.clamp(1, 59)} min';
  }
  if (difference.inHours < 24) return 'hace ${difference.inHours} h';
  if (difference.inDays == 1) return 'ayer';
  return 'el ${_shortDate(date)}';
}
