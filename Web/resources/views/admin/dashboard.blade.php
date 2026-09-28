@extends('layouts.admin')

@section('title', 'Dashboard General')
@section('page_header', 'Panel de Control Administrativo')
@section('page_subheader', 'Visión general en tiempo real del estado de solicitudes institucionales')

@section('content')
<div class="space-y-6">
    <!-- STATS CARDS GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Card Total -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Solicitudes</span>
                <span class="p-2 rounded-xl bg-blue-500/10 text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
            </div>
            <div class="text-3xl font-black text-white mt-3">{{ $totalRequests }}</div>
            <p class="text-xs text-slate-400 mt-1">Registradas en el sistema</p>
        </div>

        <!-- Card Pendientes -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-400">Pendientes</span>
                <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="text-3xl font-black text-amber-400 mt-3">{{ $pendingRequests }}</div>
            <p class="text-xs text-slate-400 mt-1">Requieren revisión inicial</p>
        </div>

        <!-- Card En Proceso -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-cyan-400">En Atencion</span>
                <span class="p-2 rounded-xl bg-cyan-500/10 text-cyan-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
            </div>
            <div class="text-3xl font-black text-cyan-400 mt-3">{{ $inProgressRequests }}</div>
            <p class="text-xs text-slate-400 mt-1">Asignadas a técnico/personal</p>
        </div>

        <!-- Card Urgentes -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-400">Urgentes Activas</span>
                <span class="p-2 rounded-xl bg-rose-500/10 text-rose-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
            </div>
            <div class="text-3xl font-black text-rose-400 mt-3">{{ $urgentRequests }}</div>
            <p class="text-xs text-slate-400 mt-1">Atención prioritaria</p>
        </div>

        <!-- Card Resueltas / Cerradas -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Resueltas/Cerradas</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="text-3xl font-black text-emerald-400 mt-3">{{ $resolvedRequests }}</div>
            <p class="text-xs text-slate-400 mt-1">Concluidas con éxito</p>
        </div>
    </div>

    <!-- QUICK ACTIONS & RECENT REQUESTS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Requests (2 columns) -->
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <h3 class="text-base font-bold text-white">Solicitudes Recientes en Bandeja</h3>
                    <p class="text-xs text-slate-400">Requerimientos ingresados desde la aplicación móvil</p>
                </div>
                <a href="{{ route('admin.requests.index') }}" class="text-xs font-semibold text-cyan-400 hover:text-cyan-300">Ver todas &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-xs font-semibold text-slate-400 uppercase border-b border-slate-800">
                            <th class="pb-3">Código</th>
                            <th class="pb-3">Título / Tipo</th>
                            <th class="pb-3">Estudiante</th>
                            <th class="pb-3">Prioridad</th>
                            <th class="pb-3">Estado</th>
                            <th class="pb-3 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($recentRequests as $req)
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 font-mono text-xs font-bold text-cyan-400">{{ $req->tracking_code }}</td>
                                <td class="py-3">
                                    <div class="font-medium text-white truncate max-w-[200px]">{{ $req->title }}</div>
                                    <div class="text-xs text-slate-400 capitalize">{{ str_replace('_', ' ', $req->category) }}</div>
                                </td>
                                <td class="py-3 text-slate-300 text-xs">
                                    {{ $req->student->name ?? 'Estudiante' }}
                                </td>
                                <td class="py-3">
                                    @switch($req->priority)
                                        @case('urgente')
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-rose-500/20 text-rose-300 border border-rose-500/30">Urgente</span>
                                            @break
                                        @case('alta')
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-orange-500/20 text-orange-300 border border-orange-500/30">Alta</span>
                                            @break
                                        @case('media')
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-blue-500/20 text-blue-300 border border-blue-500/30">Media</span>
                                            @break
                                        @default
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-slate-700 text-slate-300">Baja</span>
                                    @endswitch
                                </td>
                                <td class="py-3">
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
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-700 text-slate-300">Cerrado</span>
                                            @break
                                        @default
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400">Rechazado</span>
                                    @endswitch
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('admin.requests.show', $req->id) }}" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-white transition-colors">
                                        Atender
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500 text-sm">No hay solicitudes registradas aún.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Personal / Direct Acces Panel (1 column) -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-6">
            <div>
                <h3 class="text-base font-bold text-white">Accesos Directos Administrativos</h3>
                <p class="text-xs text-slate-400">Operaciones frecuentes del sistema</p>
            </div>

            <div class="space-y-3">
                <a href="{{ route('admin.requests.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/50 transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold">
                        📋
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white group-hover:text-cyan-400 transition-colors">Gestionar Bandeja de Entrada</div>
                        <div class="text-xs text-slate-400">Filtra, asigna técnicos y prioriza</div>
                    </div>
                </a>

                <a href="{{ route('admin.resources.create') }}" class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/50 transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold">
                        🏢
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white group-hover:text-indigo-400 transition-colors">Registrar Recurso Institucional</div>
                        <div class="text-xs text-slate-400">Aulas, laboratorios, proyectores</div>
                    </div>
                </a>

                <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/50 transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                        📊
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white group-hover:text-emerald-400 transition-colors">Ver Reportes Consolidados</div>
                        <div class="text-xs text-slate-400">Métricas de atención y rendimiento</div>
                    </div>
                </a>
            </div>

            <!-- Staff List -->
            <div class="border-t border-slate-800 pt-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Personal Encargado Activo</h4>
                    <span class="text-xs text-cyan-400 font-mono">{{ $availableStaff->count() }} registrados</span>
                </div>

                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach($availableStaff as $staff)
                        <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-slate-950/60">
                            <div class="font-medium text-slate-200">{{ $staff->name }}</div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-slate-800 text-slate-400">{{ $staff->role }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
