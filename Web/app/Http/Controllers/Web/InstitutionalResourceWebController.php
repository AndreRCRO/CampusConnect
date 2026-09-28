<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InstitutionalResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutionalResourceWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = InstitutionalResource::withCount('studentRequests');

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $resources = $query->latest()->paginate(12)->withQueryString();

        return view('admin.resources.index', compact('resources'));
    }

    public function create(): View
    {
        return view('admin.resources.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:institutional_resources,code|max:100',
            'category' => 'required|string|in:mantenimiento,soporte_tecnologico,infraestructura,equipamiento,otro',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        InstitutionalResource::create($validated);

        return redirect()->route('admin.resources.index')
            ->with('success', 'Recurso institucional registrado correctamente.');
    }

    public function edit(int $id): View
    {
        $resource = InstitutionalResource::findOrFail($id);

        return view('admin.resources.edit', compact('resource'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $resource = InstitutionalResource::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:institutional_resources,code,' . $id,
            'category' => 'required|string|in:mantenimiento,soporte_tecnologico,infraestructura,equipamiento,otro',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $resource->update($validated);

        return redirect()->route('admin.resources.index')
            ->with('success', 'Recurso institucional actualizado correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $resource = InstitutionalResource::findOrFail($id);
        $resource->delete();

        return redirect()->route('admin.resources.index')
            ->with('success', 'Recurso institucional eliminado correctamente.');
    }
}
