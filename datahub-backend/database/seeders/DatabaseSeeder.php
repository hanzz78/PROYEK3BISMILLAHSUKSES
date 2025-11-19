<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat user admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@polban.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Buat user participant untuk testing
        User::create([
            'name' => 'Participant Test',
            'email' => 'participant@polban.ac.id',
            'password' => Hash::make('password'),
            'role' => 'participant',
        ]);

        // Panggil seeder data master (URUTAN PENTING!)
        $this->call([
            ProvinsiSeeder::class,          // Harus pertama
            KabupatenKotaSeeder::class,     // Harus setelah Provinsi
            SltaSeeder::class,              // Bisa kapan saja
            JalurDaftarSeeder::class,       // Bisa kapan saja
        ]);
    }
}
