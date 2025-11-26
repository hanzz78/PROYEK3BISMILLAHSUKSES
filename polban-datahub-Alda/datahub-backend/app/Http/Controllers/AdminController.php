<?php

namespace App\Http\Controllers;

use App\Models\ImportMahasiswa;
use App\Models\Mahasiswa;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// Kita gunakan mode "Native PHP" agar tidak tergantung library Excel yang sering bermasalah
// use PhpOffice\PhpSpreadsheet\IOFactory; 

class AdminController extends Controller
{
    protected $activityLogService;
    
    const VALID_STATUSES = [
        'uploaded', 'reviewed', 'rejected', 'approved', 'in_process', 'visualizing', 'completed'
    ];

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    public function getImports(Request $request) {
        $query = ImportMahasiswa::with('user')->orderBy('created_at', 'desc');
        if ($request->has('status') && in_array($request->status, self::VALID_STATUSES)) {
             $query->where('status', $request->status);
        }
        return response()->json($query->paginate($request->input('per_page', 10)), 200);
    }

    public function getImportDetail(ImportMahasiswa $import) {
        $import->load('user');        
        return response()->json(['data' => $import], 200);
    }

    // --- UPDATE STATUS (MAIN LOGIC) ---
    public function updateStatus(Request $request, ImportMahasiswa $import)
    {
        // 1. Cek Status Final
        if (in_array($import->status, ['approved', 'rejected', 'completed', 'in_process'])) {
            return response()->json(['message' => 'Dokumen sudah diproses final.'], 403);
        }

        // 2. Validasi
        $request->validate([
            'new_status' => ['required', 'string', Rule::in(self::VALID_STATUSES)],
            'notes' => 'nullable|string|max:500', 
        ]);
        
        $newStatus = $request->input('new_status');
        $notes = $request->input('notes');

        if ($newStatus === 'rejected' && empty($notes)) {
            return response()->json(['message' => 'Catatan penolakan wajib diisi.'], 422);
        }

        DB::beginTransaction();

        try {
            // JIKA APPROVED -> JALANKAN IMPORT
            if ($newStatus === 'approved') {
                $this->importDataToDatabase($import);
            }

            // Update Status
            $import->status = $newStatus;
            if ($notes || $newStatus === 'rejected') {
                $import->admin_notes = $notes;
            }
            $import->save();

            // Log Activity
            $logAction = ($newStatus === 'approved') ? 'approve_data' : (($newStatus === 'rejected') ? 'reject_data' : 'update_user');
            $this->activityLogService->log($logAction, "Admin updated status #{$import->import_id} to {$newStatus}", Auth::id(), $request);

            DB::commit();

            return response()->json(['message' => 'Status berhasil diperbarui.', 'data' => $import], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memproses: ' . $e->getMessage()], 500);
        }
    }

    // --- FUNGSI IMPORT (VERSI LEBIH KUAT) ---
    private function importDataToDatabase(ImportMahasiswa $import)
    {
        // Gunakan Storage::path agar kompatibel dengan Windows/Linux
        if (!Storage::exists($import->file_path)) {
            throw new \Exception("File fisik tidak ditemukan di server. Kemungkinan file korup atau terhapus.");
        }
        $fullPath = Storage::path($import->file_path);

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        // Hanya Izinkan CSV/TXT (Paling Stabil)
        if ($ext !== 'csv' && $ext !== 'txt') {
            throw new \Exception("Sistem saat ini hanya mendukung import file CSV. Mohon upload ulang dengan format .csv");
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            throw new \Exception("Gagal membuka file CSV.");
        }

        // Deteksi Pemisah (Koma atau Titik Koma)
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

        $rowIndex = 0;
        while (($row = fgetcsv($handle, 10000, $delimiter)) !== false) {
            $rowIndex++;
            
            // Skip Header & Baris Kosong
            if ($rowIndex === 1) continue; 
            if (empty(array_filter($row)) || count($row) < 2) continue;

            try {
                // Mapping Data (Gunakan isset agar aman)
                // Asumsi CSV: [0]No, [1]Kelas, [2]Angkatan, [3]TglLahir, [4]JK, [5]Agama, [6]KodePos
                
                // Format Tanggal
                $tglLahir = null;
                if (isset($row[3]) && !empty($row[3])) {
                    $cleanDate = str_replace('/', '-', $row[3]); // Ubah 17/08/2023 jadi 17-08-2023
                    $time = strtotime($cleanDate);
                    if ($time) $tglLahir = date('Y-m-d', $time);
                }

                // Format JK
                $jk = isset($row[4]) ? strtoupper(substr(trim($row[4]), 0, 1)) : null;

                Mahasiswa::create([
                    'import_id' => $import->import_id,
                    'user_id_importer' => $import->user_id,
                    'user_id_approver' => Auth::id(),

                    'kelas'           => isset($row[1]) ? trim($row[1]) : null, 
                    'angkatan'        => isset($row[2]) ? (int)$row[2] : null,
                    'tgl_lahir'       => $tglLahir,
                    'jenis_kelamin'   => $jk, 
                    'agama'           => isset($row[5]) ? trim($row[5]) : null,
                    'kode_pos'        => isset($row[6]) ? trim($row[6]) : null,
                ]);

            } catch (\Exception $e) {
                fclose($handle);
                throw new \Exception("Error pada baris ke-{$rowIndex}: " . $e->getMessage());
            }
        }
        fclose($handle);
    }

    // ... (Sisanya Tetap) ...
    public function getLogs(Request $request) {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');
        if ($request->has('action') && $request->action != '') $query->where('action', $request->action);
        return response()->json($query->paginate($request->input('per_page', 50)), 200);
    }

    public function downloadOriginalFile(ImportMahasiswa $import) {
        if (!Storage::exists($import->file_path)) return response()->json(['message' => 'File tidak ditemukan.'], 404);
        $this->activityLogService->log('export_data', "Admin downloaded file", Auth::id(), request());
        return Storage::download($import->file_path, $import->file_name);
    }
}