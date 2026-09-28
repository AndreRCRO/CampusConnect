<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequestComment;
use App\Models\StudentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCommentController extends Controller
{
    public function store(Request $request, int $requestId): JsonResponse
    {
        $studentRequest = StudentRequest::findOrFail($requestId);

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
            'is_internal' => 'nullable|boolean',
        ]);

        $comment = RequestComment::create([
            'student_request_id' => $studentRequest->id,
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        return response()->json([
            'message' => 'Comentario registrado exitosamente por personal administrativo',
            'comment' => $comment->load('author:id,name,role'),
        ], 201);
    }
}
