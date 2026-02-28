<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

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
];

foreach ($faqs as $faq) {
    App\Models\PelayananFaq::create($faq);
    echo "Created: " . $faq['question'] . "\n";
}

echo "\nDone! Total: " . count($faqs) . " FAQs\n";
