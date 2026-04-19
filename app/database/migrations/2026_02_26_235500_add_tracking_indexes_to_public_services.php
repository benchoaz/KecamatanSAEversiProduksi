<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * This migration adds optimized indexes for the lacak-berkas (file tracking) feature.
     * These indexes improve search performance for:
     * - PIN (tracking_code) lookups
     * - WhatsApp number searches
     * - Category + status filtering
     */
    public function up(): void
    {
        Schema::table('public_services', function (Blueprint $table) {
            // Existing: tracking_code column but let's ensure it's indexed properly
            // Already has index from previous migration, but let's add more

            // Composite index for category + status + created_at (common query pattern)
            if (!Schema::hasIndex('public_services', 'public_services_cat_status_date_idx')) {
                $table->index(['category', 'status', 'created_at'], 'public_services_cat_status_date_idx');
            }

            // Index for WhatsApp suffix search (last 10 digits)
            if (!Schema::hasColumn('public_services', 'whatsapp_suffix')) {
                $table->string('whatsapp_suffix', 10)->nullable()->after('whatsapp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_services', function (Blueprint $table) {
            $table->dropIndex('public_services_cat_status_date_idx');
            $table->dropColumn('whatsapp_suffix');
        });
    }
};
