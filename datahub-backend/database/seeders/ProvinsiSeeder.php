<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinsiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinsis = [
            ['nama_provinsi' => 'Jawa Barat'],
            ['nama_provinsi' => 'DKI Jakarta'],
            ['nama_provinsi' => 'Jawa Tengah'],
            ['nama_provinsi' => 'Jawa Timur'],
            ['nama_provinsi' => 'Banten'],
        ];

        foreach ($provinsis as $provinsi) {
            DB::table('provinsi')->insert([
                'nama_provinsi' => $provinsi['nama_provinsi'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
