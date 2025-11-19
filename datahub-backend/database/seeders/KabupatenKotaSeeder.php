<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KabupatenKotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID provinsi
        $jabar = DB::table('provinsi')->where('nama_provinsi', 'Jawa Barat')->first();
        $dki = DB::table('provinsi')->where('nama_provinsi', 'DKI Jakarta')->first();
        $jateng = DB::table('provinsi')->where('nama_provinsi', 'Jawa Tengah')->first();
        $jatim = DB::table('provinsi')->where('nama_provinsi', 'Jawa Timur')->first();
        $banten = DB::table('provinsi')->where('nama_provinsi', 'Banten')->first();

        $kabupatenKotas = [
            // Jawa Barat
            ['id_provinsi' => $jabar->id, 'nama_kabupaten_kota' => 'Kota Bandung'],
            ['id_provinsi' => $jabar->id, 'nama_kabupaten_kota' => 'Kabupaten Bandung'],
            ['id_provinsi' => $jabar->id, 'nama_kabupaten_kota' => 'Kabupaten Bandung Barat'],
            ['id_provinsi' => $jabar->id, 'nama_kabupaten_kota' => 'Kota Cimahi'],
            ['id_provinsi' => $jabar->id, 'nama_kabupaten_kota' => 'Kota Bekasi'],
            ['id_provinsi' => $jabar->id, 'nama_kabupaten_kota' => 'Kabupaten Bekasi'],
            ['id_provinsi' => $jabar->id, 'nama_kabupaten_kota' => 'Kota Bogor'],
            ['id_provinsi' => $jabar->id, 'nama_kabupaten_kota' => 'Kabupaten Bogor'],

            // DKI Jakarta
            ['id_provinsi' => $dki->id, 'nama_kabupaten_kota' => 'Jakarta Pusat'],
            ['id_provinsi' => $dki->id, 'nama_kabupaten_kota' => 'Jakarta Selatan'],
            ['id_provinsi' => $dki->id, 'nama_kabupaten_kota' => 'Jakarta Timur'],
            ['id_provinsi' => $dki->id, 'nama_kabupaten_kota' => 'Jakarta Barat'],
            ['id_provinsi' => $dki->id, 'nama_kabupaten_kota' => 'Jakarta Utara'],

            // Jawa Tengah
            ['id_provinsi' => $jateng->id, 'nama_kabupaten_kota' => 'Kota Semarang'],
            ['id_provinsi' => $jateng->id, 'nama_kabupaten_kota' => 'Kabupaten Semarang'],

            // Jawa Timur
            ['id_provinsi' => $jatim->id, 'nama_kabupaten_kota' => 'Kota Surabaya'],
            ['id_provinsi' => $jatim->id, 'nama_kabupaten_kota' => 'Kabupaten Sidoarjo'],

            // Banten
            ['id_provinsi' => $banten->id, 'nama_kabupaten_kota' => 'Kota Tangerang'],
            ['id_provinsi' => $banten->id, 'nama_kabupaten_kota' => 'Kota Tangerang Selatan'],
        ];

        foreach ($kabupatenKotas as $kabkot) {
            DB::table('kabupaten_kota')->insert([
                'id_provinsi' => $kabkot['id_provinsi'],
                'nama_kabupaten_kota' => $kabkot['nama_kabupaten_kota'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
