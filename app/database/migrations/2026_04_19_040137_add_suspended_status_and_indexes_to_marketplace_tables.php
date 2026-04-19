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
        // 1. Update status columns to support 'suspended'
        if (DB::getDriverName() === 'pgsql') {
            Schema::table('umkm', function (Blueprint $table) {
                $table->index('nama_usaha');
                $table->index('jenis_usaha');
                $table->index('desa');
            });

            Schema::table('work_directory', function (Blueprint $table) {
                $table->index('job_title');
                $table->index('display_name');
                $table->index('job_category');
            });
        } else {
            // MySQL/MariaDB ENUM updates
            DB::statement("ALTER TABLE umkm MODIFY COLUMN status ENUM('pending', 'aktif', 'nonaktif', 'suspended') DEFAULT 'pending'");
            Schema::table('umkm', function (Blueprint $table) {
                $table->index('nama_usaha');
                $table->index('jenis_usaha');
                $table->index('desa');
            });

            DB::statement("ALTER TABLE work_directory MODIFY COLUMN status ENUM('active', 'inactive', 'pending', 'suspended') DEFAULT 'pending'");
            Schema::table('work_directory', function (Blueprint $table) {
                $table->index('job_title');
                $table->index('display_name');
                $table->index('job_category');
            });
        }

        Schema::table('umkm_products', function (Blueprint $table) {
            $table->index('nama_produk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
             Schema::table('umkm', function (Blueprint $table) {
                $table->dropIndex(['nama_usaha']);
                $table->dropIndex(['jenis_usaha']);
                $table->dropIndex(['desa']);
            });

            Schema::table('work_directory', function (Blueprint $table) {
                $table->dropIndex(['job_title']);
                $table->dropIndex(['display_name']);
                $table->dropIndex(['job_category']);
            });
        } else {
            DB::statement("ALTER TABLE umkm MODIFY COLUMN status ENUM('pending', 'aktif', 'nonaktif') DEFAULT 'pending'");
             Schema::table('umkm', function (Blueprint $table) {
                $table->dropIndex(['nama_usaha']);
                $table->dropIndex(['jenis_usaha']);
                $table->dropIndex(['desa']);
            });

            DB::statement("ALTER TABLE work_directory MODIFY COLUMN status ENUM('active', 'inactive', 'pending') DEFAULT 'pending'");
            Schema::table('work_directory', function (Blueprint $table) {
                $table->dropIndex(['job_title']);
                $table->dropIndex(['display_name']);
                $table->dropIndex(['job_category']);
            });
        }

        Schema::table('umkm_products', function (Blueprint $table) {
            $table->dropIndex(['nama_produk']);
        });
    }
};
