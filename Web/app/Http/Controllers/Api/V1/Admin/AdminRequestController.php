<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StudentRequest::with([
            'student:id,name,email',
            'assignedStaff:id,name,email',
            'institutionalResource:id,name,code,category',
            'mediaEvidences',
        ]);

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->query('priority'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->query('assigned_to'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('tracking_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $requests = $query->latest()->paginate(20);

        return response()->json($requests);
    }

    public function show(int $id): JsonResponse
    {
        $studentRequest = StudentRequest::with([
            'student:id,name,email',
            'assignedStaff:id,name,email,role',
            'institutionalResource',
            'mediaEvidences',
            'comments.author:id,name,role',
            'statusHistories.changedByUser:id,name,role',
        ])->findOrFail($id);

        return response()->json([
            'request' => $studentRequest,
        ]);
    }
}
