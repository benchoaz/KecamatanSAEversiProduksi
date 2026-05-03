<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterLayanan;
use App\Models\PelayananFaq;
use Illuminate\Support\Str;

class SyncServiceToFaq extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'faq:sync-services {--clear : Clear existing Adminduk FAQs before syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize MasterLayanan services into PelayananFaq and seed general administrative FAQs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting FAQ synchronization...');

        if ($this->option('clear')) {
            PelayananFaq::where('category', 'Adminduk')->delete();
            $this->warn('Deleted existing Adminduk FAQs.');
        }

        // 1. Sync from MasterLayanan
        $services = MasterLayanan::where('is_active', true)->get();
        $syncCount = 0;

        foreach ($services as $service) {
            $question = "Bagaimana cara mengurus " . $service->nama_layanan . "?";
            
            // Clean HTML from deskripsi_syarat if needed, but keep some formatting for AI
            $requirements = strip_tags($service->deskripsi_syarat);
            
            $answer = "Untuk mengurus **" . $service->nama_layanan . "**, berikut adalah persyaratannya:\n\n" . 
                      $service->deskripsi_syarat . "\n\n" .
                      "**Estimasi Waktu:** " . ($service->estimasi_waktu ?? 'Sesuai antrean') . ".\n\n" .
                      "Silakan ajukan melalui portal website atau datang langsung ke kantor dengan membawa berkas asli.";

            PelayananFaq::updateOrCreate(
                ['question' => $question],
                [
                    'category' => 'Adminduk',
                    'module' => PelayananFaq::MODULE_PELAYANAN,
                    'keywords' => $service->nama_layanan . ', syarat, cara, berkas, ' . $service->slug,
                    'answer' => $answer,
                    'priority' => 10,
                    'is_active' => true,
                ]
            );
            $syncCount++;
        }

        $this->info("Successfully synced {$syncCount} services to FAQ.");

        // 2. Seed General Administrative FAQs
        $this->info('Seeding general administrative FAQs...');
        $generalFaqs = [
            [
                'question' => 'Kapan jam operasional kantor pelayanan?',
                'answer' => 'Kantor pelayanan kami buka setiap hari kerja:\n- **Senin s/d Kamis**: 08:00 - 15:30 WIB\n- **Jumat**: 08:00 - 11:30 WIB\n- **Sabtu & Minggu**: Libur.',
                'keywords' => 'jam buka, jam kerja, operasional, tutup, buka',
                'category' => 'Umum'
            ],
            [
                'question' => 'Berapa biaya pengurusan administrasi di sini?',
                'answer' => 'Seluruh pengurusan administrasi kependudukan dan layanan publik di kantor kami adalah **GRATIS (Rp 0,-)**. Jika ada oknum yang meminta biaya, silakan laporkan melalui menu Pengaduan.',
                'keywords' => 'biaya, bayar, gratis, pungli, harga',
                'category' => 'Umum'
            ],
            [
                'question' => 'Bagaimana jika berkas permohonan saya ditolak?',
                'answer' => 'Jika berkas Anda ditolak, silakan periksa alasan penolakan pada detail riwayat permohonan Anda. Biasanya disebabkan oleh dokumen yang tidak lengkap atau foto berkas yang tidak jelas (blur). Anda dapat mengunggah ulang berkas yang benar tanpa harus membuat permohonan baru dari awal.',
                'keywords' => 'tolak, gagal, berkas salah, revisi, perbaiki',
                'category' => 'Umum'
            ],
            [
                'question' => 'Apakah saya bisa mengurus surat untuk orang lain?',
                'answer' => 'Secara umum, pengurusan administrasi harus dilakukan oleh yang bersangkutan. Namun, untuk anggota keluarga dalam satu Kartu Keluarga (KK), dapat diwakilkan oleh kepala keluarga atau anggota keluarga dewasa lainnya dengan menunjukkan identitas asli.',
                'keywords' => 'wakil, titip, orang lain, keluarga, kuasa',
                'category' => 'Umum'
            ],
        ];

        foreach ($generalFaqs as $faq) {
            PelayananFaq::updateOrCreate(
                ['question' => $faq['question']],
                [
                    'category' => $faq['category'],
                    'module' => PelayananFaq::MODULE_PELAYANAN,
                    'keywords' => $faq['keywords'],
                    'answer' => $faq['answer'],
                    'priority' => 5,
                    'is_active' => true,
                ]
            );
        }

        $this->info('General FAQs seeded successfully.');
    }
}
