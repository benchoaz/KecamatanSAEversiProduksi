<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan fitur Seller Center:
     * - patokan_lokasi: ancer-ancer lokasi toko untuk warga lokal
     * - satuan_harga: satuan per produk (Pcs, Bungkus, Porsi, Kg, dll)
     */
    public function up(): void
    {
        // Tambah patokan_lokasi ke tabel umkm
        if (Schema::hasTable('umkm') && !Schema::hasColumn('umkm', 'patokan_lokasi')) {
            Schema::table('umkm', function (Blueprint $table) {
                $table->string('patokan_lokasi')->nullable()->after('desa')
                    ->comment('Ancer-ancer lokasi toko, contoh: 3 ruko dari timur Alfamart');
            });
        }

        // Tambah satuan_harga ke tabel umkm_products
        if (Schema::hasTable('umkm_products') && !Schema::hasColumn('umkm_products', 'satuan_harga')) {
            Schema::table('umkm_products', function (Blueprint $table) {
                $table->string('satuan_harga', 50)->nullable()->default('Pcs')->after('harga')
                    ->comment('Satuan harga produk: Pcs, Bungkus, Porsi, Kg, Liter, Lusin, dll');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('umkm', 'patokan_lokasi')) {
            Schema::table('umkm', function (Blueprint $table) {
                $table->dropColumn('patokan_lokasi');
            });
        }

        if (Schema::hasColumn('umkm_products', 'satuan_harga')) {
            Schema::table('umkm_products', function (Blueprint $table) {
                $table->dropColumn('satuan_harga');
            });
        }
    }
};
