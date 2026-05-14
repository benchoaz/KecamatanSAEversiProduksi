<?php

namespace App\Console\Commands\Hub;

use App\Models\Hub\HubDistrict;
use App\Services\Hub\DistrictConnectionService;
use Illuminate\Console\Command;

class SyncDistricts extends Command
{
    protected $signature   = 'hub:sync-districts
                                {--district= : Slug kecamatan tertentu (kosong = semua)}
                                {--dry-run   : Cek koneksi saja tanpa menjalankan migrasi}';

    protected $description = 'Sinkronisasi & jalankan migrasi ke database 24 kecamatan sekaligus.';

    public function handle(DistrictConnectionService $connector): int
    {
        $this->info('╔═══════════════════════════════════════╗');
        $this->info('║  HUB SYNC — Migrasi Lintas Kecamatan  ║');
        $this->info('╚═══════════════════════════════════════╝');

        $targetSlug = $this->option('district');
        $isDryRun   = $this->option('dry-run');

        $query = HubDistrict::where('is_active', true);
        if ($targetSlug) {
            $query->where('slug', $targetSlug);
        }

        $districts = $query->get();

        if ($districts->isEmpty()) {
            $this->warn('Tidak ada kecamatan aktif ditemukan.');
            return self::FAILURE;
        }

        $this->line('');
        $this->line("Target: <comment>{$districts->count()} kecamatan</comment>");
        $isDryRun && $this->warn('[DRY-RUN MODE — Tidak ada perubahan yang dijalankan]');
        $this->line('');

        $success = 0;
        $failed  = 0;

        foreach ($districts as $district) {
            $this->line("→ <info>{$district->name}</info> [{$district->slug}]");

            $conn = $connector->connect($district);

            if (!$conn) {
                $this->line("  <error>✗ Gagal konek ke database '{$district->db_name}'</error>");
                $failed++;
                continue;
            }

            $this->line("  ✓ Koneksi berhasil ke '{$district->db_name}'");

            if ($isDryRun) {
                $this->line("  [DRY-RUN] Lewati eksekusi migrasi.");
                $success++;
                continue;
            }

            // Jalankan migrasi di database kecamatan ini
            try {
                \Artisan::call('migrate', [
                    '--database' => 'district_' . $district->slug,
                    '--path'     => 'database/migrations', // Migrasi standar kecamatan
                    '--force'    => true,
                ], $this->output);

                $this->line("  ✓ <comment>Migrasi selesai</comment>");
                $success++;
            } catch (\Exception $e) {
                $this->line("  <error>✗ Migrasi gagal: {$e->getMessage()}</error>");
                $failed++;
            }

            $this->line('');
        }

        $this->line('');
        $this->info("═══════════════════════════════");
        $this->info("✓ Berhasil : {$success} kecamatan");
        $failed > 0 && $this->error("✗ Gagal    : {$failed} kecamatan");
        $this->info("═══════════════════════════════");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
