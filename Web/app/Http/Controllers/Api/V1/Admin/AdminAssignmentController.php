<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAssignmentController extends Controller
{
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $assignedUser = User::findOrFail($validated['assigned_to']);

        if (! $assignedUser->hasAdminAccess()) {
            return response()->json([
                'message' => 'El usuario asignado debe ser personal administrativo o técnico.',
            ], 422);
        }

        $studentRequest = StudentRequest::findOrFail($id);
        $studentRequest->assigned_to = $assignedUser->id;
        $studentRequest->save();

        return response()->json([
            'message' => 'Responsable asignado exitosamente',
            'assigned_staff' => [
                'id' => $assignedUser->id,
                'name' => $assignedUser->name,
                'email' => $assignedUser->email,
                'role' => $assignedUser->role,
            ],
            'request' => $studentRequest->load('assignedStaff'),
        ]);
    }
}
