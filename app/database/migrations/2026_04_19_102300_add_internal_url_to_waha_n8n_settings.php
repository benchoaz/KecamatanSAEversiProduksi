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
        Schema::table('waha_n8n_settings', function (Blueprint $table) {
            $table->string('n8n_dashboard_internal_url')->nullable()->after('n8n_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waha_n8n_settings', function (Blueprint $table) {
            $table->dropColumn('n8n_dashboard_internal_url');
        });
    }
};
