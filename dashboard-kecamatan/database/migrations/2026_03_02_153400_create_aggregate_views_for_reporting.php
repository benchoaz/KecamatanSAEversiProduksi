<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * View Agregat untuk Real-time Reporting Ekonomi & Pembangunan
     * Berdasarkan analisis struktur database
     */
    public function up(): void
    {
        // ==================== VIEW EKONOMI ====================

        // View: Ringkasan Ekonomi per Desa
        DB::statement("
            CREATE OR REPLACE VIEW v_ekonomi_desa_summary AS
            SELECT 
                d.id AS desa_id,
                d.nama_desa,
                d.kode_desa,
                COUNT(DISTINCT u.id) AS total_umkm,
                COUNT(DISTINCT CASE WHEN u.status = 'aktif' THEN u.id END) AS umkm_aktif,
                COUNT(DISTINCT CASE WHEN u.status = 'pending' THEN u.id END) AS umkm_pending,
                COUNT(DISTINCT ul.id) AS total_umkm_local,
                COUNT(DISTINCT wd.id) AS total_jasa,
                COUNT(DISTINCT l.id) AS total_lowongan,
                COUNT(DISTINCT CASE WHEN l.status = 'aktif' AND l.tanggal_tutup >= CURDATE() THEN l.id END) AS lowongan_aktif,
                COUNT(DISTINCT jv.id) AS total_job_vacancy,
                -- Estimasi nilai ekonomi (placeholder - bisa dikustomisasi)
                (COUNT(DISTINCT u.id) * 10000000) AS estimasi_nilai_umkm
            FROM desa d
            LEFT JOIN umkm u ON u.desa_id = d.id
            LEFT JOIN umkm_local ul ON ul.desa_id = d.id
            LEFT JOIN work_directory wd ON wd.desa_id = d.id
            LEFT JOIN loker l ON l.desa_id = d.id
            LEFT JOIN job_vacancy jv ON jv.desa_id = d.id
            GROUP BY d.id, d.nama_desa, d.kode_desa
        ");

        // View: Ringkasan Ekonomi Kecamatan (Agregat)
        DB::statement("
            CREATE OR REPLACE VIEW v_ekonomi_kecamatan_summary AS
            SELECT 
                'KECAMATAN' AS level,
                'ALL' AS kode,
                'Seluruh Desa' AS nama,
                COUNT(DISTINCT u.id) AS total_umkm,
                COUNT(DISTINCT CASE WHEN u.status = 'aktif' THEN u.id END) AS umkm_aktif,
                COUNT(DISTINCT CASE WHEN u.status = 'pending' THEN u.id END) AS umkm_pending,
                COUNT(DISTINCT ul.id) AS total_umkm_local,
                COUNT(DISTINCT wd.id) AS total_jasa,
                COUNT(DISTINCT l.id) AS total_lowongan,
                COUNT(DISTINCT CASE WHEN l.status = 'aktif' AND l.tanggal_tutup >= CURDATE() THEN l.id END) AS lowongan_aktif,
                COUNT(DISTINCT jv.id) AS total_job_vacancy,
                (COUNT(DISTINCT u.id) * 10000000) AS estimasi_nilai_umkm
            FROM umkm u
            LEFT JOIN umkm_local ul ON 1=1
            LEFT JOIN work_directory wd ON 1=1
            LEFT JOIN loker l ON 1=1
            LEFT JOIN job_vacancy jv ON 1=1
        ");

        // View: Kategori Bisnis/Usaha
        DB::statement("
            CREATE OR REPLACE VIEW v_ekonomi_kategori_summary AS
            SELECT 
                u.jenis_usaha AS kategori,
                COUNT(*) AS jumlah,
                COUNT(CASE WHEN u.status = 'aktif' THEN 1 END) AS aktif,
                COUNT(CASE WHEN u.status = 'pending' THEN 1 END) AS pending,
                COUNT(CASE WHEN u.status = 'nonaktif' THEN 1 END) AS nonaktif
            FROM umkm u
            WHERE u.jenis_usaha IS NOT NULL
            GROUP BY u.jenis_usaha
            ORDER BY jumlah DESC
        ");

        // ==================== VIEW PEMBANGUNAN ====================

        // View: Ringkasan Pembangunan per Desa
        DB::statement("
            CREATE OR REPLACE VIEW v_pembangunan_desa_summary AS
            SELECT 
                d.id AS desa_id,
                d.nama_desa,
                d.kode_desa,
                COUNT(pd.id) AS total_proyek,
                COUNT(CASE WHEN pd.status = 'planning' THEN 1 END) AS proyek_planning,
                COUNT(CASE WHEN pd.status = 'executing' THEN 1 END) AS proyek_executing,
                COUNT(CASE WHEN pd.status = 'completed' THEN 1 END) AS proyek_completed,
                COALESCE(SUM(pd.pagu_anggaran), 0) AS total_pagu,
                COALESCE(SUM(pd.realisasi_anggaran), 0) AS total_realisasi,
                CASE 
                    WHEN SUM(pd.pagu_anggaran) > 0 
                    THEN ROUND((SUM(pd.realisasi_anggaran) / SUM(pd.pagu_anggaran)) * 100, 2)
                    ELSE 0 
                END AS prosentase_realisasi,
                COALESCE(AVG(pd.progress_fisik), 0) AS avg_progress_fisik,
                COALESCE(AVG(pd.progress_keuangan), 0) AS avg_progress_keuangan,
                MAX(pd.tanggal_mulai) AS proyek_terbaru_mulai,
                MIN(pd.tanggal_selesai) AS proyek_tercepat_selesai
            FROM desa d
            LEFT JOIN pembangunan_desa pd ON pd.desa_id = d.id
            GROUP BY d.id, d.nama_desa, d.kode_desa
        ");

        // View: Serapan Anggaran per Tahun
        DB::statement("
            CREATE OR REPLACE VIEW v_pembangunan_anggaran_tahun AS
            SELECT 
                pd.tahun_anggaran AS tahun,
                COUNT(pd.id) AS jumlah_proyek,
                SUM(pd.pagu_anggaran) AS total_pagu,
                SUM(pd.realisasi_anggaran) AS total_realisasi,
                CASE 
                    WHEN SUM(pd.pagu_anggaran) > 0 
                    THEN ROUND((SUM(pd.realisasi_anggaran) / SUM(pd.pagu_anggaran)) * 100, 2)
                    ELSE 0 
                END AS prosentase_realisasi,
                AVG(pd.progress_fisik) AS avg_progress_fisik,
                AVG(pd.progress_keuangan) AS avg_progress_keuangan
            FROM pembangunan_desa pd
            WHERE pd.tahun_anggaran IS NOT NULL
            GROUP BY pd.tahun_anggaran
            ORDER BY pd.tahun_anggaran DESC
        ");

        // View: Status SPJ/Dokumen Pertanggungjawaban
        DB::statement("
            CREATE OR REPLACE VIEW v_pembangunan_spj_status AS
            SELECT 
                pd.id AS pembangunan_id,
                pd.nama_kegatan,
                d.nama_desa,
                pd.status AS status_proyek,
                COUNT(pds.id) AS total_dokumen_wajib,
                COUNT(CASE WHEN pds.status = 'uploaded' THEN 1 END) AS dokumen_uploaded,
                COUNT(CASE WHEN pds.status = 'verified' THEN 1 END) AS dokumen_verified,
                COUNT(CASE WHEN pds.status = 'pending' THEN 1 END) AS dokumen_pending,
                CASE 
                    WHEN COUNT(pds.id) > 0 
                    THEN ROUND((COUNT(CASE WHEN pds.status IN ('uploaded', 'verified') THEN 1 END) / COUNT(pds.id)) * 100, 2)
                    ELSE 0 
                END AS prosentase_kelengkapan
            FROM pembangunan_desa pd
            LEFT JOIN pembangunan_dokumen_spj pds ON pds.pembangunan_desa_id = pd.id AND pds.is_wajib = 1
            LEFT JOIN desa d ON d.id = pd.desa_id
            GROUP BY pd.id, pd.nama_kegatan, d.nama_desa, pd.status
        ");

        // ==================== VIEW EKBANG/LAPORAN ====================

        // View: Ringkasan Submission per Desa
        DB::statement("
            CREATE OR REPLACE VIEW v_ekbang_submission_summary AS
            SELECT 
                d.id AS desa_id,
                d.nama_desa,
                m.kode_menu,
                m.nama_menu,
                COUNT(s.id) AS total_submission,
                COUNT(CASE WHEN s.status = 'draft' THEN 1 END) AS draft,
                COUNT(CASE WHEN s.status = 'submitted' THEN 1 END) AS submitted,
                COUNT(CASE WHEN s.status = 'verified' THEN 1 END) AS verified,
                COUNT(CASE WHEN s.status = 'rejected' THEN 1 END) AS rejected,
                MAX(s.created_at) AS submission_terakhir
            FROM desa d
            CROSS JOIN menu m
            LEFT JOIN submissions s ON s.desa_id = d.id AND s.menu_id = m.id
            WHERE m.kode_menu IN ('ekbang', 'pemerintah', 'kesra', 'pembangunan')
            GROUP BY d.id, d.nama_desa, m.kode_menu, m.nama_menu
        ");

        // View: Aspek EKBANG per Desa
        DB::statement("
            CREATE OR REPLACE VIEW v_ekbang_aspek_summary AS
            SELECT 
                d.id AS desa_id,
                d.nama_desa,
                a.kode_aspek,
                a.nama_aspek,
                COUNT(s.id) AS total_laporan,
                COUNT(CASE WHEN s.status = 'verified' THEN 1 END) AS terverifikasi,
                COUNT(CASE WHEN s.status = 'submitted' THEN 1 END) AS menunggu_verifikasi,
                MAX(s.created_at) AS laporan_terakhir
            FROM desa d
            CROSS JOIN aspek a
            LEFT JOIN submissions s ON s.desa_id = d.id AND s.aspek_id = a.id
            WHERE a.kode_aspek LIKE 'ekb_%' OR a.kode_aspek LIKE '%monev%'
            GROUP BY d.id, d.nama_desa, a.kode_aspek, a.nama_aspek
        ");

        // ==================== VIEW MUSRENBANG ====================

        // View: Status Musrenbang
        DB::statement("
            CREATE OR REPLACE VIEW v_musrenbang_status AS
            SELECT 
                d.id AS desa_id,
                d.nama_desa,
                um.tahun,
                um.skala,
                um.status,
                COUNT(*) AS jumlah_usulan,
                CASE um.status
                    WHEN 'usulan' THEN 'Menunggu Verifikasi'
                    WHEN 'terverifikasi' THEN 'Terverifikasi'
                    WHEN 'ditolak' THEN 'Ditolak'
                    WHEN 'dianggarkan' THEN 'Sudah Dianggarkan'
                    ELSE um.status
                END AS status_label
            FROM desa d
            LEFT JOIN usulan_musrenbang um ON um.desa_id = d.id
            GROUP BY d.id, d.nama_desa, um.tahun, um.skala, um.status
        ");

        // ==================== VIEW BUKU TAMU ====================

        // View: Ringkasan Buku Tamu
        DB::statement("
            CREATE OR REPLACE VIEW v_buku_tamu_summary AS
            SELECT 
                d.id AS desa_id,
                d.nama_desa,
                COUNT(pk.id) AS total_pengunjung,
                COUNT(CASE WHEN DATE(pk.jam_datang) = CURDATE() THEN 1 END) AS hari_ini,
                COUNT(CASE WHEN pk.status = 'menunggu' THEN 1 END) AS menunggu,
                COUNT(CASE WHEN pk.status = 'dilayani' THEN 1 END) AS dilayani,
                COUNT(CASE WHEN pk.status = 'selesai' THEN 1 END) AS selesai,
                pk.tujuan_bidang AS bidang,
                MAX(pk.jam_datang) AS kunjungan_terakhir
            FROM desa d
            LEFT JOIN pengunjung_kecamatan pk ON pk.desa_asal_id = d.id
            GROUP BY d.id, d.nama_desa, pk.tujuan_bidang
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_ekonomi_desa_summary');
        DB::statement('DROP VIEW IF EXISTS v_ekonomi_kecamatan_summary');
        DB::statement('DROP VIEW IF EXISTS v_ekonomi_kategori_summary');
        DB::statement('DROP VIEW IF EXISTS v_pembangunan_desa_summary');
        DB::statement('DROP VIEW IF EXISTS v_pembangunan_anggaran_tahun');
        DB::statement('DROP VIEW IF EXISTS v_pembangunan_spj_status');
        DB::statement('DROP VIEW IF EXISTS v_ekbang_submission_summary');
        DB::statement('DROP VIEW IF EXISTS v_ekbang_aspek_summary');
        DB::statement('DROP VIEW IF EXISTS v_musrenbang_status');
        DB::statement('DROP VIEW IF EXISTS v_buku_tamu_summary');
    }
};
