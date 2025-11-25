<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id('activitylog_id');

            // Boleh null karena kadang sistem yang buat log
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users', 'user_id')
                ->onDelete('set null');

            // ENUM action_log_enum
            $table->rawColumn('action', 'action_log_enum');

            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
