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
            ['nama_jalur_daftar' => 'SNBP'],
            ['nama_jalur_daftar' => 'SNBT'],
            ['nama_jalur_daftar' => 'Mandiri'],
            ['nama_jalur_daftar' => 'Prestasi'],
            ['nama_jalur_daftar' => 'Kerjasama'],
        ];

        foreach ($jalurDaftars as $jalur) {
            DB::table('jalur_daftar')->insert([
                'nama_jalur_daftar' => $jalur['nama_jalur_daftar'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
