<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstitutionalResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstitutionalResourceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = InstitutionalResource::query();

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $resources = $query->latest()->get();

        return response()->json($resources);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:institutional_resources,code|max:100',
            'category' => 'required|string|in:mantenimiento,soporte_tecnologico,infraestructura,equipamiento,otro',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $resource = InstitutionalResource::create($validated);

        return response()->json([
            'message' => 'Recurso institucional registrado exitosamente',
            'resource' => $resource,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $resource = InstitutionalResource::withCount('studentRequests')->findOrFail($id);

        return response()->json([
            'resource' => $resource,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $resource = InstitutionalResource::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:100|unique:institutional_resources,code,' . $id,
            'category' => 'sometimes|required|string|in:mantenimiento,soporte_tecnologico,infraestructura,equipamiento,otro',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $resource->update($validated);

        return response()->json([
            'message' => 'Recurso institucional actualizado exitosamente',
            'resource' => $resource,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $resource = InstitutionalResource::findOrFail($id);
        $resource->delete();

        return response()->json([
            'message' => 'Recurso institucional eliminado exitosamente',
        ]);
    }
}
