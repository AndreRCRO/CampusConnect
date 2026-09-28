<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Models\RequestComment;
use App\Models\StudentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentCommentController extends Controller
{
    public function index(Request $request, int $requestId): JsonResponse
    {
        $studentRequest = StudentRequest::where('id', $requestId)
            ->where('student_id', $request->user()->id)
            ->firstOrFail();

        $comments = RequestComment::where('student_request_id', $studentRequest->id)
            ->where('is_internal', false)
            ->with('author:id,name,role')
            ->oldest()
            ->get();

        return response()->json($comments);
    }

    public function store(Request $request, int $requestId): JsonResponse
    {
        $studentRequest = StudentRequest::where('id', $requestId)
            ->where('student_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $comment = RequestComment::create([
            'student_request_id' => $studentRequest->id,
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
            'is_internal' => false,
        ]);

        return response()->json([
            'message' => 'Comentario registrado exitosamente',
            'comment' => $comment->load('author:id,name,role'),
        ], 201);
    }
}
