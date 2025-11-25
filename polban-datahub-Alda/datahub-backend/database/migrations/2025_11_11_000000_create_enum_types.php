<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ENUM untuk role user
        DB::statement("DO $$ BEGIN
            CREATE TYPE role_enum AS ENUM ('admin', 'participant');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        // ENUM untuk status impor
        DB::statement("DO $$ BEGIN
            CREATE TYPE import_status_enum AS ENUM ('uploaded', 'reviewed', 'approved', 'rejected', 'in_process', 'visualizing', 'completed');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        // ENUM jenis kelamin
        DB::statement("DO $$ BEGIN
            CREATE TYPE jenis_kelamin_enum AS ENUM ('L', 'P');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        // ENUM agama
        $agamas = "'Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu', 'Lainnya'";
        DB::statement("DO $$ BEGIN
            CREATE TYPE agama_enum AS ENUM ($agamas);
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        // ENUM untuk activity log
        $actions = "'login', 'logout', 'login_failed', 'import_data', 'export_data', 'approve_data', 'reject_data', 'create_user', 'update_user'";
        DB::statement("DO $$ BEGIN
            CREATE TYPE action_log_enum AS ENUM ($actions);
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TYPE IF EXISTS action_log_enum");
        DB::statement("DROP TYPE IF EXISTS agama_enum");
        DB::statement("DROP TYPE IF EXISTS jenis_kelamin_enum");
        DB::statement("DROP TYPE IF EXISTS import_status_enum");
        DB::statement("DROP TYPE IF EXISTS role_enum");
    }
};
