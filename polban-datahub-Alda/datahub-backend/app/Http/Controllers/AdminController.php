<?php

namespace App\Http\Controllers;

use App\Models\ImportMahasiswa;
use App\Models\Mahasiswa;
use App\Models\Slta;
use App\Models\JalurDaftar;
use App\Models\KabupatenKota;
use App\Models\Provinsi;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class AdminController extends Controller
{
    protected $activityLogService;
    const VALID_STATUSES = [
        'uploaded',     // 1. Baru diupload, menunggu Admin Review (Status default)
        'reviewed',     // 2. Admin sudah melihat detail dan siap mengambil keputusan
        'rejected',     // 3. Ditolak oleh Admin
        'approved',     // 4. Disetujui (Siap diproses oleh sistem/database)
        'in_process',   // 5. Sedang diproses oleh sistem ke tabel Mahasiswa
        'visualizing',  // 6. Data sudah masuk DB, sedang dibuat visualisasi
        'completed'     // 7. Proses selesai, visualisasi siap
    ];

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    public function getImports(Request $request)
    {
        $query = ImportMahasiswa::with('user')
            ->orderBy('created_at', 'desc');
        // Filter by status (optional)
        if ($request->has('status') && in_array($request->status, self::VALID_STATUSES)) 
        {
             $query->where('status', $request->status);
        } else {
             // Default: tampilkan dokumen yang masih perlu aksi admin (uploaded, reviewed)
             $query->whereIn('status', ['uploaded', 'reviewed', 'approved']);
        }
        $imports = $query->paginate($request->input('per_page', 10));
        $this->activityLogService->log('view_imports', 'Admin viewed imports list', Auth::id(), $request);

        return response()->json($imports, 200);
    }

    public function getImportDetail(ImportMahasiswa $import)
    {
        $import->load('user');        
        // Log: Admin melihat detail dokumen
        $this->activityLogService->log(
            'view_document_detail',
            "Admin viewed document detail #{$import->id}",
            Auth::id(),
            request()
        );
        return response()->json(['data' => $import,], 200);
    }

    public function updateStatus(Request $request, ImportMahasiswa $import)
    {
        $request->validate([
            'new_status' => ['required', 'string', Rule::in(self::VALID_STATUSES)],
            'notes' => 'nullable|string|max:500', 
        ]);
        
        $oldStatus = $import->status;
        $newStatus = $request->input('new_status');
        $notes = $request->input('notes');

        // Validasi Alasan Penolakan
        if ($newStatus === 'rejected' && empty($notes)) 
        {
            return response()->json(['message' => 'Catatan penolakan wajib diisi.'], 422);
        }

        // Contoh Guard: Admin tidak boleh langsung melompat ke 'completed'
        if ($newStatus === 'completed' && $oldStatus !== 'visualizing') {
             return response()->json(['message' => 'Status completed hanya bisa dari visualizing.'], 400);
        }
        
        try {
            // Logika Status Otomatis:
            // Jika Admin mengubah ke status 'approved', dia hanya perlu klik tombol, 
            // dan sistem worker akan mengubahnya ke 'in_process' (untuk contoh ini, kita ubah manual)
            
            $import->status = $newStatus;
            
            // Perbarui notes hanya jika ada input baru atau jika itu penolakan
            if ($notes || $newStatus === 'rejected') 
            {
                $import->admin_notes = $notes;
            }

            $import->save();

            // Log activity
            $logDescription = "Admin updated status document #{$import->id} from '{$oldStatus}' to '{$newStatus}'.";
            if ($newStatus === 'rejected') 
            {
                $logDescription .= " Notes: " . $notes;
            }

            $this->activityLogService->log(
                "status_update_{$newStatus}", // Contoh action log: status_update_approved
                $logDescription,
                Auth::id(),
                $request
            );

            return response()->json(['message' => 'Status dokumen berhasil diperbarui','data' => $import,], 200);
        } catch (\Exception $e) 
        {
            return response()->json([
                'message' => 'Update status failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getLogs(Request $request)
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        $perPage = $request->input('per_page', 50);
        $logs = $query->paginate($perPage);

        return response()->json($logs, 200);
    }

    public function downloadOriginalFile(ImportMahasiswa $import)
    {
        if (!Storage::exists($import->file_path)) 
        {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        // Log download
        $this->activityLogService->log(
            'download_file',
            "Admin downloaded original file '{$import->file_name}' for document #{$import->id}",
            Auth::id(),
            request()
        );

        return Storage::download($import->file_path, $import->file_name);
    }

    // public function getPending()
    // {
    //     $imports = ImportMahasiswa::with('user')
    //         ->where('status', 'pending')
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return response()->json([
    //         'data' => $imports,
    //     ], 200);
    // }

    // public function getDetail(ImportMahasiswa $import)
    // {
    //     $import->load('user');

    //     return response()->json([
    //         'data' => $import,
    //     ], 200);
    // }

    // public function approve(Request $request, ImportMahasiswa $import)
    // {
    //     if ($import->status !== 'pending') {
    //         return response()->json([
    //             'message' => 'Import already processed',
    //         ], 400);
    //     }

    //     try {
    //         $slta_id = null;
    //         if ($import->nama_slta_raw) {
    //             $slta_id = Slta::where('nama_slta_resmi', $import->nama_slta_raw)->value('id');
    //         }

    //         $jalur_id = null;
    //         if ($import->nama_jalur_daftar_raw) {
    //             $jalur_id = JalurDaftar::where('nama_jalur_daftar', $import->nama_jalur_daftar_raw)->value('id');
    //         }

    //         $kabupaten_id = null;
    //         $provinsi_id = null;

    //         if ($import->provinsi_raw) {
    //             $provinsi_id = Provinsi::where('nama_provinsi', $import->provinsi_raw)->value('id');

    //             if ($provinsi_id && $import->nama_wilayah_raw) {
    //                 $kabupaten_id = KabupatenKota::where('nama_kabupaten_kota', $import->nama_wilayah_raw)
    //                     ->where('id_provinsi', $provinsi_id)
    //                     ->value('id');
    //             }
    //         }

    //         $mahasiswa = Mahasiswa::create([
    //             'import_id' => $import->id,
    //             'user_id_importer' => $import->user_id,
    //             'user_id_approver' => Auth::id(),
    //             'kelas' => $import->kelas,
    //             'angkatan' => $import->angkatan,
    //             'tgl_lahir' => $import->tgl_lahir,
    //             'jenis_kelamin' => $import->jenis_kelamin,
    //             'agama' => $import->agama,
    //             'kode_pos' => $import->kode_pos,
    //             'id_slta' => $slta_id,
    //             'id_jalur_daftar' => $jalur_id,
    //             'id_kabupaten_kota' => $kabupaten_id,
    //         ]);

    //         $import->status = 'approved';
    //         $import->save();

    //         $this->activityLogService->log(
    //             'approve_data',
    //             "Admin approved import data #" . $import->id,
    //             Auth::id(),
    //             $request
    //         );

    //         return response()->json([
    //             'message' => 'Data approved successfully',
    //             'mahasiswa' => $mahasiswa,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Approval failed',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function reject(Request $request, ImportMahasiswa $import)
    // {
    //     if ($import->status !== 'pending') {
    //         return response()->json([
    //             'message' => 'Import already processed',
    //         ], 400);
    //     }

    //     $request->validate([
    //         'notes' => 'required|string|max:500',
    //     ]);

    //     try {
    //         $import->status = 'rejected';
    //         $import->admin_notes = $request->input('notes');
    //         $import->save();

    //         $this->activityLogService->log(
    //             'reject_data',
    //             "Admin rejected import data #" . $import->id,
    //             Auth::id(),
    //             $request
    //         );

    //         return response()->json([
    //             'message' => 'Data rejected successfully',
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Rejection failed',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function getLogs(Request $request)
    // {
    //     $query = ActivityLog::with('user')
    //         ->orderBy('created_at', 'desc');

    //     if ($request->has('action')) {
    //         $query->where('action', $request->action);
    //     }

    //     if ($request->has('user_id')) {
    //         $query->where('user_id', $request->user_id);
    //     }

    //     $perPage = $request->input('per_page', 50);
    //     $logs = $query->paginate($perPage);

    //     return response()->json($logs, 200);
    // }
}