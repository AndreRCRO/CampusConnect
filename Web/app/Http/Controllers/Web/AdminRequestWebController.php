<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RequestComment;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRequestWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = StudentRequest::with([
            'student',
            'assignedStaff',
            'institutionalResource',
            'mediaEvidences',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->query('priority'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->query('assigned_to'));
        }

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('tracking_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $requests = $query->latest()->paginate(15)->withQueryString();
        $availableStaff = User::whereIn('role', ['admin', 'staff'])->get();

        return view('admin.requests.index', compact('requests', 'availableStaff'));
    }

    public function show(int $id): View
    {
        $studentRequest = StudentRequest::with([
            'student',
            'assignedStaff',
            'institutionalResource',
            'mediaEvidences.uploader',
            'comments.author',
            'statusHistories.changedByUser',
        ])->findOrFail($id);

        $availableStaff = User::whereIn('role', ['admin', 'staff'])->get();

        return view('admin.requests.show', compact('studentRequest', 'availableStaff'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
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

        // Record trace in status history
        $studentRequest->statusHistories()->create([
            'changed_by' => $request->user()->id,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'notes' => $validated['notes'] ?? 'Cambio de estado desde el panel web administrativo.',
        ]);

        return back()->with('success', "Estado actualizado de '{$previousStatus}' a '{$newStatus}' correctamente.");
    }

    public function assignStaff(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $assignedUser = User::findOrFail($validated['assigned_to']);

        if (! $assignedUser->hasAdminAccess()) {
            return back()->withErrors(['assigned_to' => 'El responsable asignado debe ser parte del personal administrativo o técnico.']);
        }

        $studentRequest = StudentRequest::findOrFail($id);
        $studentRequest->assigned_to = $assignedUser->id;
        
        // If assigned while pending, update status to en_proceso automatically if desired
        if ($studentRequest->status === 'pendiente') {
            $studentRequest->status = 'en_proceso';
            $studentRequest->statusHistories()->create([
                'changed_by' => $request->user()->id,
                'previous_status' => 'pendiente',
                'new_status' => 'en_proceso',
                'notes' => "Asignado automáticamente al responsable {$assignedUser->name}.",
            ]);
        }
        
        $studentRequest->save();

        return back()->with('success', "Responsable asignado a {$assignedUser->name} correctamente.");
    }

    public function updatePriority(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'priority' => 'required|string|in:baja,media,alta,urgente',
        ]);

        $studentRequest = StudentRequest::findOrFail($id);
        $oldPriority = $studentRequest->priority;
        $studentRequest->priority = $validated['priority'];
        $studentRequest->save();

        return back()->with('success', "Prioridad modificada de '{$oldPriority}' a '{$validated['priority']}'.");
    }

    public function storeComment(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
            'is_internal' => 'nullable|boolean',
        ]);

        $studentRequest = StudentRequest::findOrFail($id);

        RequestComment::create([
            'student_request_id' => $studentRequest->id,
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
            'is_internal' => $request->boolean('is_internal'),
        ]);

        return back()->with('success', 'Comentario registrado exitosamente en la bitácora.');
    }
}
