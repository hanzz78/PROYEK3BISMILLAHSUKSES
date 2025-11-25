<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JalurDaftarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jalurDaftars = [
            'SNBP',
            'SNBT',
            'SMBM-TES',
            'SMBM-UTBK',
            'SMB / SMBM',
            'ADIK',
        ];

        foreach ($jalurDaftars as $jalur) {
            DB::table('jalur_daftar')->insert([
                'nama_jalur_daftar' => $jalur,
            ]);
        }
    }
}
