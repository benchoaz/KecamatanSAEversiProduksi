<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmergencyFaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'category' => 'darurat',
                'question' => 'Apa yang harus dilakukan jika terjadi kebakaran?',
                'answer' => 'Segera hubungi Call Center Darurat 112. Tetap tenang, evakuasi diri ke tempat aman melalui jalur evakuasi, dan jangan mencoba memadamkan api besar sendirian tanpa alat pelindung.',
                'keywords' => 'kebakaran, api, damkar, 112',
                'is_active' => true,
            ],
            [
                'category' => 'darurat',
                'question' => 'Bagaimana jika ada kondisi darurat kesehatan atau persalinan (melahirkan)?',
                'answer' => 'Segera hubungi Call Center Kesehatan 119 atau hubungi ambulans desa/puskesmas terdekat. Untuk persalinan, tetap tenang, siapkan buku KIA, KK/KTP, dan segera menuju fasilitas kesehatan terdekat.',
                'keywords' => 'hamil, melahirkan, persalinan, darurat medis, sakit, kecelakaan, 119, ambulans',
                'is_active' => true,
            ],
            [
                'category' => 'darurat',
                'question' => 'Melapor kemana jika ada tindakan kriminal seperti pencurian atau gangguan keamanan?',
                'answer' => 'Segera hubungi layanan Kepolisian di nomor 110 atau hubungi petugas Trantibum Kecamatan/Polsek terdekat untuk penanganan lebih lanjut.',
                'keywords' => 'maling, rampok, polisi, kriminal, keamanan, 110',
                'is_active' => true,
            ],
            [
                'category' => 'darurat',
                'question' => 'Kemana harus melapor jika ada dugaan penyelewengan dana atau penyalahgunaan wewenang?',
                'answer' => 'Laporan terkait penyalahgunaan wewenang atau penyelewengan dana dapat disampaikan secara resmi melalui kanal SP4N LAPOR di https://www.lapor.go.id agar dapat ditindaklanjuti oleh instansi berwenang.',
                'keywords' => 'korupsi, pungli, dana desa, lapor, penyelewengan',
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $faq) {
            DB::table('pelayanan_faqs')->updateOrInsert(
                ['question' => $faq['question']],
                array_merge($faq, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
