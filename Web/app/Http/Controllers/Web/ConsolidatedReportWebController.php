<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StudentRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConsolidatedReportWebController extends Controller
{
    public function index(): View
    {
        $totalReceived = StudentRequest::count();
        $pendingCount = StudentRequest::where('status', 'pendiente')->count();
        $inProgressCount = StudentRequest::where('status', 'en_proceso')->count();
        $closedCount = StudentRequest::whereIn('status', ['cerrado', 'resuelto'])->count();

        // Requests grouped by type (category)
        $byCategory = StudentRequest::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        // Requests grouped by priority
        $byPriority = StudentRequest::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // Average resolution time in hours and days
        $resolvedRequests = StudentRequest::whereNotNull('resolved_at')
            ->orWhereNotNull('closed_at')
            ->get();

        $avgHours = 0;
        $avgDays = 0;

        if ($resolvedRequests->count() > 0) {
            $totalHours = $resolvedRequests->reduce(function ($carry, $request) {
                $endTime = $request->resolved_at ?? $request->closed_at;
                return $carry + $request->created_at->diffInHours($endTime);
            }, 0);

            $avgHours = round($totalHours / $resolvedRequests->count(), 1);
            $avgDays = round($avgHours / 24, 1);
        }

        // Assigned staff summary
        $staffMembers = User::whereIn('role', ['admin', 'staff'])
            ->withCount([
                'assignedRequests as active_count' => function ($q) {
                    $q->whereIn('status', ['pendiente', 'en_proceso']);
                },
                'assignedRequests as total_count',
            ])
            ->get();

        return view('admin.reports.index', compact(
            'totalReceived',
            'pendingCount',
            'inProgressCount',
            'closedCount',
            'byCategory',
            'byPriority',
            'avgHours',
            'avgDays',
            'staffMembers'
        ));
    }
}
