<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PelayananFaq;

class PelayananFaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate first to avoid duplicates
        PelayananFaq::truncate();

        $faqs = [
            [
                'category' => 'Administrasi',
                'module' => 'pelayanan',
                'question' => 'Syarat Pembuatan KTP Elektronik',
                'answer' => "📋 Syarat KTP Baru:\n1. Akta Lahir (asli)\n2. KK Asli\n3. Surat Pengantar RT/RW\n4. FREE/GRATIS",
                'keywords' => 'ktp,ktp baru,buat ktp,syarat ktp,kartu tanda penduduk,elektronik ktp,e-ktp',
                'priority' => 10,
                'is_active' => true
            ],
            [
                'category' => 'Administrasi',
                'module' => 'pelayanan',
                'question' => 'Syarat Pembuatan Kartu Keluarga (KK)',
                'answer' => "📋 Syarat KK Baru:\n1. Akta Lahir (untuk anggota baru)\n2. Surat Pengantar RT/RW\n3. Foto terbaru ukuran 3x4\n4. FREE/GRATIS",
                'keywords' => 'kk,kk baru,buat kk,syarat kk,kartu keluarga',
                'priority' => 10,
                'is_active' => true
            ],
            [
                'category' => 'Administrasi',
                'module' => 'pelayanan',
                'question' => 'Syarat Pembuatan Akta Kelahiran',
                'answer' => "📋 Syarat Akta Lahir:\n1. Surat Kelahiran dari Rumah Sakit/Bidan\n2. KK Orang Tua\n3. KTP Orang Tua\n4. FREE/GRATIS",
                'keywords' => 'akta,akta lahir,syarat akta,kelahiran',
                'priority' => 10,
                'is_active' => true
            ],
            [
                'category' => 'Administrasi',
                'module' => 'pelayanan',
                'question' => 'Syarat Surat Keterangan Tidak Mampu (SKTM)',
                'answer' => "📋 Syarat SKTM:\n1. Fotokopi KK\n2. Fotokopi KTP\n3. Surat Pengantar Desa\n4. Bukti Penghasilan",
                'keywords' => 'sktm,tidak mampu,miskin,syarat sktm,surat tidak mampu',
                'priority' => 10,
                'is_active' => true
            ],
            [
                'category' => 'Administrasi',
                'module' => 'pelayanan',
                'question' => 'Syarat Surat Keterangan Domisili',
                'answer' => "📋 Syarat Domisili:\n1. Fotokopi KTP\n2. Fotokopi KK\n3. Surat Pengantar RT/RW",
                'keywords' => 'domisili,tinggal,syarat domisili,tempat tinggal,kediaman',
                'priority' => 10,
                'is_active' => true
            ],
            [
                'category' => 'Administrasi',
                'module' => 'pelayanan',
                'question' => 'Syarat Surat Pengantar Nikah',
                'answer' => "📋 Syarat Pengantar Nikah:\n1. Fotokopi KK\n2. Fotokopi KTP\n3. Surat Pengantar Desa (N1-N4)\n4. Pas Photo",
                'keywords' => 'nikah,pernikahan,kawin,syarat nikah,pengantar nikah',
                'priority' => 10,
                'is_active' => true
            ],
            [
                'category' => 'Administrasi',
                'module' => 'pelayanan',
                'question' => 'Syarat Pendaftaran BPJS Kesehatan',
                'answer' => "📋 Syarat BPJS:\n1. Fotokopi KK\n2. Fotokopi KTP\n3. Surat Rekomendasi Dinas Sosial (untuk Penerima Bantuan)",
                'keywords' => 'bpjs,bpjs kesehatan,kesra,syarat bpjs',
                'priority' => 10,
                'is_active' => true
            ],
            [
                'category' => 'Umum',
                'module' => 'pelayanan',
                'question' => 'Jam Layanan Kantor',
                'answer' => "🕐 Jam Layanan:\nSenin - Jumat: 08:00 - 16:00\nSabtu: 08:00 - 12:00\nMinggu: Tutup",
                'keywords' => 'jam kerja,jam kantor',
                'priority' => 0,
                'is_active' => true
            ],
            [
                'category' => 'Darurat',
                'module' => 'pelayanan',
                'question' => 'Nomor Darurat',
                'answer' => "🚨 Nomor Darurat:\n- Polisi: 110\n- Ambulans: 118\n- Pemadam: 113\n- Posko Camat: 082231203765",
                'keywords' => 'darurat,nomor darurat',
                'priority' => 2,
                'is_active' => true
            ],
            [
                'category' => 'Pemerintahan',
                'module' => 'pelayanan',
                'question' => 'Masalah dengan aparat Desa',
                'answer' => "Masalah terkait kinerja atau administrasi Desa dapat dikonsultasikan melalui Seksi Pemerintahan di Kecamatan. Kami akan melakukan mediasi atau pembinaan terhadap Pemerintah Desa terkait sesuai kewenangan Camat sebagai pembina wilayah.",
                'keywords' => 'desa,kades,aparat desa,konflik desa,perangkat desa',
                'priority' => 1,
                'is_active' => true
            ],
            [
                'category' => 'Pembangunan',
                'module' => 'pelayanan',
                'question' => 'Izin usaha dan bantuan modal UMKM',
                'answer' => "Untuk pelaku usaha mikro, Anda dapat mengurus NIB (Nomor Induk Berusaha) secara mandiri melalui sistem OSS atau meminta bantuan pendampingan di Seksi Ekonomi & Pembangunan Kecamatan. Untuk bantuan modal, kami sering mengadakan sosialisasi program KUR dari perbankan atau pelatihan keterampilan UMKM.",
                'keywords' => 'umkm,modal,izin usaha,ibp,nib',
                'priority' => 1,
                'is_active' => true
            ]
        ];

        foreach ($faqs as $faq) {
            PelayananFaq::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }
    }
}
