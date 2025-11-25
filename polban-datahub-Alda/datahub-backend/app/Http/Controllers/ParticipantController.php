<?php

namespace App\Http\Controllers;

use App\Models\ImportMahasiswa;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
        $this->activityLogService->log(
            'view_my_documents',
            'Participant viewed their uploaded documents list.',
            Auth::id(),
            $request);
        return response()->json($imports, 200);
    }

    public function getImportDetail(ImportMahasiswa $import)
    {
        if ($import->user_id !== Auth::id()) 
        {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }
        $this->activityLogService->log(
            'view_document_detail',
            "Participant viewed their document detail #{$import->id}",
            Auth::id(),
            request());
        return response()->json(['data' => $import,], 200);
    }

    public function storeImport(Request $request)
    {
        $validator = Validator::make($request->all(), ['file' => 'required|file|mimes:csv,xlsx,xls|max:10240',]);

        if ($validator->fails()) 
        {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try 
        {
            $file = $request->file('file');            
            // 1. Simpan file asli ke storage
            $fileName = time() . '_' . $file->getClientOriginalName();
            // Gunakan path yang spesifik per user agar lebih terorganisir
            $storagePath = 'imports/' . Auth::id();
            $filePath = $file->storeAs($storagePath, $fileName);             
            // 2. Hitung jumlah baris (untuk informasi Admin/Participant)
            $rowCount = $this->countRows($file->getRealPath(), $file->getClientOriginalExtension());
            
            // 3. Buat satu record ImportMahasiswa untuk merepresentasikan DOKUMEN ini
            ImportMahasiswa::create([
                'user_id' => Auth::id(),
                'status' => 'uploaded', // Status awal: menunggu review Admin
                'file_name' => $fileName,
                'file_path' => $filePath,
                'total_rows' => $rowCount,
                'admin_notes' => 'Menunggu review Admin.'
            ]);
            
            // 4. Log Activity
            $this->activityLogService->log(
                'import_document',
                "User uploaded new document '{$fileName}' ({$rowCount} rows).",
                Auth::id(),
                $request
            );

            return response()->json(['message' => 'Dokumen berhasil diupload dan menunggu review Admin.','rows_counted' => $rowCount,], 201);

        } catch (\Exception $e) 
        {
            // Jika ada error, hapus file yang mungkin sudah tersimpan
            if (isset($filePath) && Storage::exists($filePath)) 
            {
                Storage::delete($filePath);
            }
            return response()->json(['message' => 'Import failed','error' => $e->getMessage(),], 500);
        }
    }

    private function countRows($filePath, $extension)
    {
        if (in_array($extension, ['csv', 'txt'])) 
        {
            $handle = fopen($filePath, 'r');
            $rowCount = 0;
            // Hitung semua baris yang tidak kosong
            while (($row = fgetcsv($handle)) !== false) 
            {
                if (empty(array_filter($row))) continue;
                $rowCount++;
            }
            fclose($handle);
            // Kurangi 1 untuk header
            return $rowCount > 0 ? $rowCount - 1 : 0;
        } 
        
        if (in_array($extension, ['xlsx', 'xls'])) 
        {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            // Kurangi 1 untuk header
            return count($rows) > 0 ? count($rows) - 1 : 0;
        }
        
        return 0;
    }
}