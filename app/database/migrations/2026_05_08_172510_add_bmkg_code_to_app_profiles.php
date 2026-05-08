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
        Schema::table('app_profiles', function (Blueprint $table) {
            $table->string('bmkg_adm4_code')->nullable()->default('35.13.13.2012'); // Default to Besuk Agung
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_profiles', function (Blueprint $table) {
            $table->dropColumn('bmkg_adm4_code');
        });
    }
};
