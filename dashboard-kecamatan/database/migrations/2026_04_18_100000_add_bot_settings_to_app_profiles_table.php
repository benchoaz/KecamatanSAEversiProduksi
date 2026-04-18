<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_profiles', function (Blueprint $table) {
            $table->string('public_url')->nullable()->after('phone')
                ->comment('URL publik aplikasi, digunakan bot WhatsApp (misal: https://kecamatanbesuk.my.id:8443)');
            $table->json('whatsapp_bot_menu')->nullable()->after('public_url')
                ->comment('Konfigurasi menu bot WhatsApp dalam format JSON');
        });

        // Set default menu value for existing records
        DB::table('app_profiles')->update([
            'whatsapp_bot_menu' => json_encode([
                [
                    'number' => '1',
                    'label' => 'ADMINISTRASI',
                    'description' => 'Cek Syarat dan Status Berkas',
                    'action' => 'administrasi',
                    'enabled' => true,
                ],
                [
                    'number' => '2',
                    'label' => 'PRODUK UMKM',
                    'description' => 'Belanja Produk & Olahan Warga Lokal',
                    'action' => 'umkm_produk',
                    'enabled' => true,
                ],
                [
                    'number' => '3',
                    'label' => 'CARI JASA',
                    'description' => 'Tukang, ART, Ojek, Tenaga Harian',
                    'action' => 'jasa',
                    'enabled' => true,
                ],
                [
                    'number' => '4',
                    'label' => 'PENGADUAN',
                    'description' => 'Aspirasi dan Laporan Warga',
                    'action' => 'pengaduan',
                    'enabled' => true,
                ],
                [
                    'number' => '5',
                    'label' => 'KELOLA PROFIL',
                    'description' => 'Kelola Data Jasa / Toko UMKM Anda',
                    'action' => 'kelola_profil',
                    'enabled' => true,
                ],
            ]),
        ]);
    }

    public function down(): void
    {
        Schema::table('app_profiles', function (Blueprint $table) {
            $table->dropColumn(['public_url', 'whatsapp_bot_menu']);
        });
    }
};
