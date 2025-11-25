<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id('wilayah_id');

            $table->foreignId('provinsi_id')
                ->constrained('provinsi', 'provinsi_id')
                ->onDelete('restrict');

            $table->string('nama_wilayah', 100);
            $table->double('latitude');
            $table->double('longitude');

            $table->unique(['provinsi_id', 'nama_wilayah']); // kombinasi unik
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah');
    }
};
