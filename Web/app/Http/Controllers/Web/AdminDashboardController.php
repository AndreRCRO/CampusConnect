<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $totalRequests = StudentRequest::count();
        $pendingRequests = StudentRequest::where('status', 'pendiente')->count();
        $inProgressRequests = StudentRequest::where('status', 'en_proceso')->count();
        $resolvedRequests = StudentRequest::whereIn('status', ['resuelto', 'cerrado'])->count();

        $urgentRequests = StudentRequest::where('priority', 'urgente')
            ->whereNotIn('status', ['resuelto', 'cerrado'])
            ->count();

        $recentRequests = StudentRequest::with(['student', 'assignedStaff', 'institutionalResource'])
            ->latest()
            ->take(6)
            ->get();

        $availableStaff = User::whereIn('role', ['admin', 'staff'])->get();

        return view('admin.dashboard', compact(
            'totalRequests',
            'pendingRequests',
            'inProgressRequests',
            'resolvedRequests',
            'urgentRequests',
            'recentRequests',
            'availableStaff'
        ));
    }
}
