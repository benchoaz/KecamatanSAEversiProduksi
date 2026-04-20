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
        Schema::create('dokumen_pencairan_desas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained('desa')->onDelete('cascade');
            $table->integer('tahun')->default(date('Y'));
            $table->enum('kategori_dokumen', [
                'apbdes_2026',
                'perkades_penjabaran',
                'lpj_2025',
                'laporan_realisasi_tahap_sebelumnya'
            ]);
            $table->string('file_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_pencairan_desas');
    }
};
