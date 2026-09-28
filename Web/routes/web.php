<?php

use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\AdminRequestWebController;
use App\Http\Controllers\Web\Auth\WebAuthController;
use App\Http\Controllers\Web\ConsolidatedReportWebController;
use App\Http\Controllers\Web\InstitutionalResourceWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CAMPUS CONNECT Web Routes (Dashboard Administrativo)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Authentication Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('admin.logout');
});

// Protected Administrative Web Routes
Route::prefix('admin')->middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Solicitudes estudiantiles
    Route::get('/requests', [AdminRequestWebController::class, 'index'])->name('admin.requests.index');
    Route::get('/requests/{id}', [AdminRequestWebController::class, 'show'])->name('admin.requests.show');
    Route::post('/requests/{id}/status', [AdminRequestWebController::class, 'updateStatus'])->name('admin.requests.update_status');
    Route::post('/requests/{id}/assign', [AdminRequestWebController::class, 'assignStaff'])->name('admin.requests.assign_staff');
    Route::post('/requests/{id}/priority', [AdminRequestWebController::class, 'updatePriority'])->name('admin.requests.update_priority');
    Route::post('/requests/{id}/comments', [AdminRequestWebController::class, 'storeComment'])->name('admin.requests.store_comment');

    // Recursos institucionales (Aulas, laboratorios, equipos)
    Route::resource('resources', InstitutionalResourceWebController::class)->names('admin.resources');

    // Reportes y estadísticas consolidadas
    Route::get('/reports', [ConsolidatedReportWebController::class, 'index'])->name('admin.reports.index');
});
