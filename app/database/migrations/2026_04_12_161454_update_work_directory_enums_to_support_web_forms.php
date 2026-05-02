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
        if (DB::getDriverName() === 'pgsql') {
            // Drop existing constraints first to avoid "Check Violation"
            DB::statement("ALTER TABLE work_directory DROP CONSTRAINT IF EXISTS work_directory_data_source_check");
            DB::statement("ALTER TABLE work_directory DROP CONSTRAINT IF EXISTS work_directory_status_check");
            DB::statement("ALTER TABLE work_directory DROP CONSTRAINT IF EXISTS work_directory_job_type_check");
            
            DB::statement("ALTER TABLE work_directory ALTER COLUMN job_type TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE work_directory ALTER COLUMN status TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE work_directory ALTER COLUMN status SET DEFAULT 'pending'");
            DB::statement("ALTER TABLE work_directory ALTER COLUMN data_source TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE work_directory ALTER COLUMN data_source SET DEFAULT 'kecamatan'");
        } else {
            DB::statement("ALTER TABLE work_directory MODIFY COLUMN job_type ENUM('harian', 'jasa', 'keliling', 'transportasi', 'umkm') NOT NULL");
            DB::statement("ALTER TABLE work_directory MODIFY COLUMN status ENUM('active', 'inactive', 'pending') DEFAULT 'pending'");
            DB::statement("ALTER TABLE work_directory MODIFY COLUMN data_source ENUM('kecamatan', 'desa', 'warga', 'web_form') DEFAULT 'kecamatan'");
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
            DB::statement("ALTER TABLE work_directory MODIFY COLUMN job_type ENUM('harian', 'jasa', 'keliling', 'transportasi') NOT NULL");
            DB::statement("ALTER TABLE work_directory MODIFY COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
            DB::statement("ALTER TABLE work_directory MODIFY COLUMN data_source ENUM('kecamatan', 'desa', 'warga') DEFAULT 'kecamatan'");
        }
    }
};
