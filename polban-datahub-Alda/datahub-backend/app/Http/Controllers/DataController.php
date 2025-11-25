<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class DataController extends Controller
{
    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }
    
    public function dataMahasiswa()
    {
        $mahasiswa = Mahasiswa::with(['slta', 'jalurDaftar','wilayah', 'provinsi' ])->get();
        return response()->json([
            'total' => $mahasiswa->count(),
            'data' => $mahasiswa
        ], 200);
    }
}