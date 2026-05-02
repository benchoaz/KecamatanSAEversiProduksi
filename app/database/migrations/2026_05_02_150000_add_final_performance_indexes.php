<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('public_services', function (Blueprint $table) {
            // Index for faster lookups by category and status
            $table->index(['category', 'status'], 'idx_ps_cat_status');
            // Index for tracking code with status
            $table->index(['tracking_code', 'status'], 'idx_ps_tracking_status');
            // Index for WhatsApp suffix
            if (!Schema::hasColumn('public_services', 'whatsapp_suffix')) {
                $table->string('whatsapp_suffix', 10)->nullable()->index();
            }
        });

        Schema::table('umkm', function (Blueprint $table) {
            $table->index(['status', 'is_verified'], 'idx_umkm_status_verified');
            $table->index('desa', 'idx_umkm_desa');
        });

        Schema::table('work_directory', function (Blueprint $table) {
            $table->index(['status', 'is_verified'], 'idx_wd_status_verified');
            $table->index('service_area', 'idx_wd_area');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_services', function (Blueprint $table) {
            $table->dropIndex('idx_ps_cat_status');
            $table->dropIndex('idx_ps_tracking_status');
        });

        Schema::table('umkm', function (Blueprint $table) {
            $table->dropIndex('idx_umkm_status_verified');
            $table->dropIndex('idx_umkm_desa');
        });

        Schema::table('work_directory', function (Blueprint $table) {
            $table->dropIndex('idx_wd_status_verified');
            $table->dropIndex('idx_wd_area');
        });
    }
};
