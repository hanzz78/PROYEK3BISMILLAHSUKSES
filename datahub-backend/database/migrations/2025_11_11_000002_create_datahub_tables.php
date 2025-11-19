<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //Definisikan tipe ENUM kustom untuk PostgreSQL
        DB::statement("DROP TYPE IF EXISTS import_status_enum CASCADE");
        DB::statement("DROP TYPE IF EXISTS jenis_kelamin_enum CASCADE");
        DB::statement("DROP TYPE IF EXISTS agama_enum CASCADE");

        DB::statement("CREATE TYPE import_status_enum AS ENUM ('pending', 'approved', 'rejected')");
        DB::statement("CREATE TYPE jenis_kelamin_enum AS ENUM ('L', 'P')");
        $agamas = "'Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu', 'Lainnya'";
        DB::statement("CREATE TYPE agama_enum AS ENUM ($agamas)");

        // Tabel Master/Lookup SLTA
        Schema::create('slta', function (Blueprint $table) {
            $table->id();
            $table->string('nama_slta_resmi', 100)->unique();
            $table->timestamps();
        });

        // Tabel Master/Lookup Jalur Daftar
        Schema::create('jalur_daftar', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jalur_daftar', 50)->unique();
            $table->timestamps();
        });

        // REVISI V5: Tabel Provinsi (BARU)
        Schema::create('provinsi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_provinsi', 100)->unique();
            $table->timestamps();
        });

        // REVISI V5: Tabel Kabupaten/Kota (BARU, menggantikan 'wilayah')
        Schema::create('kabupaten_kota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_provinsi')->constrained('provinsi')->onDelete('restrict');
            $table->string('nama_kabupaten_kota', 100);
            $table->timestamps();
            
            $table->unique(['id_provinsi', 'nama_kabupaten_kota']); // Kombinasi harus unik
        });

        // Tabel Staging untuk impor data (Tidak berubah dari v4)
        Schema::create('import_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->rawColumn('status', 'import_status_enum')->default('pending');

            // Data mentah (semua sudah nullable)
            $table->string('kelas', 10)->nullable();
            $table->integer('angkatan')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->rawColumn('jenis_kelamin', 'jenis_kelamin_enum')->nullable();
            $table->rawColumn('agama', 'agama_enum')->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('nama_slta_raw', 255)->nullable();
            $table->string('nama_jalur_daftar_raw', 255)->nullable();
            $table->string('nama_wilayah_raw', 255)->nullable(); // Kab/Kota
            $table->string('provinsi_raw', 255)->nullable(); // Provinsi
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            $table->index('status');
        });

        // Tabel Final (data bersih)
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            
            // Kolom pelacakan (WAJIB)
            $table->foreignId('import_id')->constrained('import_mahasiswa')->onDelete('restrict');
            $table->foreignId('user_id_importer')->constrained('users')->onDelete('restrict');
            $table->foreignId('user_id_approver')->constrained('users')->onDelete('restrict');
            
            // Data (NULLABLE)
            $table->string('kelas', 2)->nullable();
            $table->integer('angkatan')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->rawColumn('jenis_kelamin', 'jenis_kelamin_enum')->nullable();
            $table->rawColumn('agama', 'agama_enum')->nullable();
            $table->string('kode_pos', 10)->nullable();
            
            // Foreign Keys (NULLABLE)
            $table->foreignId('id_slta')->nullable()->constrained('slta')->onDelete('restrict');
            $table->foreignId('id_jalur_daftar')->nullable()->constrained('jalur_daftar')->onDelete('restrict');
            
            // REVISI V5: FK ke tabel 'kabupaten_kota'
            $table->foreignId('id_kabupaten_kota')->nullable()->constrained('kabupaten_kota')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
        Schema::dropIfExists('import_mahasiswa');
        Schema::dropIfExists('kabupaten_kota'); // REVISI V5
        Schema::dropIfExists('provinsi'); // REVISI V5
        Schema::dropIfExists('jalur_daftar');
        Schema::dropIfExists('slta');

        // Hapus ENUMs
        DB::statement("DROP TYPE IF EXISTS import_status_enum");
        DB::statement("DROP TYPE IF EXISTS jenis_kelamin_enum");
        DB::statement("DROP TYPE IF EXISTS agama_enum");
    }
};