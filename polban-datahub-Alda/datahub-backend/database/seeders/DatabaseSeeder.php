<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat user admin
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@polban.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Buat user participant untuk testing
        DB::table('users')->insert([
            'name' => 'Participant',
            'email' => 'participant@polban.ac.id',
            'password' => Hash::make('password'),
            'role' => 'participant',
        ]);

        // Buat user internal
        DB::table('users')->insert([
            'name' => 'Internal',
            'email' => 'internal@polban.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Buat import_mahasiswa dummy untuk foreign key
        DB::table('import_mahasiswa')->insert([
            'user_id' => 1,
            'status' => 'approved',
        ]);

        // Panggil seeder data master (URUTAN PENTING!)
        $this->call([
            ProvinsiSeeder::class,         // Harus pertama
            WilayahSeeder::class,          // Harus setelah Provinsi
            SltaSeeder::class,             // Bisa kapan saja
            JalurDaftarSeeder::class,      // Bisa kapan saja
            //MahasiswaSeeder::class,        // Terakhir, baca dari CSV
        ]);
    }
}
