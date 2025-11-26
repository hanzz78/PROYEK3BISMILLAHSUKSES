<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataController; 
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ExportController;

// --- 1. Rute Publik ---
// PERBAIKAN: Tambahkan ->name('login') di akhir
Route::post('/login', [AuthController::class, 'login'])->name('login');

// --- 2. Rute Terproteksi (Harus Login) ---
Route::middleware('auth:sanctum')->group(function () 
{    
    // Auth & User Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) { return $request->user(); });

    // Role: DataCore
    Route::middleware('role:datacore')->group(function () {
        Route::get('/datacore/mahasiswa', [DataController::class, 'dataMahasiswa']);
    });

    // --- Rute Khusus Participant ---
    Route::middleware('role:participant')->prefix('participant')->group(function () {
        Route::post('/upload', [ParticipantController::class, 'storeImport']);
        Route::get('/dokumen', [ParticipantController::class, 'getImports']);
        Route::get('/dokumen/{import}', [ParticipantController::class, 'getImportDetail']);
    });

    // --- Rute Khusus Admin ---
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dokumen', [AdminController::class, 'getImports']);
        Route::get('/dokumen/{import}', [AdminController::class, 'getImportDetail']);
        Route::post('/dokumen/{import}/status', [AdminController::class, 'updateStatus']); 
        Route::get('/logs', [AdminController::class, 'getLogs']);
        Route::get('/dokumen/{import}/download', [AdminController::class, 'downloadOriginalFile']);
    });
});