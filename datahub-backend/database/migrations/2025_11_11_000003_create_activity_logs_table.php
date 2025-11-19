<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Pastikan ini di-import

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Daftar aksi yang bisa dicatat
        DB::statement("DROP TYPE IF EXISTS action_log_enum CASCADE"); // Baris ini untuk testing jika ada error
        $actions = "'login', 'logout', 'login_failed', 'import_data', 'export_data', 'approve_data', 'reject_data', 'create_user', 'update_user'";
        DB::statement("CREATE TYPE action_log_enum AS ENUM ($actions)");

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // user_id bisa null jika aksi dilakukan oleh sistem (bukan user)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->rawColumn('action', 'action_log_enum');
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            
            // Hanya perlu created_at, tidak perlu updated_at untuk log
            $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        DB::statement("DROP TYPE IF EXISTS action_log_enum");
    }
};