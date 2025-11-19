<?php

namespace App\Http\Controllers;

use App\Models\ImportMahasiswa;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Store imported data (Participant only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,xlsx,xls,txt|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            $rowCount = 0;
            $errors = [];

            if (in_array($extension, ['csv', 'txt'])) {
                $handle = fopen($file->getRealPath(), 'r');

                $header = fgetcsv($handle);
                if (!$header) {
                    throw new \Exception('File CSV kosong');
                }

                $lineNumber = 1;

                while (($row = fgetcsv($handle)) !== false) {
                    $lineNumber++;

                    if (empty(array_filter($row))) {
                        continue;
                    }

                    try {
                        ImportMahasiswa::create([
                            'user_id' => auth()->id(),
                            'status' => 'pending',
                            'kelas' => isset($row[0]) && $row[0] !== '' ? $row[0] : null,
                            'angkatan' => isset($row[1]) && $row[1] !== '' ? (int)$row[1] : null,
                            'tgl_lahir' => isset($row[2]) && $row[2] !== '' ? $row[2] : null,
                            'jenis_kelamin' => isset($row[3]) && $row[3] !== '' ? $row[3] : null,
                            'agama' => isset($row[4]) && $row[4] !== '' ? $row[4] : null,
                            'kode_pos' => isset($row[5]) && $row[5] !== '' ? $row[5] : null,
                            'nama_slta_raw' => isset($row[6]) && $row[6] !== '' ? $row[6] : null,
                            'nama_jalur_daftar_raw' => isset($row[7]) && $row[7] !== '' ? $row[7] : null,
                            'nama_wilayah_raw' => isset($row[8]) && $row[8] !== '' ? $row[8] : null,
                            'provinsi_raw' => isset($row[9]) && $row[9] !== '' ? $row[9] : null,
                        ]);
                        $rowCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Baris {$lineNumber}: " . $e->getMessage();
                    }
                }

                fclose($handle);
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();

                if (empty($rows)) {
                    throw new \Exception('File Excel kosong');
                }

                // Skip header row
                array_shift($rows);

                $lineNumber = 1;

                foreach ($rows as $row) {
                    $lineNumber++;

                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    try {
                        ImportMahasiswa::create([
                            'user_id' => auth()->id(),
                            'status' => 'pending',
                            'kelas' => isset($row[0]) && $row[0] !== '' ? $row[0] : null,
                            'angkatan' => isset($row[1]) && $row[1] !== '' ? (int)$row[1] : null,
                            'tgl_lahir' => isset($row[2]) && $row[2] !== '' ? $row[2] : null,
                            'jenis_kelamin' => isset($row[3]) && $row[3] !== '' ? $row[3] : null,
                            'agama' => isset($row[4]) && $row[4] !== '' ? $row[4] : null,
                            'kode_pos' => isset($row[5]) && $row[5] !== '' ? $row[5] : null,
                            'nama_slta_raw' => isset($row[6]) && $row[6] !== '' ? $row[6] : null,
                            'nama_jalur_daftar_raw' => isset($row[7]) && $row[7] !== '' ? $row[7] : null,
                            'nama_wilayah_raw' => isset($row[8]) && $row[8] !== '' ? $row[8] : null,
                            'provinsi_raw' => isset($row[9]) && $row[9] !== '' ? $row[9] : null,
                        ]);
                        $rowCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Baris {$lineNumber}: " . $e->getMessage();
                    }
                }
            } else {
                throw new \Exception('Format file tidak didukung.');
            }

            $this->activityLogService->log(
                'import_data',
                "User imported {$rowCount} data rows",
                auth()->id(),
                $request
            );

            $response = [
                'message' => 'Data imported successfully',
                'rows_imported' => $rowCount,
            ];

            if (!empty($errors)) {
                $response['errors'] = array_slice($errors, 0, 10);
            }

            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
