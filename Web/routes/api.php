<?php

use App\Http\Controllers\Api\V1\Admin\AdminAssignmentController;
use App\Http\Controllers\Api\V1\Admin\AdminCommentController;
use App\Http\Controllers\Api\V1\Admin\AdminPriorityController;
use App\Http\Controllers\Api\V1\Admin\AdminRequestController;
use App\Http\Controllers\Api\V1\Admin\AdminStatusController;
use App\Http\Controllers\Api\V1\Admin\ConsolidatedReportController;
use App\Http\Controllers\Api\V1\Admin\InstitutionalResourceController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Student\StudentCommentController;
use App\Http\Controllers\Api\V1\Student\StudentMediaController;
use App\Http\Controllers\Api\V1\Student\StudentRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CAMPUS CONNECT API Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Authentication Routes
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // ====================================================================
        // MOBILE APP ROUTES (ESTUDIANTE)
        // Utilizada por el estudiante para registrar solicitudes, adjuntar
        // evidencias multimedia, consultar seguimiento y ver/agregar comentarios.
        // ====================================================================
        Route::prefix('student')->middleware('role:student')->group(function () {
            Route::get('/requests', [StudentRequestController::class, 'index']);
            Route::post('/requests', [StudentRequestController::class, 'store']);
            Route::get('/requests/{id}', [StudentRequestController::class, 'show']);
            Route::get('/requests/{id}/tracking', [StudentRequestController::class, 'tracking']);

            Route::get('/requests/{id}/comments', [StudentCommentController::class, 'index']);
            Route::post('/requests/{id}/comments', [StudentCommentController::class, 'store']);

            Route::post('/requests/{id}/media', [StudentMediaController::class, 'store']);
        });

        // ====================================================================
        // DASHBOARD WEB ROUTES (PERSONAL ADMINISTRATIVO / STAFF)
        // Utilizada por el personal administrativo para priorizar, asignar
        // responsables, cambiar estados, gestionar recursos e generar reportes.
        // ====================================================================
        Route::prefix('admin')->middleware('role:admin,staff')->group(function () {
            Route::get('/requests', [AdminRequestController::class, 'index']);
            Route::get('/requests/{id}', [AdminRequestController::class, 'show']);
            Route::patch('/requests/{id}/priority', [AdminPriorityController::class, 'update']);
            Route::patch('/requests/{id}/assign', [AdminAssignmentController::class, 'update']);
            Route::patch('/requests/{id}/status', [AdminStatusController::class, 'update']);
            Route::post('/requests/{id}/comments', [AdminCommentController::class, 'store']);

            Route::apiResource('resources', InstitutionalResourceController::class);

            Route::get('/reports/consolidated', [ConsolidatedReportController::class, 'index']);
        });

    });
});
