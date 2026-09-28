@extends('layouts.admin')

@section('title', 'Bandeja de Solicitudes')
@section('page_header', 'Bandeja de Solicitudes Institucionales')
@section('page_subheader', 'Gestión, asignación y priorización centralizada de requerimientos')

@section('content')
<div class="space-y-6">
    <!-- FILTERS BAR -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <form method="GET" action="{{ route('admin.requests.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
            <!-- Filter Search -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Buscar por código o título</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ej: REQ-2026... o proyector"
                       class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500">
            </div>

            <!-- Filter Estado -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Estado</label>
                <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:outline-none focus:border-cyan-500 capitalize">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" {{ request('status') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="en_proceso" {{ request('status') === 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                    <option value="resuelto" {{ request('status') === 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                    <option value="cerrado" {{ request('status') === 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                    <option value="rechazado" {{ request('status') === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                </select>
            </div>

            <!-- Filter Prioridad -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Prioridad</label>
                <select name="priority" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:outline-none focus:border-cyan-500 capitalize">
                    <option value="">Todas las prioridades</option>
                    <option value="urgente" {{ request('priority') === 'urgente' ? 'selected' : '' }}>Urgente</option>
                    <option value="alta" {{ request('priority') === 'alta' ? 'selected' : '' }}>Alta</option>
                    <option value="media" {{ request('priority') === 'media' ? 'selected' : '' }}>Media</option>
                    <option value="baja" {{ request('priority') === 'baja' ? 'selected' : '' }}>Baja</option>
                </select>
            </div>

            <!-- Filter Tipo / Categoría -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Tipo de Servicio</label>
                <select name="category" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:outline-none focus:border-cyan-500">
                    <option value="">Todos los tipos</option>
                    <option value="mantenimiento" {{ request('category') === 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                    <option value="soporte_tecnologico" {{ request('category') === 'soporte_tecnologico' ? 'selected' : '' }}>Soporte Tecnológico</option>
                    <option value="infraestructura" {{ request('category') === 'infraestructura' ? 'selected' : '' }}>Infraestructura</option>
                    <option value="equipamiento" {{ request('category') === 'equipamiento' ? 'selected' : '' }}>Equipamiento</option>
                    <option value="otro" {{ request('category') === 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>

            <!-- Filter Actions -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs rounded-xl shadow-lg shadow-cyan-600/20 transition-all">
                    Filtrar
                </button>
                <a href="{{ route('admin.requests.index') }}" class="py-2.5 px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs rounded-xl transition-colors">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- REQUESTS TABLE -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-950/80 text-xs uppercase font-semibold text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="px-5 py-4">Código / Fecha</th>
                        <th class="px-5 py-4">Título & Categoría</th>
                        <th class="px-5 py-4">Estudiante</th>
                        <th class="px-5 py-4">Recurso Vinculado</th>
                        <th class="px-5 py-4">Prioridad</th>
                        <th class="px-5 py-4">Estado</th>
                        <th class="px-5 py-4">Responsable</th>
                        <th class="px-5 py-4 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-mono text-xs font-bold text-cyan-400">{{ $req->tracking_code }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $req->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-white max-w-xs truncate">{{ $req->title }}</div>
                                <span class="inline-block text-[11px] text-cyan-300 font-medium capitalize mt-0.5">
                                    {{ str_replace('_', ' ', $req->category) }}
                                </span>
                                @if($req->media_evidences_count ?? $req->mediaEvidences->count() > 0)
                                    <span class="inline-flex items-center gap-1 text-[10px] text-amber-400 bg-amber-500/10 px-1.5 py-0.5 rounded ml-2">
                                        📎 {{ $req->media_evidences_count ?? $req->mediaEvidences->count() }} evidencia(s)
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-300">
                                <div class="font-medium text-white">{{ $req->student->name ?? 'Estudiante' }}</div>
                                <div class="text-slate-500">{{ $req->student->email ?? '' }}</div>
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-400">
                                @if($req->institutionalResource)
                                    <span class="font-medium text-slate-200">{{ $req->institutionalResource->name }}</span>
                                    <div class="text-[10px] text-slate-500">{{ $req->institutionalResource->code }}</div>
                                @else
                                    <span class="text-slate-600 italic">No especificado</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @switch($req->priority)
                                    @case('urgente')
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase bg-rose-500/20 text-rose-300 border border-rose-500/30">Urgente</span>
                                        @break
                                    @case('alta')
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase bg-orange-500/20 text-orange-300 border border-orange-500/30">Alta</span>
                                        @break
                                    @case('media')
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase bg-blue-500/20 text-blue-300 border border-blue-500/30">Media</span>
                                        @break
                                    @default
                                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase bg-slate-800 text-slate-400">Baja</span>
                                @endswitch
                            </td>
                            <td class="px-5 py-4">
                                @switch($req->status)
                                    @case('pendiente')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">Pendiente</span>
                                        @break
                                    @case('en_proceso')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">En Proceso</span>
                                        @break
                                    @case('resuelto')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Resuelto</span>
                                        @break
                                    @case('cerrado')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-800 text-slate-300">Cerrado</span>
                                        @break
                                    @default
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400">Rechazado</span>
                                @endswitch
                            </td>
                            <td class="px-5 py-4 text-xs text-slate-300">
                                @if($req->assignedStaff)
                                    <div class="font-medium text-cyan-300">👤 {{ $req->assignedStaff->name }}</div>
                                @else
                                    <span class="text-amber-500/80 font-medium text-[11px]">⚠️ Sin asignar</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <a href="{{ route('admin.requests.show', $req->id) }}" 
                                   class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl bg-cyan-600/20 hover:bg-cyan-600 text-cyan-300 hover:text-white border border-cyan-500/30 text-xs font-semibold transition-all">
                                    <span>Ver / Atender</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                                <div class="text-3xl mb-2">🔍</div>
                                <p class="text-sm font-medium">No se encontraron solicitudes con los filtros seleccionados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/60">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
