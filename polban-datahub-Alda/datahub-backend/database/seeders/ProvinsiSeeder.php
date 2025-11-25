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
            ['nama_provinsi' => 'Aceh', 'latitude' => 4.6951, 'longitude' => 96.7494],
            ['nama_provinsi' => 'Sumatera Utara', 'latitude' => 2.1154, 'longitude' => 99.5451],
            ['nama_provinsi' => 'Sumatera Barat', 'latitude' => -0.7399, 'longitude' => 100.8000],
            ['nama_provinsi' => 'Riau', 'latitude' => 0.2933, 'longitude' => 101.7068],
            ['nama_provinsi' => 'Jambi', 'latitude' => -1.6101, 'longitude' => 103.6131],
            ['nama_provinsi' => 'Sumatera Selatan', 'latitude' => -3.3194, 'longitude' => 104.9148],
            ['nama_provinsi' => 'Bengkulu', 'latitude' => -3.5778, 'longitude' => 102.3463],
            ['nama_provinsi' => 'Lampung', 'latitude' => -4.5586, 'longitude' => 105.4068],
            ['nama_provinsi' => 'Kepulauan Bangka Belitung', 'latitude' => -2.7411, 'longitude' => 106.4406],
            ['nama_provinsi' => 'Kepulauan Riau', 'latitude' => 3.9457, 'longitude' => 108.1429],
            ['nama_provinsi' => 'DKI Jakarta', 'latitude' => -6.2088, 'longitude' => 106.8456],
            ['nama_provinsi' => 'Jawa Barat', 'latitude' => -6.9175, 'longitude' => 107.6191],
            ['nama_provinsi' => 'Jawa Tengah', 'latitude' => -7.1506, 'longitude' => 110.1429],
            ['nama_provinsi' => 'DI Yogyakarta', 'latitude' => -7.7956, 'longitude' => 110.3695],
            ['nama_provinsi' => 'Jawa Timur', 'latitude' => -7.5506, 'longitude' => 112.7520],
            ['nama_provinsi' => 'Banten', 'latitude' => -6.4058, 'longitude' => 106.0640],
            ['nama_provinsi' => 'Bali', 'latitude' => -8.3405, 'longitude' => 115.0920],
            ['nama_provinsi' => 'Nusa Tenggara Barat', 'latitude' => -8.6529, 'longitude' => 117.3616],
            ['nama_provinsi' => 'Nusa Tenggara Timur', 'latitude' => -8.6574, 'longitude' => 121.0794],
            ['nama_provinsi' => 'Kalimantan Barat', 'latitude' => -0.2787, 'longitude' => 111.4752],
            ['nama_provinsi' => 'Kalimantan Tengah', 'latitude' => -1.6815, 'longitude' => 113.3824],
            ['nama_provinsi' => 'Kalimantan Selatan', 'latitude' => -3.0926, 'longitude' => 115.2838],
            ['nama_provinsi' => 'Kalimantan Timur', 'latitude' => 0.5387, 'longitude' => 116.4194],
            ['nama_provinsi' => 'Kalimantan Utara', 'latitude' => 3.0731, 'longitude' => 116.0413],
            ['nama_provinsi' => 'Sulawesi Utara', 'latitude' => 0.6246, 'longitude' => 123.9750],
            ['nama_provinsi' => 'Sulawesi Tengah', 'latitude' => -1.4301, 'longitude' => 121.4456],
            ['nama_provinsi' => 'Sulawesi Selatan', 'latitude' => -3.6687, 'longitude' => 119.9740],
            ['nama_provinsi' => 'Sulawesi Tenggara', 'latitude' => -4.1448, 'longitude' => 122.1747],
            ['nama_provinsi' => 'Gorontalo', 'latitude' => 0.6999, 'longitude' => 122.4467],
            ['nama_provinsi' => 'Sulawesi Barat', 'latitude' => -2.8441, 'longitude' => 119.2320],
            ['nama_provinsi' => 'Maluku', 'latitude' => -3.2385, 'longitude' => 130.1453],
            ['nama_provinsi' => 'Maluku Utara', 'latitude' => 1.5709, 'longitude' => 127.8087],
            ['nama_provinsi' => 'Papua Barat', 'latitude' => -1.3361, 'longitude' => 133.1747],
            ['nama_provinsi' => 'Papua', 'latitude' => -4.2699, 'longitude' => 138.0804],
            ['nama_provinsi' => 'Papua Tengah', 'latitude' => -3.8642, 'longitude' => 136.3733],
            ['nama_provinsi' => 'Papua Pegunungan', 'latitude' => -4.2478, 'longitude' => 139.3725],
            ['nama_provinsi' => 'Papua Selatan', 'latitude' => -7.6961, 'longitude' => 139.6819],
            ['nama_provinsi' => 'Papua Barat Daya', 'latitude' => -1.3361, 'longitude' => 133.1747]

        ];

        foreach ($provinsis as $provinsi) {
            DB::table('provinsi')->insert([
                'nama_provinsi' => $provinsi['nama_provinsi'],
                'latitude' => $provinsi['latitude'],
                'longitude' => $provinsi['longitude'],
            ]);
        }
    }
}
