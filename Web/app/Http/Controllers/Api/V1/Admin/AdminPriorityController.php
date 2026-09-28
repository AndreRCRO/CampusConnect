<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPriorityController extends Controller
{
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'priority' => 'required|string|in:baja,media,alta,urgente',
        ]);

        $studentRequest = StudentRequest::findOrFail($id);
        $oldPriority = $studentRequest->priority;
        $studentRequest->priority = $validated['priority'];
        $studentRequest->save();

        return response()->json([
            'message' => 'Prioridad actualizada exitosamente',
            'previous_priority' => $oldPriority,
            'new_priority' => $studentRequest->priority,
            'request' => $studentRequest,
        ]);
    }
}
