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
        Schema::table('public_service_attachments', function (Blueprint $table) {
            $table->text('ai_analysis_result')->nullable();
            $table->string('ai_status')->nullable(); // valid, suspicious, invalid
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_service_attachments', function (Blueprint $table) {
            $table->dropColumn(['ai_analysis_result', 'ai_status']);
        });
    }
};
