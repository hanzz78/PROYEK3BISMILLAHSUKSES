<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinsi', function (Blueprint $table) {
            $table->id('provinsi_id');
            $table->string('nama_provinsi', 100)->unique();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provinsi');
    }
};
