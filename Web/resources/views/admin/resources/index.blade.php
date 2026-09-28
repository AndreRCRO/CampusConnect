@extends('layouts.admin')

@section('title', 'Gestión de Recursos Institucionales')
@section('page_header', 'Gestión de Recursos Institucionales')
@section('page_subheader', 'Administración de aulas, laboratorios, equipamiento e infraestructura vinculable')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-900 border border-slate-800 p-5 rounded-2xl">
        <form method="GET" action="{{ route('admin.resources.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar recurso por código o nombre..."
                   class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 min-w-[240px]">

            <select name="category" class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none focus:border-cyan-500 capitalize">
                <option value="">Todas las categorías</option>
                <option value="mantenimiento" {{ request('category') === 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                <option value="soporte_tecnologico" {{ request('category') === 'soporte_tecnologico' ? 'selected' : '' }}>Soporte Tecnológico</option>
                <option value="infraestructura" {{ request('category') === 'infraestructura' ? 'selected' : '' }}>Infraestructura</option>
                <option value="equipamiento" {{ request('category') === 'equipamiento' ? 'selected' : '' }}>Equipamiento</option>
                <option value="otro" {{ request('category') === 'otro' ? 'selected' : '' }}>Otro</option>
            </select>

            <button type="submit" class="py-2 px-3 bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs rounded-xl transition-all">
                Buscar
            </button>
        </form>

        <a href="{{ route('admin.resources.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-cyan-600 to-indigo-600 hover:from-cyan-500 hover:to-indigo-500 text-white font-semibold text-xs rounded-xl shadow-lg shadow-cyan-600/20 transition-all shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Registrar Nuevo Recurso</span>
        </a>
    </div>

    <!-- RESOURCES GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($resources as $res)
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4 relative flex flex-col justify-between group hover:border-slate-700 transition-all">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded border border-cyan-500/20">
                            {{ $res->code }}
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $res->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-slate-800 text-slate-500' }}">
                            {{ $res->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <h3 class="text-base font-bold text-white group-hover:text-cyan-300 transition-colors">{{ $res->name }}</h3>
                    <p class="text-xs text-slate-400 capitalize">Categoría: <span class="text-slate-200 font-semibold">{{ str_replace('_', ' ', $res->category) }}</span></p>

                    @if($res->location)
                        <div class="text-xs text-slate-400 flex items-center gap-1.5 pt-1">
                            <span>📍</span>
                            <span>{{ $res->location }}</span>
                        </div>
                    @endif

                    @if($res->description)
                        <p class="text-xs text-slate-400 line-clamp-2 pt-1 border-t border-slate-800/80">{{ $res->description }}</p>
                    @endif
                </div>

                <div class="pt-4 border-t border-slate-800 flex items-center justify-between mt-3">
                    <span class="text-xs text-slate-400">
                        📋 <span class="font-bold text-white">{{ $res->student_requests_count }}</span> solicitudes vinculadas
                    </span>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.resources.edit', $res->id) }}" class="p-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg transition-colors text-xs font-semibold">
                            Editar
                        </a>

                        <form action="{{ route('admin.resources.destroy', $res->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este recurso?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white rounded-lg transition-colors text-xs font-semibold">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center text-slate-500 space-y-2">
                <div class="text-4xl">🏢</div>
                <p class="text-sm font-medium">No hay recursos institucionales registrados aún.</p>
                <a href="{{ route('admin.resources.create') }}" class="inline-block text-xs text-cyan-400 hover:underline">Registrar el primero ahora &rarr;</a>
            </div>
        @endforelse
    </div>

    @if($resources->hasPages())
        <div class="p-4 bg-slate-900 border border-slate-800 rounded-2xl">
            {{ $resources->links() }}
        </div>
    @endif
</div>
@endsection
