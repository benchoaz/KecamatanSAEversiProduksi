<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Hub\HubDistrict;

class HubVillageMappingSeeder extends Seeder
{
    public function run(): void
    {
        $besuk = HubDistrict::where('slug', 'besuk')->first();

        if (!$besuk) {
            $this->command->warn('⚠️  Kecamatan Besuk belum ada. Pastikan data hub_districts sudah terisi.');
            return;
        }

        // Update konfigurasi WAHA & n8n untuk Besuk
        $besuk->update([
            'waha_session_name' => 'default',
            'ai_enabled'        => true,
            'n8n_webhook_url'   => env('N8N_WEBHOOK_BESUK', 'https://n8n.kecamatansae.id/webhook/wa-besuk-handler'),
            'l1_keywords'       => [
                'jam buka'      => 'Kantor Kecamatan Besuk buka Senin-Jumat pukul 08.00-16.00 WIB.',
                'jam pelayanan' => 'Pelayanan: Senin-Kamis 08.00-15.30 WIB, Jumat 08.00-11.30 WIB.',
                'alamat'        => 'Kecamatan Besuk, Kabupaten Probolinggo, Jawa Timur.',
                'lokasi'        => 'Kecamatan Besuk, Kabupaten Probolinggo, Jawa Timur.',
            ],
        ]);

        // ✅ 17 Desa Kecamatan Besuk — Data Aktual dari Sistem VPS
        $desas = [
            ['village_name' => 'alas kandang',  'aliases' => ['alaskandang', 'alas kndang']],
            ['village_name' => 'alas nylur',    'aliases' => ['alas nyiur', 'alasnyiur', 'alasniur', 'alas niur', 'alasnylur']],
            ['village_name' => 'alas sumur lor', 'aliases' => ['alas sumur', 'alassumur', 'alassumur lor']],
            ['village_name' => 'alas tengah',   'aliases' => ['alastengah']],
            ['village_name' => 'bago',           'aliases' => []],
            ['village_name' => 'besuk agung',   'aliases' => ['besukagung', 'besuk lor']],
            ['village_name' => 'besuk kidul',   'aliases' => ['besuk selatan', 'besuk kidul']],
            ['village_name' => 'jambangan',     'aliases' => ['jambangan']],
            ['village_name' => 'kecik',         'aliases' => []],
            ['village_name' => 'klampokan',     'aliases' => ['klampokan']],
            ['village_name' => 'krampilan',     'aliases' => ['kramplan', 'krampian']],
            ['village_name' => 'matekan',       'aliases' => []],
            ['village_name' => 'randu jalak',   'aliases' => ['randujalak', 'randu merak']],
            ['village_name' => 'sindet anyar',  'aliases' => ['sindetanyar', 'sindet anyar']],
            ['village_name' => 'sindet lami',   'aliases' => ['sindetlami', 'sindetan']],
            ['village_name' => 'sumberan',      'aliases' => ['sumber an']],
            ['village_name' => 'sumurdalam',    'aliases' => ['sumur dalam', 'sumur-dalam']],
        ];

        // Hapus data lama, insert ulang yang benar
        DB::table('hub_village_map')->where('hub_district_id', $besuk->id)->delete();

        foreach ($desas as $desa) {
            DB::table('hub_village_map')->insert([
                'village_name'    => $desa['village_name'],
                'aliases'         => json_encode($desa['aliases']),
                'hub_district_id' => $besuk->id,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        $this->command->info('✅ 17 Desa Kecamatan Besuk (data aktual VPS) berhasil di-mapping ke Hub Router.');
    }
}
