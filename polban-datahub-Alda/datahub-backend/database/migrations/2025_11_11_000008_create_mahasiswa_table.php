<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            // Kita gunakan mahasiswa_id agar konsisten dengan gaya penamaan Anda
            $table->id('mahasiswa_id');

            // -------------------------------
            // FK TRACKING (Wajib Sebutkan Nama Kolom Tujuan)
            // -------------------------------
            
            // Fix: Sebutkan 'import_id' parameter ke-2
            $table->foreignId('import_id')
                ->nullable()
                ->constrained('import_mahasiswa', 'import_id') 
                ->nullOnDelete();

            // Fix: Sebutkan 'user_id' parameter ke-2
            $table->foreignId('user_id_importer')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();

            $table->foreignId('user_id_approver')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();


            // -------------------------------
            // DATA MAHASISWA
            // -------------------------------
            $table->string('kelas', 10)->nullable();
            $table->integer('angkatan')->nullable();
            $table->date('tgl_lahir')->nullable();

            // Gunakan string jika enum custom belum dibuat
            $table->string('jenis_kelamin')->nullable();
            $table->string('agama')->nullable();

            $table->string('kode_pos', 10)->nullable();

            // -------------------------------
            // FK ke tabel master 
            // -------------------------------
            // Pastikan tabel slta/jalur/wilayah juga menggunakan primary key custom (slta_id, dll)
            // Jika mereka pakai standard id(), ubah parameter ke-2 jadi 'id'
            
            $table->foreignId('slta_id')
                ->nullable()
                ->constrained('slta', 'slta_id')
                ->onDelete('restrict');

            $table->foreignId('jalur_daftar_id')
                ->nullable()
                ->constrained('jalur_daftar', 'jalur_daftar_id')
                ->onDelete('restrict');

            $table->foreignId('wilayah_id')
                ->nullable()
                ->constrained('wilayah', 'wilayah_id')
                ->onDelete('restrict');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};