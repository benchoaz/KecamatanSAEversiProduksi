<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterLayanan;

class MasterLayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate to avoid duplicates
        MasterLayanan::truncate();

        $services = [
            [
                'nama_layanan' => 'KTP Elektronik',
                'slug' => 'ktp',
                'deskripsi_syarat' => 'Akta Lahir, KK Asli, Surat Pengantar RT/RW',
                'estimasi_waktu' => '14 Hari Kerja',
                'ikon' => 'fa-id-card',
                'warna_bg' => 'bg-blue-50',
                'warna_text' => 'text-blue-600',
                'is_active' => true,
                'urutan' => 1
            ],
            [
                'nama_layanan' => 'Kartu Keluarga (KK)',
                'slug' => 'kk',
                'deskripsi_syarat' => 'Akta Lahir, Surat Pengantar RT/RW, Foto 3x4',
                'estimasi_waktu' => '14 Hari Kerja',
                'ikon' => 'fa-users',
                'warna_bg' => 'bg-green-50',
                'warna_text' => 'text-green-600',
                'is_active' => true,
                'urutan' => 2
            ],
            [
                'nama_layanan' => 'Akta Kelahiran',
                'slug' => 'akta',
                'deskripsi_syarat' => 'Surat Kelahiran RS/Bidan, KK Orang Tua, KTP Orang Tua',
                'estimasi_waktu' => '7 Hari Kerja',
                'ikon' => 'fa-baby',
                'warna_bg' => 'bg-purple-50',
                'warna_text' => 'text-purple-600',
                'is_active' => true,
                'urutan' => 3
            ],
            [
                'nama_layanan' => 'SKTM',
                'slug' => 'sktm',
                'deskripsi_syarat' => 'Fotokopi KK, Fotokopi KTP, Surat Pengantar Desa, Bukti Penghasilan',
                'estimasi_waktu' => '3 Hari Kerja',
                'ikon' => 'fa-hand-holding-heart',
                'warna_bg' => 'bg-yellow-50',
                'warna_text' => 'text-yellow-600',
                'is_active' => true,
                'urutan' => 4
            ],
            [
                'nama_layanan' => 'Surat Keterangan Domisili',
                'slug' => 'domisili',
                'deskripsi_syarat' => 'Fotokopi KTP, Fotokopi KK, Surat Pengantar RT/RW',
                'estimasi_waktu' => '1 Hari Kerja',
                'ikon' => 'fa-home',
                'warna_bg' => 'bg-indigo-50',
                'warna_text' => 'text-indigo-600',
                'is_active' => true,
                'urutan' => 5
            ],
            [
                'nama_layanan' => 'Surat Pengantar Nikah',
                'slug' => 'nikah',
                'deskripsi_syarat' => 'Fotokopi KK, Fotokopi KTP, Surat Pengantar Desa (N1-N4), Pas Photo',
                'estimasi_waktu' => '3 Hari Kerja',
                'ikon' => 'fa-heart',
                'warna_bg' => 'bg-rose-50',
                'warna_text' => 'text-rose-600',
                'is_active' => true,
                'urutan' => 6
            ],
            [
                'nama_layanan' => 'BPJS Kesehatan',
                'slug' => 'bpjs',
                'deskripsi_syarat' => 'Fotokopi KK, Fotokopi KTP, Surat Rekomendasi Dinas Sosial (untuk Penerima Bantuan)',
                'estimasi_waktu' => '7 Hari Kerja',
                'ikon' => 'fa-notes-medical',
                'warna_bg' => 'bg-teal-50',
                'warna_text' => 'text-teal-600',
                'is_active' => true,
                'urutan' => 7
            ],
            [
                'nama_layanan' => 'Pengaduan / Laporan',
                'slug' => 'pengaduan',
                'deskripsi_syarat' => 'Tulis kronologi kejadian dengan jelas dan lengkap',
                'estimasi_waktu' => '14 Hari Kerja',
                'ikon' => 'fa-comment-dots',
                'warna_bg' => 'bg-red-50',
                'warna_text' => 'text-red-600',
                'is_active' => true,
                'urutan' => 8
            ],
        ];

        foreach ($services as $service) {
            MasterLayanan::create($service);
        }
    }
}
