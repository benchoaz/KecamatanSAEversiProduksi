<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE work_directory ALTER COLUMN status TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE work_directory ALTER COLUMN status SET DEFAULT 'pending'");
        } else {
            DB::statement("ALTER TABLE work_directory MODIFY COLUMN status ENUM('active', 'inactive', 'pending') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE work_directory ALTER COLUMN status SET DEFAULT 'active'");
        } else {
            DB::statement("ALTER TABLE work_directory MODIFY COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
        }
    }
};
