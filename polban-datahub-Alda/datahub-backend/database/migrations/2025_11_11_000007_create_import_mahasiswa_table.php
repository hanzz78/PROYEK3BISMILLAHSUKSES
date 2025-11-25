<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_mahasiswa', function (Blueprint $table) {
            $table->id('import_id');

            // FK ke users (importer) - wajib ada
            $table->foreignId('user_id')
                ->constrained('users', 'user_id')
                ->onDelete('cascade');

            // ENUM status: pending, approved, rejected
            $table->rawColumn('status', 'import_status_enum')
                ->default('pending')
                ->index();

            // RAW DATA (nullable semua)
            $table->string('kelas', 2)->nullable();
            $table->integer('angkatan')->nullable();
            $table->date('tgl_lahir')->nullable();

            $table->rawColumn('jenis_kelamin', 'jenis_kelamin_enum')->nullable();
            $table->rawColumn('agama', 'agama_enum')->nullable();

            $table->string('kode_pos', 5)->nullable();
            $table->string('nama_slta_raw', 255)->nullable();
            $table->string('nama_jalur_daftar_raw', 20)->nullable();
            $table->string('nama_wilayah_raw', 100)->nullable();   // kab/kota
            $table->string('provinsi_raw', 255)->nullable();        // provinsi
            $table->text('admin_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_mahasiswa');
    }
};
