<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jalur_daftar', function (Blueprint $table) {
            $table->id('jalur_daftar_id');
            $table->string('nama_jalur_daftar', 20)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jalur_daftar');
    }
};
