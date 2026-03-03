<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Indeks untuk optimasi query Ekonomi & Pembangunan
     * Berdasarkan analisis struktur database
     */
    public function up(): void
    {
        // ==================== INDEKS EKONOMI ====================

        // Tabel umkm
        Schema::table('umkm', function (Blueprint $table) {
            $table->index(['desa_id', 'status'], 'idx_umkm_desa_status');
            $table->index(['jenis_usaha', 'status'], 'idx_umkm_jenis_status');
            $table->index('created_at', 'idx_umkm_created_at');
        });

        // Tabel umkm_local
        Schema::table('umkm_local', function (Blueprint $table) {
            $table->index(['desa_id', 'status'], 'idx_umkm_local_desa_status');
            $table->index('kategori', 'idx_umkm_local_kategori');
        });

        // Tabel work_directory
        Schema::table('work_directory', function (Blueprint $table) {
            $table->index(['job_category', 'status'], 'idx_workdir_category_status');
            $table->index('service_area', 'idx_workdir_service_area');
            $table->index('price', 'idx_workdir_price');
        });

        // Tabel loker
        Schema::table('loker', function (Blueprint $table) {
            $table->index(['desa_id', 'status'], 'idx_loker_desa_status');
            $table->index('tanggal_tutup', 'idx_loker_tgl_tutup');
            $table->index(['jenis', 'status'], 'idx_loker_jenis_status');
        });

        // Tabel job_vacancy
        Schema::table('job_vacancy', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'idx_jobvacancy_status_created');
        });

        // ==================== INDEKS PEMBANGUNAN ====================

        // Tabel pembangunan_desa
        Schema::table('pembangunan_desa', function (Blueprint $table) {
            $table->index(['desa_id', 'tahun_anggaran'], 'idx_pembangunan_desa_tahun');
            $table->index(['status', 'progress_fisik'], 'idx_pembangunan_status_progress');
            $table->index(['tanggal_mulai', 'tanggal_selesai'], 'idx_pembangunan_tgl_range');
            $table->index('progress_keuangan', 'idx_pembangunan_progress_keu');
        });

        // Tabel pembangunan_dokumen_spj
        Schema::table('pembangunan_dokumen_spj', function (Blueprint $table) {
            $table->index(['pembangunan_desa_id', 'status'], 'idx_spj_desa_status');
            $table->index('status', 'idx_spj_status');
        });

        // Tabel pembangunan_logbook
        Schema::table('pembangunan_logbook', function (Blueprint $table) {
            $table->index(['pembangunan_desa_id', 'tanggal'], 'idx_logbook_desa_tgl');
        });

        // ==================== INDEKS MASTER DATA ====================

        // Tabel master_kegiatans
        Schema::table('master_kegiatans', function (Blueprint $table) {
            $table->index(['tahun', 'jenis_kegatan'], 'idx_mkegiatans_tahun_jenis');
            $table->index('sumber_dana', 'idx_mkegiatans_sumber_dana');
        });

        // Tabel master_komponen_belanja
        Schema::table('master_komponen_belanja', function (Blueprint $table) {
            $table->index(['kategori', 'is_active'], 'idx_mkomponen_kategori_active');
        });

        // Tabel master_bidang
        Schema::table('master_bidang', function (Blueprint $table) {
            $table->index('kode_bidang', 'idx_mbidang_kode');
        });

        // Tabel master_sub_bidang
        Schema::table('master_sub_bidang', function (Blueprint $table) {
            $table->index(['master_bidang_id', 'kode_sub_bidang'], 'idx_msubbidang_master_kode');
        });

        // ==================== INDEKS SUBMISSION (EKBANG) ====================

        // Tabel submissions - untuk laporan berkala ekbang
        Schema::table('submissions', function (Blueprint $table) {
            $table->index(['desa_id', 'menu_id', 'created_at'], 'idx_submission_desa_menu_tgl');
            $table->index(['menu_id', 'status'], 'idx_submission_menu_status');
            $table->index(['aspek_id', 'status'], 'idx_submission_aspek_status');
        });

        // Tabel UsulanMusrenbang
        Schema::table('usulan_musrenbang', function (Blueprint $table) {
            $table->index(['desa_id', 'status', 'skala'], 'idx_musrenbang_desa_status_skala');
            $table->index('tahun', 'idx_musrenbang_tahun');
        });

        // ==================== INDEKS PENGUNJUNG (BUKU TAMU) ====================

        Schema::table('pengunjung_kecamatan', function (Blueprint $table) {
            $table->index(['desa_asal_id', 'status'], 'idx_pengunjung_desa_status');
            $table->index(['jam_datang', 'status'], 'idx_pengunjung_jam_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus indeks (fallback only - tidak semua bisa dihapus)
        Schema::table('umkm', function (Blueprint $table) {
            $table->dropIndex('idx_umkm_desa_status');
            $table->dropIndex('idx_umkm_jenis_status');
            $table->dropIndex('idx_umkm_created_at');
        });

        Schema::table('umkm_local', function (Blueprint $table) {
            $table->dropIndex('idx_umkm_local_desa_status');
            $table->dropIndex('idx_umkm_local_kategori');
        });

        Schema::table('work_directory', function (Blueprint $table) {
            $table->dropIndex('idx_workdir_category_status');
            $table->dropIndex('idx_workdir_service_area');
            $table->dropIndex('idx_workdir_price');
        });

        Schema::table('loker', function (Blueprint $table) {
            $table->dropIndex('idx_loker_desa_status');
            $table->dropIndex('idx_loker_tgl_tutup');
            $table->dropIndex('idx_loker_jenis_status');
        });

        Schema::table('pembangunan_desa', function (Blueprint $table) {
            $table->dropIndex('idx_pembangunan_desa_tahun');
            $table->dropIndex('idx_pembangunan_status_progress');
            $table->dropIndex('idx_pembangunan_tgl_range');
            $table->dropIndex('idx_pembangunan_progress_keu');
        });

        Schema::table('pembangunan_dokumen_spj', function (Blueprint $table) {
            $table->dropIndex('idx_spj_desa_status');
            $table->dropIndex('idx_spj_status');
        });

        Schema::table('pembangunan_logbook', function (Blueprint $table) {
            $table->dropIndex('idx_logbook_desa_tgl');
        });

        Schema::table('master_kegiatans', function (Blueprint $table) {
            $table->dropIndex('idx_mkegiatans_tahun_jenis');
            $table->dropIndex('idx_mkegiatans_sumber_dana');
        });

        Schema::table('master_komponen_belanja', function (Blueprint $table) {
            $table->dropIndex('idx_mkomponen_kategori_active');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('idx_submission_desa_menu_tgl');
            $table->dropIndex('idx_submission_menu_status');
            $table->dropIndex('idx_submission_aspek_status');
        });

        Schema::table('usulan_musrenbang', function (Blueprint $table) {
            $table->dropIndex('idx_musrenbang_desa_status_skala');
            $table->dropIndex('idx_musrenbang_tahun');
        });

        Schema::table('pengunjung_kecamatan', function (Blueprint $table) {
            $table->dropIndex('idx_pengunjung_desa_status');
            $table->dropIndex('idx_pengunjung_jam_status');
        });
    }
};
