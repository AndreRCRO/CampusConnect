<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StudentRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $requests = StudentRequest::where('student_id', $request->user()->id)
            ->with(['institutionalResource', 'mediaEvidences', 'assignedStaff:id,name,email'])
            ->latest()
            ->paginate(15);

        return response()->json($requests);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|in:mantenimiento,soporte_tecnologico,infraestructura,equipamiento,otro',
            'institutional_resource_id' => 'nullable|exists:institutional_resources,id',
            'priority' => 'nullable|string|in:baja,media,alta,urgente',
        ]);

        $trackingCode = 'REQ-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        $studentRequest = StudentRequest::create([
            'tracking_code' => $trackingCode,
            'student_id' => $request->user()->id,
            'institutional_resource_id' => $validated['institutional_resource_id'] ?? null,
            'category' => $validated['category'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? 'media',
            'status' => 'pendiente',
        ]);

        // Record initial status history
        $studentRequest->statusHistories()->create([
            'changed_by' => $request->user()->id,
            'previous_status' => 'ninguno',
            'new_status' => 'pendiente',
            'notes' => 'Solicitud registrada por el estudiante desde la aplicación móvil.',
        ]);

        return response()->json([
            'message' => 'Solicitud registrada exitosamente',
            'request' => $studentRequest->load(['institutionalResource', 'mediaEvidences']),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $studentRequest = StudentRequest::where('id', $id)
            ->where('student_id', $request->user()->id)
            ->with([
                'institutionalResource',
                'mediaEvidences',
                'comments' => fn ($query) => $query->where('is_internal', false)->with('author:id,name,role'),
                'statusHistories.changedByUser:id,name,role',
                'assignedStaff:id,name,email',
            ])
            ->firstOrFail();

        return response()->json([
            'request' => $studentRequest,
        ]);
    }

    public function tracking(Request $request, int $id): JsonResponse
    {
        $studentRequest = StudentRequest::where('id', $id)
            ->where('student_id', $request->user()->id)
            ->with([
                'statusHistories.changedByUser:id,name,role',
                'assignedStaff:id,name,email',
            ])
            ->firstOrFail();

        return response()->json([
            'tracking_code' => $studentRequest->tracking_code,
            'current_status' => $studentRequest->status,
            'priority' => $studentRequest->priority,
            'category' => $studentRequest->category,
            'assigned_staff' => $studentRequest->assignedStaff,
            'created_at' => $studentRequest->created_at,
            'resolved_at' => $studentRequest->resolved_at,
            'closed_at' => $studentRequest->closed_at,
            'timeline' => $studentRequest->statusHistories,
        ]);
    }
}
