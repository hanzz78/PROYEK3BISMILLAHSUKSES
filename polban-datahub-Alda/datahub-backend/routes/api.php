<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataController; 
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ExportController;


// --- 1. Rute Publik ---
Route::post('/login', [AuthController::class, 'login']);
// Route::get('/data/mahasiswa', [DataController::class, 'dataMahasiswa']); 

// --- 2. Rute Terproteksi (Harus Login) ---
Route::middleware('auth:sanctum')->group(function () 
{    
    // Auth & User Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {return $request->user();});

    // Fitur Export (Bisa diakses user login)
    //Route::get('/export-data', [ExportController::class, 'export']);
    Route::middleware('role:datacore')->group(function () {
        Route::get('/datacore/mahasiswa', [DataController::class, 'dataMahasiswa']);
    });

    // --- Rute Khusus Participant (Import Data) ---
    Route::middleware('role:participant')->group(function () {
        //Route::post('/import-data', [ImportController::class, 'store']);
        // Import Dokumen
        Route::post('/upload', [ParticipantController::class, 'storeImport']);
        // Melihat Status Dokumen (Miliknya sendiri)
        Route::get('/dokumen', [ParticipantController::class, 'getImports']);
        Route::get('/dokumen/{import}', [ParticipantController::class, 'getImportDetail']);
    });

    // --- Rute Khusus Admin (Approval & Logs) ---
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Route::get('/pending-imports', [AdminController::class, 'getPending']);
        // Route::get('/pending-imports/{import}', [AdminController::class, 'getDetail']);
        // Route::post('/approve/{import}', [AdminController::class, 'approve']);
        // Route::post('/reject/{import}', [AdminController::class, 'reject']);
        //Route::get('/activity-logs', [AdminController::class, 'getLogs']);
        // Review dan Manajemen Dokumen Import
        Route::get('/dokumen', [AdminController::class, 'getImports']); // List semua dokumen
        Route::get('/dokumen/{import}', [AdminController::class, 'getImportDetail']);
        // Aksi Admin: Mengubah status dokumen
        Route::post('/dokumen/{import}/status', [AdminController::class, 'updateStatus']); 
        // Fitur Logs
        Route::get('/logs', [AdminController::class, 'getLogs']);
        // Fitur Download File Asli
        Route::get('/dokumen/{import}/download', [AdminController::class, 'downloadOriginalFile']);
    });
});