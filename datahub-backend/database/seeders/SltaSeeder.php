<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SltaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sltas = [
            ['nama_slta_resmi' => 'SMAN 1 Bandung'],
            ['nama_slta_resmi' => 'SMAN 2 Bandung'],
            ['nama_slta_resmi' => 'SMAN 3 Bandung'],
            ['nama_slta_resmi' => 'SMAN 5 Bandung'],
            ['nama_slta_resmi' => 'SMKN 1 Kota Bandung'],
            ['nama_slta_resmi' => 'SMKN 2 Kota Bandung'],
            ['nama_slta_resmi' => 'SMKN 4 Kota Bandung'],
            ['nama_slta_resmi' => 'SMA Pasundan 1 Bandung'],
            ['nama_slta_resmi' => 'SMA BPI 1 Bandung'],
            ['nama_slta_resmi' => 'SMAN 1 Jakarta'],
            ['nama_slta_resmi' => 'SMAN 8 Jakarta'],
            ['nama_slta_resmi' => 'SMAN 1 Cimahi'],
            ['nama_slta_resmi' => 'SMKN 1 Cimahi'],
        ];

        foreach ($sltas as $slta) {
            DB::table('slta')->insert([
                'nama_slta_resmi' => $slta['nama_slta_resmi'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
