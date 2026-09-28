@extends('layouts.admin')

@section('title', 'Reportes Consolidados')
@section('page_header', 'Reportes & Estadísticas Consolidadas')
@section('page_subheader', 'Indicadores de desempeño institucionales, tiempos de atención y distribución de solicitudes')

@section('content')
<div class="space-y-6">
    <!-- METRICS SUMMARY CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric Total -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 relative overflow-hidden">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Solicitudes Recibidas</div>
            <div class="text-3xl font-black text-white mt-2">{{ $totalReceived }}</div>
            <div class="text-xs text-slate-500 mt-1">Registradas por todos los canales</div>
        </div>

        <!-- Metric Pendientes -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 relative overflow-hidden">
            <div class="text-xs font-semibold uppercase tracking-wider text-amber-400">Solicitudes Pendientes</div>
            <div class="text-3xl font-black text-amber-400 mt-2">{{ $pendingCount }}</div>
            <div class="text-xs text-slate-500 mt-1">Esperando primera atención</div>
        </div>

        <!-- Metric Cerradas/Resueltas -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 relative overflow-hidden">
            <div class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Solicitudes Concluidas</div>
            <div class="text-3xl font-black text-emerald-400 mt-2">{{ $closedCount }}</div>
            <div class="text-xs text-slate-500 mt-1">Resueltas y cerradas formalmente</div>
        </div>

        <!-- Metric Tiempo Promedio -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 relative overflow-hidden">
            <div class="text-xs font-semibold uppercase tracking-wider text-cyan-400">Tiempo Promedio de Atención</div>
            <div class="text-3xl font-black text-cyan-400 mt-2">{{ $avgHours }} <span class="text-sm font-normal text-slate-400">hrs</span></div>
            <div class="text-xs text-slate-500 mt-1">Aproximadamente {{ $avgDays }} días desde registro</div>
        </div>
    </div>

    <!-- TWO COLUMN DETAILED BREAKDOWNS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- SOLICITUDES POR TIPO / CATEGORIA -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Solicitudes según Tipo de Servicio</h3>
                <span class="text-xs text-slate-400">Categorías Institucionales</span>
            </div>

            <div class="space-y-3">
                @php
                    $categories = [
                        'mantenimiento' => ['name' => 'Mantenimiento General', 'color' => 'bg-amber-500', 'textColor' => 'text-amber-400'],
                        'soporte_tecnologico' => ['name' => 'Soporte Tecnológico', 'color' => 'bg-cyan-500', 'textColor' => 'text-cyan-400'],
                        'infraestructura' => ['name' => 'Infraestructura & Aulas', 'color' => 'bg-indigo-500', 'textColor' => 'text-indigo-400'],
                        'equipamiento' => ['name' => 'Equipamiento & Proyectores', 'color' => 'bg-emerald-500', 'textColor' => 'text-emerald-400'],
                        'otro' => ['name' => 'Otros Servicios', 'color' => 'bg-slate-500', 'textColor' => 'text-slate-400'],
                    ];
                @endphp

                @foreach($categories as $key => $meta)
                    @php
                        $count = $byCategory[$key] ?? 0;
                        $pct = $totalReceived > 0 ? round(($count / $totalReceived) * 100, 1) : 0;
                    @endphp
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span class="text-slate-200">{{ $meta['name'] }}</span>
                            <span class="{{ $meta['textColor'] }} font-mono">{{ $count }} ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full h-2.5 bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                            <div class="h-full {{ $meta['color'] }} rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- SOLICITUDES POR PRIORIDAD -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Solicitudes según Prioridad</h3>
                <span class="text-xs text-slate-400">Nivel de Impacto</span>
            </div>

            <div class="space-y-3">
                @php
                    $priorities = [
                        'urgente' => ['name' => '⚡ Urgente', 'color' => 'bg-rose-500', 'textColor' => 'text-rose-400'],
                        'alta' => ['name' => '🔥 Alta', 'color' => 'bg-orange-500', 'textColor' => 'text-orange-400'],
                        'media' => ['name' => '🔷 Media', 'color' => 'bg-blue-500', 'textColor' => 'text-blue-400'],
                        'baja' => ['name' => '🔹 Baja', 'color' => 'bg-slate-600', 'textColor' => 'text-slate-400'],
                    ];
                @endphp

                @foreach($priorities as $key => $meta)
                    @php
                        $count = $byPriority[$key] ?? 0;
                        $pct = $totalReceived > 0 ? round(($count / $totalReceived) * 100, 1) : 0;
                    @endphp
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span class="text-slate-200">{{ $meta['name'] }}</span>
                            <span class="{{ $meta['textColor'] }} font-mono">{{ $count }} ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full h-2.5 bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                            <div class="h-full {{ $meta['color'] }} rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ASSIGNED RESPONSIBLES TABLE -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div>
                <h3 class="text-base font-bold text-white">Desglose de Responsables Asignados</h3>
                <p class="text-xs text-slate-400">Distribución de carga de trabajo entre el personal de atención y técnicos</p>
            </div>
            <span class="text-xs text-cyan-400 font-mono">{{ $staffMembers->count() }} Encargados</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-950/80 text-xs uppercase font-semibold text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Nombre del Personal</th>
                        <th class="px-4 py-3">Correo Electrónico</th>
                        <th class="px-4 py-3">Rol</th>
                        <th class="px-4 py-3 text-center">Solicitudes Activas</th>
                        <th class="px-4 py-3 text-center">Total Historico Asignado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($staffMembers as $staff)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-4 py-3 font-semibold text-white">👤 {{ $staff->name }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ $staff->email }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-slate-800 text-slate-300">
                                    {{ $staff->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    {{ $staff->active_count }} activas
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-bold text-cyan-400">
                                {{ $staff->total_count }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500 text-xs">No hay usuarios de personal administrativo registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
