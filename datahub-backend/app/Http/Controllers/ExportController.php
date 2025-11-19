<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExportController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Export mahasiswa data (Shared - Admin & Participant)
     */
    public function export(Request $request)
    {
        try {
            $query = Mahasiswa::with([
                'slta',
                'jalurDaftar',
                'kabupatenKota.provinsi',
                'importer',
                'approver'
            ]);

            // Filter by angkatan if provided
            if ($request->has('angkatan')) {
                $query->where('angkatan', $request->angkatan);
            }

            // Filter by kelas if provided
            if ($request->has('kelas')) {
                $query->where('kelas', $request->kelas);
            }

            // Filter by tahun if provided
            if ($request->has('tahun')) {
                $query->whereYear('created_at', $request->tahun);
            }

            $data = $query->get();

            // Transform data untuk export
            $exportData = $data->map(function ($mhs) {
                return [
                    'ID' => $mhs->id,
                    'Kelas' => $mhs->kelas,
                    'Angkatan' => $mhs->angkatan,
                    'Tanggal Lahir' => $mhs->tgl_lahir?->format('Y-m-d'),
                    'Jenis Kelamin' => $mhs->jenis_kelamin,
                    'Agama' => $mhs->agama,
                    'Kode Pos' => $mhs->kode_pos,
                    'SLTA' => $mhs->slta?->nama_slta_resmi,
                    'Jalur Daftar' => $mhs->jalurDaftar?->nama_jalur_daftar,
                    'Kabupaten/Kota' => $mhs->kabupatenKota?->nama_kabupaten_kota,
                    'Provinsi' => $mhs->kabupatenKota?->provinsi?->nama_provinsi,
                    'Importer' => $mhs->importer?->name,
                    'Approver' => $mhs->approver?->name,
                    'Created At' => $mhs->created_at->format('Y-m-d H:i:s'),
                ];
            });

            // Log activity
            $filterDesc = [];
            if ($request->has('angkatan')) {
                $filterDesc[] = "angkatan {$request->angkatan}";
            }
            if ($request->has('kelas')) {
                $filterDesc[] = "kelas {$request->kelas}";
            }

            $description = "User exported " . $data->count() . " records";
            if (!empty($filterDesc)) {
                $description .= " (" . implode(', ', $filterDesc) . ")";
            }

            $this->activityLogService->log(
                'export_data',
                $description,
                auth()->id(),
                $request
            );

            // Create Excel file
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set header
            $headers = [
                'ID',
                'Kelas',
                'Angkatan',
                'Tanggal Lahir',
                'Jenis Kelamin',
                'Agama',
                'Kode Pos',
                'SLTA',
                'Jalur Daftar',
                'Kabupaten/Kota',
                'Provinsi',
                'Importer',
                'Approver',
                'Created At'
            ];

            $sheet->fromArray([$headers], null, 'A1');

            // Style header
            $headerRange = 'A1:N1';
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);

            // Add data rows
            $rowNumber = 2;
            foreach ($exportData as $row) {
                $sheet->fromArray([array_values($row)], null, 'A' . $rowNumber);
                $rowNumber++;
            }

            // Auto-size columns
            foreach (range('A', 'N') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Generate filename
            $filename = 'mahasiswa_export_' . date('YmdHis') . '.xlsx';

            // Create writer and download
            $writer = new Xlsx($spreadsheet);

            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Export failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
