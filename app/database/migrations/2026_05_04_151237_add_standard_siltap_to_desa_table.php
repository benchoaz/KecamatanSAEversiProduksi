<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('desa', function (Blueprint $table) {
            $table->decimal('siltap_kades', 15, 2)->default(0)->after('pagu_siltap');
            $table->decimal('siltap_sekdes', 15, 2)->default(0)->after('siltap_kades');
            $table->decimal('siltap_perangkat', 15, 2)->default(0)->after('siltap_sekdes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('desa', function (Blueprint $table) {
            $table->dropColumn(['siltap_kades', 'siltap_sekdes', 'siltap_perangkat']);
        });
    }
};
