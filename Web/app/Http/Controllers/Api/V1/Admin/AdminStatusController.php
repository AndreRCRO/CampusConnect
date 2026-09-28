<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminStatusController extends Controller
{
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pendiente,en_proceso,resuelto,cerrado,rechazado',
            'notes' => 'nullable|string|max:1000',
        ]);

        $studentRequest = StudentRequest::findOrFail($id);
        $previousStatus = $studentRequest->status;
        $newStatus = $validated['status'];

        $studentRequest->status = $newStatus;

        if ($newStatus === 'resuelto' && ! $studentRequest->resolved_at) {
            $studentRequest->resolved_at = now();
        }

        if ($newStatus === 'cerrado' && ! $studentRequest->closed_at) {
            $studentRequest->closed_at = now();
        }

        $studentRequest->save();

        // Create status change history trace
        $studentRequest->statusHistories()->create([
            'changed_by' => $request->user()->id,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Estado de solicitud actualizado exitosamente',
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'request' => $studentRequest->load(['statusHistories.changedByUser:id,name,role']),
        ]);
    }
}
