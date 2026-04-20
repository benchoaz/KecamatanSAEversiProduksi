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
        Schema::create('desa_summary_pmks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained('desa')->onDelete('cascade');
            $table->decimal('pagu_dd_2026', 15, 2)->default(0);
            $table->boolean('status_lpj_2025')->default(false)->comment('Wajib TRUE (100% selesai) untuk pencairan 2026');
            $table->integer('tahap_berjalan')->default(1)->comment('Tahap pencairan: 1, 2, atau 3');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('desa_summary_pmks');
    }
};
