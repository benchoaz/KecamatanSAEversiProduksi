<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class CreatePencairanMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Menu::updateOrCreate(
            ['kode_menu' => 'pencairan_dd'],
            [
                'nama_menu' => 'Syarat Pencairan DD',
                'deskripsi' => 'Modul verifikasi dan rekomendasi pencairan Dana Desa sesuai PMK 7/2026.',
                'icon' => 'fas fa-file-invoice-dollar',
                'urutan' => 6,
                'is_active' => true,
            ]
        );
    }
}
