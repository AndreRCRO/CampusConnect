@extends('layouts.admin')

@section('title', 'Detalle de Solicitud ' . $studentRequest->tracking_code)
@section('page_header', 'Detalle de Solicitud ' . $studentRequest->tracking_code)
@section('page_subheader', 'Acción administrativa, revisión de evidencias y trazabilidad')

@section('content')
<div class="space-y-6">
    <!-- Back Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.requests.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-cyan-400 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver a la Bandeja de Solicitudes
        </a>

        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-400 font-mono">Código: {{ $studentRequest->tracking_code }}</span>
        </div>
    </div>

    <!-- MAIN TWO COLUMN GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- LEFT 2 COLUMNS: DETAILS, EVIDENCE & TIMELINE -->
        <div class="lg:col-span-2 space-y-6">
            <!-- REQUEST SUMMARY CARD -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl">
                <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-800 pb-4">
                    <div>
                        <span class="px-2.5 py-1 rounded-md text-xs font-extrabold uppercase bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                            {{ str_replace('_', ' ', $studentRequest->category) }}
                        </span>
                        <h2 class="text-xl font-bold text-white mt-2">{{ $studentRequest->title }}</h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Registrado el {{ $studentRequest->created_at->format('d/m/Y a las H:i hrs') }} (hace {{ $studentRequest->created_at->diffForHumans() }})
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Priority Badge -->
                        @switch($studentRequest->priority)
                            @case('urgente')
                                <span class="px-3 py-1 rounded-lg text-xs font-black uppercase bg-rose-500/20 text-rose-300 border border-rose-500/40">⚡ Urgente</span>
                                @break
                            @case('alta')
                                <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase bg-orange-500/20 text-orange-300 border border-orange-500/40">🔥 Alta</span>
                                @break
                            @case('media')
                                <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase bg-blue-500/20 text-blue-300 border border-blue-500/40">🔷 Media</span>
                                @break
                            @default
                                <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase bg-slate-800 text-slate-300">🔹 Baja</span>
                        @endswitch

                        <!-- Status Badge -->
                        @switch($studentRequest->status)
                            @case('pendiente')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">Pendiente</span>
                                @break
                            @case('en_proceso')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/30">En Proceso</span>
                                @break
                            @case('resuelto')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">Resuelto</span>
                                @break
                            @case('cerrado')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-700 text-slate-300">Cerrado</span>
                                @break
                            @default
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400">Rechazado</span>
                        @endswitch
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Descripción detallada del requerimiento</h3>
                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-800/80 text-slate-200 text-sm leading-relaxed whitespace-pre-line">
                        {{ $studentRequest->description }}
                    </div>
                </div>

                <!-- Student & Resource Meta -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800/60 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold text-sm shrink-0">
                            🎓
                        </div>
                        <div>
                            <div class="text-xs text-slate-400 font-semibold uppercase">Estudiante Solicitante</div>
                            <div class="text-sm font-semibold text-white">{{ $studentRequest->student->name ?? 'Estudiante' }}</div>
                            <div class="text-xs text-slate-400">{{ $studentRequest->student->email ?? '' }}</div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800/60 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-sm shrink-0">
                            🏢
                        </div>
                        <div>
                            <div class="text-xs text-slate-400 font-semibold uppercase">Ubicación reportada</div>
                            <div class="text-sm font-semibold text-white">{{ $studentRequest->location ?: 'No especificada' }}</div>
                            @if($studentRequest->institutionalResource)
                                <div class="text-xs text-cyan-400 font-mono">{{ $studentRequest->institutionalResource->code }} | {{ $studentRequest->institutionalResource->name }}</div>
                            @else
                                <div class="text-xs text-slate-500 italic">Sin recurso institucional vinculado</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- EVIDENCIAS MULTIMEDIA GALERIA -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">📷</span>
                        <h3 class="text-base font-bold text-white">Evidencias Multimedia Adjuntas</h3>
                    </div>
                    <span class="text-xs font-semibold text-slate-400 bg-slate-800 px-2.5 py-1 rounded-full">
                        {{ $studentRequest->mediaEvidences->count() }} archivo(s)
                    </span>
                </div>

                @if($studentRequest->mediaEvidences->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($studentRequest->mediaEvidences as $media)
                            <div class="bg-slate-950 border border-slate-800 rounded-xl p-3 space-y-2 group hover:border-cyan-500/50 transition-colors">
                                <div class="aspect-video bg-slate-900 rounded-lg overflow-hidden flex items-center justify-center relative">
                                    @if(str_contains($media->file_type, 'image'))
                                        <img src="{{ asset('storage/' . $media->file_path) }}" alt="{{ $media->file_name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="text-center p-2">
                                            <span class="text-3xl">📄</span>
                                            <p class="text-[10px] text-slate-400 mt-1 uppercase font-semibold">{{ $media->file_type }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="truncate">
                                    <p class="text-xs font-semibold text-white truncate" title="{{ $media->file_name }}">{{ $media->file_name }}</p>
                                    <p class="text-[10px] text-slate-500">{{ round($media->file_size / 1024, 1) }} KB | {{ $media->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <a href="{{ asset('storage/' . $media->file_path) }}" target="_blank" download
                                   class="block w-full text-center py-1.5 bg-slate-900 hover:bg-cyan-600 text-xs font-medium text-slate-300 hover:text-white rounded-lg transition-colors">
                                    Descargar / Abrir Evidencia
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center bg-slate-950/60 border border-dashed border-slate-800 rounded-xl text-slate-500 text-xs">
                        El estudiante no adjuntó archivos multimedia al registrar la solicitud.
                    </div>
                @endif
            </div>

            <!-- BITACORA Y TIMELINE DE SEGUIMIENTO -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                    <span class="text-lg">📜</span>
                    <h3 class="text-base font-bold text-white">Bitácora & Trazabilidad del Ciclo de Vida</h3>
                </div>

                <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-800">
                    @forelse($studentRequest->statusHistories as $history)
                        <div class="relative group">
                            <div class="absolute -left-6 top-1 w-3 h-3 rounded-full bg-cyan-500 border-2 border-slate-900"></div>
                            <div class="bg-slate-950 p-4 rounded-xl border border-slate-800/80 space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-cyan-400 uppercase">
                                        Cambio: {{ $history->previous_status }} &rarr; {{ $history->new_status }}
                                    </span>
                                    <span class="text-slate-500">{{ $history->created_at->format('d/m/Y H:i hrs') }}</span>
                                </div>
                                <p class="text-xs text-slate-300">{{ $history->notes ?? 'Sin observaciones anotadas.' }}</p>
                                <div class="text-[11px] text-slate-500 pt-1">
                                    Por: <span class="text-slate-300 font-semibold">{{ $history->changedByUser->name ?? 'Sistema' }}</span> ({{ $history->changedByUser->role ?? 'admin' }})
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">Sin historial registrado aún.</p>
                    @endforelse
                </div>
            </div>

            <!-- SECCION DE COMENTARIOS Y OBSERVACIONES DE ATENCION -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                    <span class="text-lg">💬</span>
                    <h3 class="text-base font-bold text-white">Comentarios y Comunicaciones</h3>
                </div>

                <!-- Comment Form -->
                <form action="{{ route('admin.requests.store_comment', $studentRequest->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <textarea name="comment" rows="3" required placeholder="Escribe un comentario u observación sobre la atención de la solicitud..."
                                  class="w-full p-3 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500"></textarea>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_internal" value="1" class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-cyan-600">
                            <span class="text-xs text-amber-400 font-medium">Marcar como Nota Interna (solo visible para personal administrativo)</span>
                        </label>

                        <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs rounded-xl shadow-lg shadow-cyan-600/20 transition-all">
                            Agregar Comentario
                        </button>
                    </div>
                </form>

                <!-- Comments List -->
                <div class="space-y-3 pt-2">
                    @foreach($studentRequest->comments as $comment)
                        <div class="p-4 rounded-xl {{ $comment->is_internal ? 'bg-amber-500/5 border border-amber-500/20' : 'bg-slate-950 border border-slate-800' }} space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-white">{{ $comment->author->name ?? 'Usuario' }}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] uppercase font-semibold bg-slate-800 text-slate-400">{{ $comment->author->role ?? 'user' }}</span>
                                    @if($comment->is_internal)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] uppercase font-bold bg-amber-500/20 text-amber-300">Nota Interna</span>
                                    @endif
                                </div>
                                <span class="text-slate-500">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-xs text-slate-300 whitespace-pre-line">{{ $comment->comment }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- RIGHT 1 COLUMN: ADMINISTRATIVE ACTIONS PANEL -->
        <div class="space-y-6">
            <!-- ACTION CARD 1: ASIGNAR RESPONSABLE -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                    <span class="text-lg">👤</span>
                    <h3 class="text-base font-bold text-white">Asignar Responsable / Técnico</h3>
                </div>

                <div class="text-xs text-slate-400">
                    Actualmente asignado a: 
                    @if($studentRequest->assignedStaff)
                        <span class="font-bold text-cyan-400">{{ $studentRequest->assignedStaff->name }}</span>
                    @else
                        <span class="font-bold text-amber-400">Ningún encargado asignado</span>
                    @endif
                </div>

                <form action="{{ route('admin.requests.assign_staff', $studentRequest->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <select name="assigned_to" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none focus:border-cyan-500">
                            <option value="">Seleccionar personal...</option>
                            @foreach($availableStaff as $staff)
                                <option value="{{ $staff->id }}" {{ $studentRequest->assigned_to == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }} ({{ strtoupper($staff->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs rounded-xl shadow-lg shadow-indigo-600/20 transition-all">
                        Actualizar Asignación
                    </button>
                </form>
            </div>

            <!-- ACTION CARD 2: CAMBIAR ESTADO -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                    <span class="text-lg">🔄</span>
                    <h3 class="text-base font-bold text-white">Cambiar Estado del Requerimiento</h3>
                </div>

                <form action="{{ route('admin.requests.update_status', $studentRequest->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nuevo Estado</label>
                        <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none focus:border-cyan-500 capitalize">
                            <option value="pendiente" {{ $studentRequest->status === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="en_proceso" {{ $studentRequest->status === 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                            <option value="resuelto" {{ $studentRequest->status === 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                            <option value="cerrado" {{ $studentRequest->status === 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                            <option value="rechazado" {{ $studentRequest->status === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Nota de la Bitácora</label>
                        <textarea name="notes" rows="2" placeholder="Explica las acciones realizadas o motivos del cambio..."
                                  class="w-full p-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500"></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs rounded-xl shadow-lg shadow-emerald-600/20 transition-all">
                        Guardar Cambio de Estado
                    </button>
                </form>
            </div>

            <!-- ACTION CARD 3: CAMBIAR PRIORIDAD -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl">
                <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                    <span class="text-lg">⚡</span>
                    <h3 class="text-base font-bold text-white">Ajustar Nivel de Prioridad</h3>
                </div>

                <form action="{{ route('admin.requests.update_priority', $studentRequest->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <select name="priority" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none focus:border-cyan-500 capitalize">
                            <option value="urgente" {{ $studentRequest->priority === 'urgente' ? 'selected' : '' }}>Urgente</option>
                            <option value="alta" {{ $studentRequest->priority === 'alta' ? 'selected' : '' }}>Alta</option>
                            <option value="media" {{ $studentRequest->priority === 'media' ? 'selected' : '' }}>Media</option>
                            <option value="baja" {{ $studentRequest->priority === 'baja' ? 'selected' : '' }}>Baja</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-500 text-white font-semibold text-xs rounded-xl shadow-lg shadow-amber-600/20 transition-all">
                        Actualizar Prioridad
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
