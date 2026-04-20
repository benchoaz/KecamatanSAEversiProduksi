<?php

namespace App\Services;

use App\Models\DesaSummaryPmk;
use App\Models\DokumenPencairanDesa;
use App\Models\RekomendasiPencairanDd;

class EvaluasiPencairanService
{
    /**
     * Mengevaluasi dokumen dan kelayakan sebuah Desa berdasarkan PMK 7/2026.
     * Mengembalikan array status_akhir dan pesan.
     */
    public function evaluasiKesiapanPencairan($desaId, $tahap)
    {
        $tahun = date('Y'); // Anggap APBDes tahun berjalan
        
        $summary = DesaSummaryPmk::where('desa_id', $desaId)->first();
        if (!$summary) {
            return ['status' => 'BELUM ADA', 'pesan' => 'Data ringkasan PMK desa belum terisi.'];
        }

        // Cek dokumen wajib
        $lpj2025 = DokumenPencairanDesa::where([
            'desa_id' => $desaId,
            'kategori_dokumen' => 'lpj_2025'
        ])->first();

        $apbdes2026 = DokumenPencairanDesa::where([
            'desa_id' => $desaId,
            'kategori_dokumen' => 'apbdes_2026'
        ])->first();

        $perkades = DokumenPencairanDesa::where([
            'desa_id' => $desaId,
            'kategori_dokumen' => 'perkades_penjabaran'
        ])->first();

        $laporanRealisasiSblm = DokumenPencairanDesa::where([
            'desa_id' => $desaId,
            'kategori_dokumen' => 'laporan_realisasi_tahap_sebelumnya'
        ])->first();

        // LOGIC BERDASARKAN PMK NO 7 TAHUN 2026
        
        // 1. LPJ 2025 Harus 100% Selesai
        // status_lpj_2025 boolean mewakili persetujuan inspektorat/100% tuntas
        if (!$summary->status_lpj_2025 || !$lpj2025) {
            return [
                'status' => 'TUNDA', 
                'pesan' => 'LPJ 2025 belum tuntas 100% atau dokumen belum diunggah. Selesaikan sebelum pengajuan DD 2026.'
            ];
        }

        // 2. APBDes 2026
        if (!$apbdes2026) {
            return [
                'status' => 'TUNDA', 
                'pesan' => 'Dokumen APBDes 2026 belum diunggah atau belum ditetapkan/disahkan BPD.'
            ];
        }

        // 3. Perkades Penjabaran APBDes
        if (!$perkades) {
            return [
                'status' => 'TIDAK LAYAK', 
                'pesan' => 'Dokumen krusial (Perkades Penjabaran APBDes) tidak tersedia.'
            ];
        }

        // 4. Laporan Realisasi Tahap Sebelumnya (Hanya untuk Tahap 2 & 3)
        if ($tahap > 1 && !$laporanRealisasiSblm) {
            return [
                'status' => 'PERBAIKAN', 
                'pesan' => 'Laporan realisasi tahap sebelumnya belum dilampirkan.'
            ];
        }

        // Jika semua lolos -> Layak Cair
        return [
            'status' => 'LAYAK CAIR',
            'pesan' => 'Seluruh prasyarat administrasi PMK 7/2026 terpenuhi. Menunggu SK Rekomendasi Camat.'
        ];
    }
}
