<?php

namespace App\Http\Controllers;

use App\Models\ImportMahasiswa;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; // Wajib untuk transaksi

class ParticipantController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    public function getImports(Request $request)
    {
        $imports = ImportMahasiswa::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));
        
        // PERBAIKAN: Matikan log ini karena 'view_my_documents' tidak ada di ENUM database
        // $this->activityLogService->log('view_my_documents', ...);
        
        return response()->json($imports, 200);
    }

    public function getImportDetail(ImportMahasiswa $import)
    {
        if ($import->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }
        
        // PERBAIKAN: Matikan log ini karena 'view_document_detail' tidak ada di ENUM database
        // $this->activityLogService->log('view_document_detail', ...);

        return response()->json(['data' => $import], 200);
    }

    public function storeImport(Request $request)
    {
        // Validasi
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:20480',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $filePath = null;

        // Mulai Transaksi Database
        DB::beginTransaction();

        try {
            $file = $request->file('file');            
            $fileName = time() . '_' . $file->getClientOriginalName();
            $storagePath = 'imports/' . Auth::id();
            
            // 1. Simpan File Fisik
            $filePath = $file->storeAs($storagePath, $fileName);             
            
            // 2. Hitung Baris
            $ext = strtolower($file->getClientOriginalExtension());
            $rowCount = $this->countRows($file->getRealPath(), $ext);
            
            // 3. Simpan ke Database Import
            ImportMahasiswa::create([
                'user_id' => Auth::id(),
                'status' => 'uploaded',
                'file_name' => $fileName,
                'file_path' => $filePath,
                'total_rows' => $rowCount,
                'admin_notes' => 'Menunggu review Admin.'
            ]);
            
            // 4. Log Activity
            // PERBAIKAN PENTING: Ganti 'import_document' menjadi 'import_data'
            // agar sesuai dengan ENUM di database (create_enum_types.php)
            $this->activityLogService->log(
                'import_data', 
                "User uploaded new document '{$fileName}' ({$rowCount} rows).", 
                Auth::id(), 
                $request
            );

            // Simpan perubahan
            DB::commit();

            return response()->json([
                'message' => 'Dokumen berhasil diupload.',
                'rows_counted' => $rowCount,
            ], 201);

        } catch (\Exception $e) {
            // Batalkan perubahan jika error
            DB::rollBack();

            // Hapus file fisik
            if ($filePath && Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
            
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function countRows($filePath, $extension)
    {
        try {
            if (in_array($extension, ['csv', 'txt'])) {
                if (!file_exists($filePath)) return 0;
                $handle = fopen($filePath, 'r');
                $rowCount = 0;
                while (($row = fgetcsv($handle)) !== false) {
                    if (array_filter($row)) $rowCount++;
                }
                fclose($handle);
                return max(0, $rowCount - 1);
            } 
            
            if (in_array($extension, ['xlsx', 'xls'])) {
                $spreadsheet = IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                return max(0, $worksheet->getHighestRow() - 1);
            }
        } catch (\Exception $e) {
            return 0;
        }
        return 0;
    }
}