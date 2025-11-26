<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_mahasiswa', function (Blueprint $table) {
            $table->id('import_id'); // Primary Key

            // FK ke users
            $table->foreignId('user_id')
                  ->constrained('users', 'user_id') // Pastikan targetnya user_id
                  ->onDelete('cascade');

            // --- KOLOM FILE (PENTING: INI YANG KURANG SEBELUMNYA) ---
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('total_rows')->default(0);

            // Status
            $table->string('status')->default('uploaded')->index();

            // RAW DATA
            $table->string('kelas', 10)->nullable();
            $table->integer('angkatan')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('agama')->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('nama_slta_raw', 255)->nullable();
            $table->string('nama_jalur_daftar_raw', 100)->nullable();
            $table->string('nama_wilayah_raw', 100)->nullable();
            $table->string('provinsi_raw', 255)->nullable();
            
            $table->text('admin_notes')->nullable();
            
            // Timestamps wajib ada
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_mahasiswa');
    }
};