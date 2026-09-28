<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsolidatedReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Total requests received
        $totalReceived = StudentRequest::count();

        // Pending requests
        $pendingCount = StudentRequest::where('status', 'pendiente')->count();

        // Requests according to type / category
        $byCategory = StudentRequest::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        // Requests according to priority
        $byPriority = StudentRequest::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // Closed/Resolved requests
        $closedCount = StudentRequest::whereIn('status', ['cerrado', 'resuelto'])->count();

        // Assigned responsibles breakdown
        $assignedSummary = User::whereIn('role', ['admin', 'staff'])
            ->withCount(['assignedRequests' => function ($query) {
                $query->whereIn('status', ['pendiente', 'en_proceso']);
            }])
            ->get(['id', 'name', 'email', 'role'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'active_assigned_requests_count' => $user->assigned_requests_count,
                    'total_assigned_requests_count' => StudentRequest::where('assigned_to', $user->id)->count(),
                ];
            });

        // Average resolution time calculation in hours (created_at to resolved_at or closed_at)
        $resolvedRequests = StudentRequest::whereNotNull('resolved_at')
            ->orWhereNotNull('closed_at')
            ->get();

        $averageResolutionHours = 0;
        if ($resolvedRequests->count() > 0) {
            $totalHours = $resolvedRequests->reduce(function ($carry, $request) {
                $endTime = $request->resolved_at ?? $request->closed_at;
                return $carry + $request->created_at->diffInHours($endTime);
            }, 0);
            $averageResolutionHours = round($totalHours / $resolvedRequests->count(), 2);
        }

        return response()->json([
            'report_summary' => [
                'total_solicitudes_recibidas' => $totalReceived,
                'solicitudes_pendientes' => $pendingCount,
                'solicitudes_cerradas' => $closedCount,
                'tiempo_promedio_atencion_horas' => $averageResolutionHours,
            ],
            'solicitudes_segun_tipo' => [
                'mantenimiento' => $byCategory['mantenimiento'] ?? 0,
                'soporte_tecnologico' => $byCategory['soporte_tecnologico'] ?? 0,
                'infraestructura' => $byCategory['infraestructura'] ?? 0,
                'equipamiento' => $byCategory['equipamiento'] ?? 0,
                'otro' => $byCategory['otro'] ?? 0,
            ],
            'solicitudes_segun_prioridad' => [
                'baja' => $byPriority['baja'] ?? 0,
                'media' => $byPriority['media'] ?? 0,
                'alta' => $byPriority['alta'] ?? 0,
                'urgente' => $byPriority['urgente'] ?? 0,
            ],
            'responsables_asignados' => $assignedSummary,
        ]);
    }
}
