<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\AppProfile::updateOrCreate(
            ['id' => 1],
            [
                'app_name' => 'Kecamatan SAE',
                'region_name' => 'Kecamatan Besuk',
                'region_level' => 'kecamatan',
                'tagline' => 'Solusi Administrasi Terpadu untuk Masyarakat',
                'address' => 'Jl. Raya Besuk No. 1, Probolinggo',
                'phone' => '0335-123456',
                'office_hours_mon_thu' => '07.30 - 16.00 WIB',
                'office_hours_fri' => '07.30 - 15.00 WIB',
                'leader_name' => 'Nama Camat Besuk',
                'leader_title' => 'Camat Besuk',
                'is_menu_layanan_active' => true,
                'is_menu_berita_active' => true,
                'is_menu_statistik_active' => true,
                'is_menu_umkm_active' => true,
                'whatsapp_bot_number' => '0821-4328-9363',
                'is_bot_active' => true,
            ]
        );
    }
}
