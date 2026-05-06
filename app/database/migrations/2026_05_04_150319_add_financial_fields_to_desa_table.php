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
            $table->string('rekening_desa')->nullable()->after('nama_desa');
            $table->decimal('pagu_siltap', 15, 2)->default(0)->after('rekening_desa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('desa', function (Blueprint $table) {
            $table->dropColumn(['rekening_desa', 'pagu_siltap']);
        });
    }
};
