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
        Schema::table('personil_desa', function (Blueprint $table) {
            $table->text('alasan_revisi')->nullable();
            $table->timestamp('tanggal_permohonan_revisi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personil_desa', function (Blueprint $table) {
            $table->dropColumn(['alasan_revisi', 'tanggal_permohonan_revisi']);
        });
    }
};
