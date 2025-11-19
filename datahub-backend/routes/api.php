<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\AdminApprovalController;
use App\Http\Controllers\ExportController;

// Rute Publik
Route::post('/login', [AuthController::class, 'login']);

// Rute Terproteksi (Harus Login)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/export-data', [ExportController::class, 'export']);

    // Rute Khusus Participant
    Route::middleware('role:participant')->group(function () {
        Route::post('/import-data', [ImportController::class, 'store']);
    });

    // Rute Khusus Admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/pending-imports', [AdminApprovalController::class, 'getPending']);
        Route::get('/pending-imports/{import}', [AdminApprovalController::class, 'getDetail']);
        Route::post('/approve/{import}', [AdminApprovalController::class, 'approve']);
        Route::post('/reject/{import}', [AdminApprovalController::class, 'reject']);
        Route::get('/activity-logs', [AdminApprovalController::class, 'getLogs']);
    });
});
