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
        Schema::table('personil_desa', function (Blueprint $table) {
            $table->decimal('siltap_pokok', 15, 2)->default(0)->after('jabatan');
            $table->decimal('tunjangan_jabatan', 15, 2)->default(0)->after('siltap_pokok');
            $table->string('rekening_bank')->nullable()->after('nik');
            $table->string('nama_bank')->nullable()->after('rekening_bank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personil_desa', function (Blueprint $table) {
            $table->dropColumn(['siltap_pokok', 'tunjangan_jabatan', 'rekening_bank', 'nama_bank']);
        });
    }
};
