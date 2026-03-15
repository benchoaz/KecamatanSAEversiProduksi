<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Performance indexes for faster queries
     */
    public function up(): void
    {
        // Public Services indexes
        Schema::table('public_services', function (Blueprint $table) {
            $table->index('status', 'idx_public_service_status');
            $table->index('tracking_code', 'idx_public_service_tracking');
            $table->index('whatsapp_suffix', 'idx_public_service_whatsapp');
            $table->index('category', 'idx_public_service_category');
            $table->index(['desa_id', 'status'], 'idx_public_service_desa_status');
        });

        // Users indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index(['desa_id', 'role_id'], 'idx_user_desa_role');
            $table->index('status', 'idx_user_status');
        });

        // Submissions indexes
        Schema::table('submissions', function (Blueprint $table) {
            $table->index(['desa_id', 'status'], 'idx_submission_desa_status');
            $table->index('submitted_by', 'idx_submission_submitted_by');
        });

        // Announcement indexes
        Schema::table('announcements', function (Blueprint $table) {
            $table->index(['is_active', 'start_date', 'end_date'], 'idx_announcement_active_dates');
            $table->index('target_type', 'idx_announcement_target_type');
        });

        // Desa indexes
        Schema::table('desa', function (Blueprint $table) {
            $table->index('status', 'idx_desa_status');
            $table->index('kode_desa', 'idx_desa_kode');
        });

        // Berita indexes
        Schema::table('berita', function (Blueprint $table) {
            $table->index(['is_published', 'published_at'], 'idx_berita_published');
            $table->index('slug', 'idx_berita_slug');
        });

        // UMKM indexes
        Schema::table('umkm_locals', function (Blueprint $table) {
            $table->index(['is_verified', 'is_active'], 'idx_umkm_verified_active');
            $table->index('module', 'idx_umkm_module');
        });

        // Loker indexes
        Schema::table('lokers', function (Blueprint $table) {
            $table->index('status', 'idx_loker_status');
            $table->index(['is_active', 'expired_at'], 'idx_loker_active_expired');
        });

        // Master Layanan indexes
        Schema::table('master_layanans', function (Blueprint $table) {
            $table->index(['is_active', 'urutan'], 'idx_master_layanan_active_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes if needed (be careful in production!)
        Schema::table('public_services', function (Blueprint $table) {
            $table->dropIndex('idx_public_service_status');
            $table->dropIndex('idx_public_service_tracking');
            $table->dropIndex('idx_public_service_whatsapp');
            $table->dropIndex('idx_public_service_category');
            $table->dropIndex('idx_public_service_desa_status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_user_desa_role');
            $table->dropIndex('idx_user_status');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('idx_submission_desa_status');
            $table->dropIndex('idx_submission_submitted_by');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex('idx_announcement_active_dates');
            $table->dropIndex('idx_announcement_target_type');
        });

        Schema::table('desa', function (Blueprint $table) {
            $table->dropIndex('idx_desa_status');
            $table->dropIndex('idx_desa_kode');
        });

        Schema::table('berita', function (Blueprint $table) {
            $table->dropIndex('idx_berita_published');
            $table->dropIndex('idx_berita_slug');
        });

        Schema::table('umkm_locals', function (Blueprint $table) {
            $table->dropIndex('idx_umkm_verified_active');
            $table->dropIndex('idx_umkm_module');
        });

        Schema::table('lokers', function (Blueprint $table) {
            $table->dropIndex('idx_loker_status');
            $table->dropIndex('idx_loker_active_expired');
        });

        Schema::table('master_layanans', function (Blueprint $table) {
            $table->dropIndex('idx_master_layanan_active_order');
        });
    }
};
