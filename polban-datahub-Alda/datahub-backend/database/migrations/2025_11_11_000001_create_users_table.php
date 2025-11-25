<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);

            // ENUM: admin / participant
            $table->rawColumn('role', 'role_enum')->default('participant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
