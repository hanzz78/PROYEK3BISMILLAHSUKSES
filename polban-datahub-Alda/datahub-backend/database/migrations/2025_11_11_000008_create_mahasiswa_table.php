<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id('mahasiswa_id');

            // -------------------------------
            // FK TRACKING WAJIB
            // -------------------------------
            $table->foreignId('import_id')
                ->nullable()
                ->constrained('import_mahasiswa', 'import_id')
                ->nullOnDelete();

            $table->foreignId('user_id_importer')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();

            $table->foreignId('user_id_approver')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();


            // -------------------------------
            // DATA MAHASISWA (nullable)
            // -------------------------------
            $table->string('kelas', 2)->nullable();
            $table->integer('angkatan')->nullable();
            $table->date('tgl_lahir')->nullable();

            // ENUM PostgreSQL
            $table->rawColumn('jenis_kelamin', 'jenis_kelamin_enum')->nullable();
            $table->rawColumn('agama', 'agama_enum')->nullable();

            $table->string('kode_pos', 5)->nullable();

            // -------------------------------
            // FK ke tabel master (nullable)
            // -------------------------------
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
