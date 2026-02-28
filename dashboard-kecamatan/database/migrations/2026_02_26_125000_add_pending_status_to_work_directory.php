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
        // Update the enum to include 'pending'
        DB::statement("ALTER TABLE work_directory MODIFY COLUMN status ENUM('active', 'inactive', 'pending') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE work_directory MODIFY COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
    }
};
