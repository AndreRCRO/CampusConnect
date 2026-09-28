@extends('layouts.admin')

@section('title', 'Editar Recurso ' . $resource->code)
@section('page_header', 'Editar Recurso Institucional')
@section('page_subheader', 'Modificar datos o estado del recurso ' . $resource->code)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <a href="{{ route('admin.resources.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-cyan-400 transition-colors">
        &larr; Volver al catálogo de recursos
    </a>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-6">
        <h2 class="text-lg font-bold text-white border-b border-slate-800 pb-3">Editar Recurso: {{ $resource->name }}</h2>

        <form action="{{ route('admin.resources.update', $resource->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Nombre del Recurso *</label>
                    <input type="text" name="name" value="{{ old('name', $resource->name) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:outline-none focus:border-cyan-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Código Único *</label>
                    <input type="text" name="code" value="{{ old('code', $resource->code) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:outline-none focus:border-cyan-500 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Categoría / Tipo *</label>
                    <select name="category" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:outline-none focus:border-cyan-500">
                        <option value="soporte_tecnologico" {{ old('category', $resource->category) === 'soporte_tecnologico' ? 'selected' : '' }}>Soporte Tecnológico</option>
                        <option value="mantenimiento" {{ old('category', $resource->category) === 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                        <option value="infraestructura" {{ old('category', $resource->category) === 'infraestructura' ? 'selected' : '' }}>Infraestructura</option>
                        <option value="equipamiento" {{ old('category', $resource->category) === 'equipamiento' ? 'selected' : '' }}>Equipamiento</option>
                        <option value="otro" {{ old('category', $resource->category) === 'otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Ubicación Física</label>
                    <input type="text" name="location" value="{{ old('location', $resource->location) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Descripción / Especificaciones Técnicas</label>
                <textarea name="description" rows="3"
                          class="w-full p-3 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white focus:outline-none focus:border-cyan-500">{{ old('description', $resource->description) }}</textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $resource->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-cyan-600">
                <label for="is_active" class="text-xs font-semibold text-slate-300 cursor-pointer">Recurso activo</label>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <a href="{{ route('admin.resources.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold hover:bg-slate-700 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-indigo-600 text-white text-xs font-bold shadow-lg shadow-cyan-600/20 hover:from-cyan-500 hover:to-indigo-500 transition-all">
                    Actualizar Recurso
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
