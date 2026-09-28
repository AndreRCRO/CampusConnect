<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Models\MediaEvidence;
use App\Models\StudentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentMediaController extends Controller
{
    public function store(Request $request, int $requestId): JsonResponse
    {
        $studentRequest = StudentRequest::where('id', $requestId)
            ->where('student_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,pdf,mp4,doc,docx|max:20480', // max 20MB
        ]);

        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store('evidences/' . $studentRequest->id, 'public');

        $media = MediaEvidence::create([
            'student_request_id' => $studentRequest->id,
            'uploaded_by' => $request->user()->id,
            'file_path' => $path,
            'file_name' => $uploadedFile->getClientOriginalName(),
            'file_type' => $uploadedFile->getClientMimeType(),
            'file_size' => $uploadedFile->getSize(),
        ]);

        return response()->json([
            'message' => 'Evidencia multimedia adjuntada exitosamente',
            'media' => $media,
        ], 201);
    }
}
