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
        Schema::create('rekomendasi_pencairan_dds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained('desa')->onDelete('cascade');
            $table->integer('tahap_pencairan')->comment('1, 2, atau 3');
            $table->enum('status_akhir', [
                'VALID', 
                'TIDAK VALID', 
                'BELUM ADA', 
                'TUNDA', 
                'TIDAK LAYAK', 
                'PERBAIKAN', 
                'LAYAK CAIR'
            ])->default('BELUM ADA');
            $table->text('catatan_revisi')->nullable();
            $table->string('pdf_rekomendasi_camat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_pencairan_dds');
    }
};
